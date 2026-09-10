<?php
require_once "clases/providers/BillingProviderFactory.php";
require_once "helpers/billing_helpers.php";
require_once "clases/cl_ordenes.php";

if ($method == "POST") {
    $num_variables = count($request);
    if ($num_variables == 2) {
        if ($request[1] == "electronica")                    showResponse(facturar());
        if ($request[1] == "anular")                         showResponse(anular());
        if ($request[1] == "incrementar-secuencial")         showResponse(incrementarSecuencial());
        if ($request[1] == "reintentar-inventario")          showResponse(reintentarInventario());
        if ($request[1] == "reintentar-reversion-inventario") showResponse(reintentarReversionInventario());
        if ($request[1] == "ver-inventario")                 showResponse(verInventario());
    }
    showResponse(['success' => 0, 'mensaje' => 'Evento no existente en Methodo POST']);
} else {
    showResponse(['success' => 0, 'mensaje' => "El metodo $method para Facturacion aun no esta disponible."]);
}

/**
 * Punto único de facturación: resuelve si la sucursal/empresa factura con Contifico
 * o Runfood (tb_empresa_facturacion) y delega en el BillingProviderInterface correspondiente.
 * El front no necesita saber qué proveedor se usa.
 */
function facturar(): array {
    global $input;

    if (!isset($input['id'])) return ['success' => 0, 'mensaje' => 'Campo id es obligatorio'];
    $id = $input['id'];

    $ClOrdenes = new cl_ordenes();
    $orden = $ClOrdenes->get_orden_array($id);
    if (!$orden) return ['success' => 0, 'mensaje' => 'La orden no existe'];

    if (ExistFacturaToOrden($id)) return ['success' => 0, 'mensaje' => "La orden $id ya tiene una factura creada"];

    $provider = BillingProviderFactory::makeForEmpresa(cod_empresa);
    if (!$provider) return ['success' => 0, 'mensaje' => 'La empresa no tiene un sistema de facturación electrónica habilitado'];

    $infoFacturacion = $provider->getInfoSucursal($orden["cod_sucursal"]);
    if (!$infoFacturacion) return ['success' => 0, 'mensaje' => 'La sucursal no tiene configurado un pto de emisión'];
    if ((int)$infoFacturacion["facturar"] == 0) return ['success' => -1, 'mensaje' => 'La opción de facturar no está habilitada'];

    $msgError = "";
    $schema = $provider->buildSchema($id, $infoFacturacion, $msgError);
    if (!$schema) return ['success' => 0, 'mensaje' => $msgError, 'detail' => 'Error en buildSchema'];

    $result = $provider->sendInvoice($id, $schema, $infoFacturacion);
    if ($result['success']) {
        saveOrdenFactura(
            $id,
            $result['external_id'],
            $result['document_number'],
            $provider->getCodigoSistema(),
            $result['cod_proveedor'],
            $result['tipo_documento'],
            $result['estado']
        );
        $result['inventario'] = $provider->adjustInventory($id, "EGR");
        saveEstadoInventario($id, mapEstadoInventarioEgreso($result['inventario']));
    }
    return $result;
}

/**
 * NO_APLICA: nada que debitar (sucursal sin inventario o sin movimientos que mover).
 * NO_DEBITADO: se intentó pero Contifico rechazó/falló la llamada — reintentable.
 * DEBITADO: Contifico confirmó el egreso.
 */
function mapEstadoInventarioEgreso(array $resultInventario): string {
    if (!empty($resultInventario['skipped'])) return 'NO_APLICA';
    return $resultInventario['success'] ? 'DEBITADO' : 'NO_DEBITADO';
}

function mapEstadoInventarioReversion(array $resultInventario): string {
    return $resultInventario['success'] ? 'REVERTIDO' : 'NO_REVERTIDO';
}

/**
 * Parche manual para cuando el egreso de inventario falló (Contifico ya tiene la factura, pero
 * el movimiento de inventario no se pudo mover) — solo reintenta el egreso, no la factura.
 */
function reintentarInventario(): array {
    global $input;

    if (!isset($input['id'])) return ['success' => 0, 'mensaje' => 'Campo id es obligatorio'];
    $id = $input['id'];

    $ClOrdenes = new cl_ordenes();
    $orden = $ClOrdenes->get_orden_array($id);
    if (!$orden) return ['success' => 0, 'mensaje' => 'La orden no existe'];

    $factura = getUltimaFacturaOrden($id);
    if (!$factura) return ['success' => 0, 'mensaje' => "La orden $id no tiene una factura creada"];
    if (!in_array($factura['estado'], ['CREADA', 'EMITIDA_SRI'])) {
        return ['success' => 0, 'mensaje' => "La factura de la orden $id no está activa (estado: {$factura['estado']}), no se puede debitar inventario"];
    }
    // NO_APLICA también es reintentable: adjustInventory() reevalúa la config de la sucursal en
    // vivo, así que si se activó el check de inventario después del envío original, esto lo
    // corrige solo (si sigue sin aplicar, vuelve a quedar en NO_APLICA sin efecto negativo).
    if (!in_array($factura['estado_inventario'], ['NO_DEBITADO', 'NO_APLICA'])) {
        return ['success' => 0, 'mensaje' => "El inventario de la orden $id está en estado '{$factura['estado_inventario']}', no requiere reintento"];
    }

    $provider = BillingProviderFactory::makeForEmpresa(cod_empresa);
    if (!$provider) return ['success' => 0, 'mensaje' => 'La empresa no tiene un sistema de facturación electrónica habilitado'];
    if (!($provider instanceof ContificoProvider)) {
        return ['success' => 0, 'mensaje' => 'Esta acción solo aplica para facturación con Contifico'];
    }

    $infoFacturacion = $provider->getInfoSucursal($orden["cod_sucursal"]);
    if (!$infoFacturacion) return ['success' => 0, 'mensaje' => 'La sucursal no tiene configurado un pto de emisión'];

    $resultInventario = $provider->adjustInventory($id, "EGR");
    saveEstadoInventario($id, mapEstadoInventarioEgreso($resultInventario));
    return $resultInventario;
}

/**
 * Igual que reintentarInventario() pero para la reversa (ING) cuando la factura ya está
 * anulada y el egreso original sí se había debitado, pero la reversa falló.
 */
function reintentarReversionInventario(): array {
    global $input;

    if (!isset($input['id'])) return ['success' => 0, 'mensaje' => 'Campo id es obligatorio'];
    $id = $input['id'];

    $ClOrdenes = new cl_ordenes();
    $orden = $ClOrdenes->get_orden_array($id);
    if (!$orden) return ['success' => 0, 'mensaje' => 'La orden no existe'];

    $factura = getUltimaFacturaOrden($id);
    if (!$factura) return ['success' => 0, 'mensaje' => "La orden $id no tiene una factura creada"];
    if ($factura['estado'] !== 'ANULADA') {
        return ['success' => 0, 'mensaje' => "La orden $id no está anulada"];
    }
    if ($factura['estado_inventario'] !== 'NO_REVERTIDO') {
        return ['success' => 0, 'mensaje' => "El inventario de la orden $id está en estado '{$factura['estado_inventario']}', no requiere reintento"];
    }

    $provider = BillingProviderFactory::makeForEmpresa(cod_empresa);
    if (!$provider) return ['success' => 0, 'mensaje' => 'La empresa no tiene un sistema de facturación electrónica habilitado'];
    if (!($provider instanceof ContificoProvider)) {
        return ['success' => 0, 'mensaje' => 'Esta acción solo aplica para facturación con Contifico'];
    }

    $infoFacturacion = $provider->getInfoSucursal($orden["cod_sucursal"]);
    if (!$infoFacturacion) return ['success' => 0, 'mensaje' => 'La sucursal no tiene configurado un pto de emisión'];

    $resultInventario = $provider->adjustInventory($id, "ING");
    saveEstadoInventario($id, mapEstadoInventarioReversion($resultInventario));
    return $resultInventario;
}

/**
 * Detalle de qué se debitó/revertió (o qué se debitaría/revertiría) en inventario para una
 * orden. DEBITADO/REVERTIDO leen el payload guardado en el envío real (fiel a lo que pasó,
 * aunque después cambien ingredientes/precios); NO_DEBITADO/NO_APLICA/NO_REVERTIDO recalculan
 * en vivo porque todavía no hay nada persistido que mostrar.
 */
function verInventario(): array {
    global $input;

    if (!isset($input['id'])) return ['success' => 0, 'mensaje' => 'Campo id es obligatorio'];
    $id = $input['id'];

    $ClOrdenes = new cl_ordenes();
    $orden = $ClOrdenes->get_orden_array($id);
    if (!$orden) return ['success' => 0, 'mensaje' => 'La orden no existe'];

    $factura = getUltimaFacturaOrden($id);
    if (!$factura || !$factura['estado_inventario']) {
        return ['success' => 0, 'mensaje' => "La orden $id no tiene movimiento de inventario asociado"];
    }

    $provider = BillingProviderFactory::makeForEmpresa(cod_empresa);
    if (!$provider) return ['success' => 0, 'mensaje' => 'La empresa no tiene un sistema de facturación electrónica habilitado'];
    if (!($provider instanceof ContificoProvider)) {
        return ['success' => 0, 'mensaje' => 'Esta acción solo aplica para facturación con Contifico'];
    }

    $estado = $factura['estado_inventario'];

    if (in_array($estado, ['DEBITADO', 'REVERTIDO'])) {
        $tipoBuscado = $estado === 'DEBITADO' ? 'EGR' : 'ING';
        $movimiento = $ClOrdenes->getInventarioOrden($id, $tipoBuscado);
        if (!$movimiento) return ['success' => 0, 'mensaje' => 'No se encontró el movimiento guardado'];
        $inventarioPayload = json_decode($movimiento['payload'], true);
        return [
            'success'      => 1,
            'origen'       => 'HISTORICO',
            'inventario'   => $inventarioPayload,
            'fecha'        => $movimiento['fecha'],
            'id_contifico' => $movimiento['id'],
            'codigo'       => $movimiento['codigo'],
            'descripcion'  => $inventarioPayload['descripcion'] ?? null,
        ];
    }

    $infoFacturacion = $provider->getInfoSucursal($orden["cod_sucursal"]);
    if (!$infoFacturacion) return ['success' => 0, 'mensaje' => 'La sucursal no tiene configurado un pto de emisión'];

    $tipoPreview = $estado === 'NO_REVERTIDO' ? 'ING' : 'EGR';
    $preview = $provider->previewInventory($id, $tipoPreview);
    if (!$preview['success']) return $preview;
    if (!empty($preview['skipped'])) {
        return ['success' => 0, 'mensaje' => $preview['mensaje']];
    }
    return ['success' => 1, 'origen' => 'PREVIEW', 'inventario' => $preview['inventario']];
}

/**
 * Parche manual para cuando Contifico ya aceptó una factura pero el secuencial local
 * no llegó a subir (proceso caído entre el envío y el incremento). Solo hace un +1 al
 * secuencial de la sucursal/tipo de documento correspondiente; no reintenta el envío.
 */
function incrementarSecuencial(): array {
    global $input;

    if (!isset($input['id'])) return ['success' => 0, 'mensaje' => 'Campo id es obligatorio'];
    $id = $input['id'];

    $ClOrdenes = new cl_ordenes();
    $orden = $ClOrdenes->get_orden_array($id);
    if (!$orden) return ['success' => 0, 'mensaje' => 'La orden no existe'];

    $provider = BillingProviderFactory::makeForEmpresa(cod_empresa);
    if (!$provider) return ['success' => 0, 'mensaje' => 'La empresa no tiene un sistema de facturación electrónica habilitado'];
    if (!($provider instanceof ContificoProvider)) {
        return ['success' => 0, 'mensaje' => 'Esta acción solo aplica para facturación con Contifico'];
    }

    if (ExistFacturaToOrden($id)) {
        return ['success' => 0, 'mensaje' => "La orden $id ya tiene una factura creada, no es necesario subir el secuencial"];
    }

    $infoFacturacion = $provider->getInfoSucursal($orden["cod_sucursal"]);
    if (!$infoFacturacion) return ['success' => 0, 'mensaje' => 'La sucursal no tiene configurado un pto de emisión'];

    return $provider->bumpSecuencial($infoFacturacion);
}

function anular(): array {
    global $input;

    if (!isset($input['id'])) return ['success' => 0, 'mensaje' => 'Campo id es obligatorio'];
    $id = $input['id'];

    $ClOrdenes = new cl_ordenes();
    $orden = $ClOrdenes->get_orden_array($id);
    if (!$orden) return ['success' => 0, 'mensaje' => 'La orden no existe'];

    $provider = BillingProviderFactory::makeForEmpresa(cod_empresa);
    if (!$provider) return ['success' => 0, 'mensaje' => 'La empresa no tiene un sistema de facturación electrónica habilitado'];

    $msgError = "";
    if (!$provider->canVoid($id, $msgError)) return ['success' => 0, 'mensaje' => $msgError];

    $ordFactura = ExistFacturaToOrden($id);
    if (!$ordFactura) return ['success' => 0, 'mensaje' => "La orden $id no tiene una factura electronica creada"];

    $infoFacturacion = $provider->getInfoSucursal($orden["cod_sucursal"]);
    if (!$infoFacturacion) return ['success' => 0, 'mensaje' => 'La sucursal no tiene configurado un pto de emisión'];

    $result = $provider->voidInvoice($id, $ordFactura, $infoFacturacion);
    if ($result['success']) {
        AnularOrdenFactura($id);
        // Solo se intenta revertir si de verdad se había debitado; si estaba NO_APLICA o
        // NO_DEBITADO no hay nada que devolver a Contifico y el estado se deja como está.
        $factura = getUltimaFacturaOrden($id);
        if ($factura && $factura['estado_inventario'] === 'DEBITADO') {
            $result['inventario'] = $provider->adjustInventory($id, "ING");
            saveEstadoInventario($id, mapEstadoInventarioReversion($result['inventario']));
        }
    }
    return $result;
}

<?php
require_once "clases/providers/BillingProviderFactory.php";
require_once "helpers/billing_helpers.php";
require_once "clases/cl_ordenes.php";

if ($method == "POST") {
    $num_variables = count($request);
    if ($num_variables == 2) {
        if ($request[1] == "electronica") showResponse(facturar());
        if ($request[1] == "anular")      showResponse(anular());
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
    }
    return $result;
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
        $result['inventario'] = $provider->adjustInventory($id, "ING");
    }
    return $result;
}

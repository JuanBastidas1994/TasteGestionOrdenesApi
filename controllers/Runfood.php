<?php
require_once "clases/providers/RunfoodProvider.php";
require_once "helpers/billing_helpers.php";

$provider = new RunfoodProvider();

if ($method == "POST") {
    $num_variables = count($request);
    if ($num_variables == 2) {
        if ($request[1] == "electronica") showResponse(facturar($provider));
        if ($request[1] == "anular")      showResponse(anular($provider));
    }
    showResponse(['success' => 0, 'mensaje' => 'Evento no existente en Methodo POST']);
} else {
    showResponse(['success' => 0, 'mensaje' => "El metodo $method para Runfood aun no esta disponible."]);
}

function facturar(RunfoodProvider $provider): array {
    global $input;

    if (!isset($input['id'])) return ['success' => 0, 'mensaje' => 'Campo id es obligatorio'];
    $id = $input['id'];

    require_once "clases/cl_ordenes.php";
    $ClOrdenes = new cl_ordenes();
    $orden = $ClOrdenes->get_orden_array($id);
    if (!$orden) return ['success' => 0, 'mensaje' => 'La orden no existe'];

    if (ExistFacturaToOrden($id)) return ['success' => 0, 'mensaje' => "La orden $id ya tiene una factura creada"];

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
    }
    return $result;
}

function anular(RunfoodProvider $provider): array {
    global $input;

    if (!isset($input['id'])) return ['success' => 0, 'mensaje' => 'Campo id es obligatorio'];
    $id = $input['id'];

    require_once "clases/cl_ordenes.php";
    $ClOrdenes = new cl_ordenes();

    // Runfood exige que la orden esté anulada localmente antes de anularla en el sistema externo
    $orden = $ClOrdenes->getOrdenAnulada($id);
    if (!$orden) return ['success' => 0, 'mensaje' => 'Una orden no puede anularse electronicamente si no se ha anulado localmente'];

    $ordFactura = ExistFacturaToOrden($id);
    if (!$ordFactura) return ['success' => 0, 'mensaje' => "La orden $id no tiene una factura electronica creada"];

    $infoFacturacion = $provider->getInfoSucursal($orden["cod_sucursal"]);
    if (!$infoFacturacion) return ['success' => 0, 'mensaje' => 'La sucursal no tiene configurado un pto de emisión'];

    $result = $provider->voidInvoice($id, $ordFactura, $infoFacturacion);
    if ($result['success']) AnularOrdenFactura($id);
    return $result;
}

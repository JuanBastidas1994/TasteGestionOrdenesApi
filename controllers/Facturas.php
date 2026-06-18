<?php
require_once "clases/providers/ContificoProvider.php";
require_once "helpers/billing_helpers.php";
require_once "clases/cl_facturas.php";

$ClFacturas = new cl_facturas();
$provider   = new ContificoProvider();

if ($method == "GET") {
    $num_variables = count($request);
    if ($num_variables == 1) {
        $sucursales = $ClSucursales->lista();
        if (count($sucursales) > 0) {
            $return['success'] = 1;
            $return['mensaje'] = "Correcto";
            $return['data']    = $sucursales;
        } else {
            $return['success'] = 0;
            $return['mensaje'] = "No hay sucursales";
        }
        showResponse($return);
    }
    showResponse(['success' => 0, 'mensaje' => 'Evento no existente']);
} else if ($method == "POST") {
    $num_variables = count($request);
    if ($num_variables == 2) {
        if ($request[1] == "electronica") showResponse(facturar($provider));
        if ($request[1] == "anular")      showResponse(anular($provider));
        if ($request[1] == "cobro")       showResponse(cobro($provider));
    }
    showResponse(['success' => 0, 'mensaje' => 'Evento no existente en Methodo POST']);
} else {
    showResponse(['success' => 0, 'mensaje' => "El metodo $method para Facturas aun no esta disponible."]);
}

function facturar(ContificoProvider $provider): array {
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

function anular(ContificoProvider $provider): array {
    global $input;

    if (!isset($input['id'])) return ['success' => 0, 'mensaje' => 'Campo id es obligatorio'];
    $id = $input['id'];

    require_once "clases/cl_ordenes.php";
    $ClOrdenes = new cl_ordenes();
    $orden = $ClOrdenes->get_orden_array($id);
    if (!$orden) return ['success' => 0, 'mensaje' => 'La orden no existe'];

    $ordFactura = ExistFacturaToOrden($id);
    if (!$ordFactura) return ['success' => 0, 'mensaje' => "La orden $id no tiene una factura electronica creada"];

    $infoFacturacion = $provider->getInfoSucursal($orden["cod_sucursal"]);
    if (!$infoFacturacion) return ['success' => 0, 'mensaje' => 'La sucursal no tiene configurado un pto de emisión'];
    if ((int)$infoFacturacion["facturar"] == 0) return ['success' => -1, 'mensaje' => 'La opción de facturar no está habilitada'];

    $result = $provider->voidInvoice($id, $ordFactura, $infoFacturacion);
    if ($result['success']) AnularOrdenFactura($id);
    return $result;
}

function cobro(ContificoProvider $provider): array {
    global $input;

    if (!isset($input['id'])) return ['success' => 0, 'mensaje' => 'Campo id es obligatorio'];
    $id = $input['id'];

    $ordFactura = ExistFacturaToOrden($id);
    if (!$ordFactura) return ['success' => 0, 'mensaje' => "La orden $id no tiene una factura electronica creada"];

    return $provider->addPayment($id, $ordFactura);
}

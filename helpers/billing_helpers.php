<?php
/**
 * Funciones compartidas para todos los proveedores de facturación.
 * No referenciar constantes específicas de proveedor (cod_sistema_facturacion).
 */

function ExistFacturaToOrden($cod_orden) {
    $query = "SELECT * FROM tb_orden_factura_electronica
              WHERE cod_orden = $cod_orden
              AND estado IN ('CREADA', 'EMITIDA_SRI')";
    return Conexion::buscarRegistro($query);
}

function saveOrdenFactura($pcod_orden, $pclaveAcceso, $pnumFactura, $cod_sistema, $cod_proveedor, $tipo_documento, $estado = "CREADA") {
    $query = "INSERT INTO tb_orden_factura_electronica(cod_orden, num_factura, clave_acceso, estado, cod_sistema_facturacion, cod_contifico_empresa, tipo)
              VALUES('$pcod_orden','$pnumFactura','$pclaveAcceso','$estado','$cod_sistema', $cod_proveedor, '$tipo_documento')";
    return Conexion::ejecutar($query, NULL);
}

function AnularOrdenFactura($pcod_orden) {
    $query = "UPDATE tb_orden_factura_electronica SET estado = 'ANULADA' WHERE cod_orden = $pcod_orden";
    return Conexion::ejecutar($query, NULL);
}

/** Última fila de facturación de la orden (una orden puede tener varias si se anuló y reenvió). */
function getUltimaFacturaOrden($cod_orden) {
    $query = "SELECT * FROM tb_orden_factura_electronica
              WHERE cod_orden = $cod_orden
              ORDER BY cod_orden_factura_electronica DESC LIMIT 1";
    return Conexion::buscarRegistro($query);
}

/**
 * NO_APLICA / NO_DEBITADO / DEBITADO describen el egreso de inventario por la venta;
 * NO_REVERTIDO / REVERTIDO describen el ingreso de reversa cuando la factura se anula.
 */
function saveEstadoInventario($cod_orden, $estado) {
    $query = "UPDATE tb_orden_factura_electronica
              SET estado_inventario = '$estado'
              WHERE cod_orden_factura_electronica = (
                  SELECT cod_orden_factura_electronica FROM (
                      SELECT MAX(cod_orden_factura_electronica) AS cod_orden_factura_electronica
                      FROM tb_orden_factura_electronica WHERE cod_orden = $cod_orden
                  ) t
              )";
    return Conexion::ejecutar($query, NULL);
}

/**
 * Motivo por el que una orden no se pudo facturar (lo muestra el ⚠ de Reenvío de facturas).
 * El motivo va como parámetro: suele traer comillas (nombres de productos, mensajes del proveedor)
 * que rompían el INSERT armado a mano.
 */
function saveErrorFacturacion($cod_orden, $proveedor, $motivo) {
    $query = "INSERT INTO tb_orden_errores(cod_orden, tipo, proveedor, motivo, fecha)
              VALUES(:cod_orden, 'FACTURA', :proveedor, :motivo, :fecha)";
    return Conexion::ejecutar($query, [
        ':cod_orden' => $cod_orden,
        ':proveedor' => $proveedor,
        ':motivo'    => mb_substr((string)$motivo, 0, 2000),
        ':fecha'     => fecha(),
    ]);
}

/**
 * Comanda = pedido abierto en un sistema externo que también es POS/cocina (Runfood).
 * Vive aparte de tb_orden_factura_electronica: se abre al salir de ENTRANTE y se factura al entregar.
 */
function getComandaOrden($cod_orden) {
    $query = "SELECT * FROM tb_orden_comanda
              WHERE cod_orden = $cod_orden
              ORDER BY cod_orden_comanda DESC LIMIT 1";
    return Conexion::buscarRegistro($query);
}

function saveComandaOrden($cod_orden, $cod_sistema, $cod_proveedor, $external_order_id, $external_tab_id, $order_number, $estado = "ABIERTA") {
    $tab = ($external_tab_id === null) ? "NULL" : "'$external_tab_id'";
    $query = "INSERT INTO tb_orden_comanda(cod_orden, cod_sistema_facturacion, cod_proveedor, external_order_id, external_tab_id, order_number, estado)
              VALUES($cod_orden, $cod_sistema, $cod_proveedor, '$external_order_id', $tab, '$order_number', '$estado')";
    return Conexion::ejecutar($query, NULL);
}

function setEstadoComandaOrden($cod_orden_comanda, $estado) {
    $query = "UPDATE tb_orden_comanda SET estado = '$estado' WHERE cod_orden_comanda = $cod_orden_comanda";
    return Conexion::ejecutar($query, NULL);
}

function empresaGravaIva($cod_empresa) {
    $query = "SELECT * FROM tb_empresas WHERE cod_empresa = $cod_empresa";
    $resp = Conexion::buscarRegistro($query);
    return $resp ? $resp['envio_grava_iva'] : 0;
}

/**
 * $cod_sistema es opcional por compatibilidad, pero conviene pasarlo: cod_contifico_empresa
 * guarda ids de espacios distintos según el sistema (Contifico: empresa; Runfood: cod_sucursal)
 * y sin filtrar por sistema un mismo número podría cruzar mapeos.
 */
function getProductoById($cod_producto, $cod_proveedor_empresa, $cod_sistema = null) {
    $filtroSistema = $cod_sistema ? "AND cod_sistema_facturacion = $cod_sistema" : "";
    $query = "SELECT * FROM tb_productos_facturacion
              WHERE cod_producto = $cod_producto
              AND cod_contifico_empresa = $cod_proveedor_empresa
              $filtroSistema";
    return Conexion::buscarRegistro($query);
}

function getEnvioyAdicionalByAlias($alias, $cod_empresa, $ruc_id, $cod_sistema) {
    $query = "SELECT * FROM tb_productos_envio_facturacion
              WHERE alias = '$alias'
              AND cod_empresa = $cod_empresa
              AND cod_contifico_empresa = $ruc_id
              AND cod_sistema_facturacion = $cod_sistema";
    return Conexion::buscarRegistro($query);
}

function getFacturacionElectronica($cod_empresa) {
    $query = "SELECT ef.*, f.nombre
              FROM tb_empresa_facturacion ef
              INNER JOIN tb_sistema_facturacion f ON ef.cod_sistema_facturacion = f.cod_sistema_facturacion
              WHERE ef.cod_empresa = $cod_empresa AND ef.estado = 'A'
              ORDER BY ef.prioridad";
    return Conexion::buscarVariosRegistro($query);
}

function noRound($value, $option) {
    if ($option) {
        $parts = explode(".", $value);
        return count($parts) > 1 ? $parts[0] . "." . substr($parts[1], 0, 2) : $value;
    }
    return number_format($value, 2);
}

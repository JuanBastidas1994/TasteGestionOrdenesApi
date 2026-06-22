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

function empresaGravaIva($cod_empresa) {
    $query = "SELECT * FROM tb_empresas WHERE cod_empresa = $cod_empresa";
    $resp = Conexion::buscarRegistro($query);
    return $resp ? $resp['envio_grava_iva'] : 0;
}

function getProductoById($cod_producto, $cod_proveedor_empresa) {
    $query = "SELECT * FROM tb_productos_facturacion
              WHERE cod_producto = $cod_producto
              AND cod_contifico_empresa = $cod_proveedor_empresa";
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

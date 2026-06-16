<?php

require_once "helpers/fidelizacion/logicaClasica.php";
require_once "helpers/fidelizacion/logicaSimple.php";

/**
 * Orquestador de fidelización.
 * Consulta la configuración de la empresa, determina el tipo de lógica
 * (clasico|simple) y delega al helper correspondiente.
 *
 * @param int $cod_orden   Orden a procesar
 * @param int $cod_empresa Empresa propietaria de la orden
 */
function procesarFidelizacion(int $cod_orden, int $cod_empresa): bool
{
    require_once "clases/cl_empresas.php";
    require_once "clases/cl_clientes.php";

    // Configuración de fidelización de la empresa
    $Clempresas = new cl_empresas();
    $config = $Clempresas->getFidelizacionById($cod_empresa);
    if (!$config) return false;

    $tipo = isset($config['tipo_fidelizacion']) ? $config['tipo_fidelizacion'] : 'clasico';

    // Datos de la orden para obtener cod_usuario
    $ordenDb = Conexion::buscarRegistro(
        "SELECT cod_usuario FROM tb_orden_cabecera WHERE cod_orden = $cod_orden"
    );
    if (!$ordenDb) return false;

    // Cargar cliente asociado al usuario de la orden
    $cliente = new cl_clientes();
    if (!$cliente->getByUser((int)$ordenDb['cod_usuario'])) return false;

    // Monto a acumular: pagos que no usaron saldo de billetera (forma_pago != 'P')
    $pagos = Conexion::buscarRegistro(
        "SELECT SUM(monto) as monto FROM tb_orden_pagos
         WHERE cod_orden = $cod_orden AND forma_pago NOT IN('P')"
    );
    $monto = ($pagos && $pagos['monto'] !== null) ? (float)$pagos['monto'] : 0;

    if ($monto <= 0) return false;

    $orden = [
        'cod_orden' => $cod_orden,
        'monto'     => $monto,
    ];

    if ($tipo === 'simple') {
        return procesarLogicaSimple($orden, $config, $cliente);
    }

    return procesarLogicaClasica($orden, $config, $cliente);
}

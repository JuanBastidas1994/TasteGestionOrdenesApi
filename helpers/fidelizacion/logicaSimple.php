<?php

/**
 * Lógica simple de fidelización.
 * El monto comprado se acumula en tb_clientes_saldos como progreso hacia la meta.
 * Cuando el acumulado alcanza meta_puntos se acredita $1.00 en tb_cliente_dinero
 * por cada vez que quepa la meta. El remanente queda en tb_clientes_saldos.
 */
function procesarLogicaSimple(array $orden, array $config, cl_clientes $cliente): bool
{
    $metaPuntos = (float)$config['meta_puntos'];
    $diasSaldo  = (int)$config['cant_dias_caducidad_saldo'];
    $diasDinero = (int)$config['cant_dias_caducidad_dinero'];
    $cod_orden  = (int)$orden['cod_orden'];
    $monto      = (float)$orden['monto'];

    if ($metaPuntos <= 0) return false;

    $saldoActual    = (float)$cliente->GetSaldo();
    $totalAcumulado = $saldoActual + $monto;

    $metasCumplidas = intval($totalAcumulado / $metaPuntos);
    $nuevoSaldo     = $totalAcumulado - ($metasCumplidas * $metaPuntos);

    // Por cada meta cumplida: $1.00 canjeable en tb_cliente_dinero
    for ($i = 0; $i < $metasCumplidas; $i++) {
        $cliente->AddDinero(1.00, $cliente->cod_cliente, 3, $diasDinero, $cod_orden);
    }

    return (bool)$cliente->ActualizarSaldo($cliente->cod_cliente, $nuevoSaldo, $saldoActual, $diasSaldo, $cod_orden);
}

<?php

/**
 * Lógica clásica de fidelización.
 * El monto + residuo anterior se divide entre divisor_puntos.
 * Los puntos ganados se convierten en dinero según el nivel del cliente (dinero_x_punto).
 * El residuo que no llega al divisor queda en tb_clientes_saldos para la siguiente compra.
 */
function procesarLogicaClasica(array $orden, array $config, cl_clientes $cliente): bool
{
    $divisor   = (int)$config['divisor_puntos'];
    $diasSaldo = (int)$config['cant_dias_caducidad_saldo'];
    $diasPuntos = (int)$config['cant_dias_caducidad_puntos'];
    $diasDinero = (int)$config['cant_dias_caducidad_dinero'];
    $cod_orden  = (int)$orden['cod_orden'];
    $amount     = (float)$orden['monto'];

    $saldoActual    = (float)$cliente->GetSaldo();
    $puntosActuales = (int)$cliente->GetPuntos();
    $nivelActual    = $cliente->getNivel($puntosActuales);

    if (!$nivelActual) return false;

    $amount    += $saldoActual;                          // monto + residuo acumulado
    $pointsWin  = intval($amount / $divisor);            // puntos enteros ganados
    $newBalance = $amount - ($pointsWin * $divisor);     // nuevo residuo

    if ($pointsWin > 0) {
        $newPoints = $puntosActuales + $pointsWin;

        if ($newPoints > $nivelActual['punto_final']) {
            // El cliente sube de nivel: los puntos se reparten entre el nivel anterior y el nuevo
            $newLevel = $cliente->getNivel($newPoints);
            if (!$newLevel) return false;

            $pointNewLevel = $newPoints - $newLevel['punto_inicial'];
            $pointOldLevel = $pointsWin - $pointNewLevel;

            // Puntos acreditados en el nuevo nivel
            $montoNewLevel = $pointNewLevel * $newLevel['dinero_x_punto'];
            $cliente->AddDinero($montoNewLevel, $cliente->cod_cliente, 3, $diasDinero, $cod_orden);
            $cliente->AddPuntos($cliente->cod_cliente, $newLevel['posicion'], $pointNewLevel, $montoNewLevel, $diasPuntos, $cod_orden);

            // Puntos acreditados en el nivel anterior
            $montoOldLevel = $pointOldLevel * $nivelActual['dinero_x_punto'];
            $cliente->AddDinero($montoOldLevel, $cliente->cod_cliente, 3, $diasDinero, $cod_orden);
            $cliente->AddPuntos($cliente->cod_cliente, $nivelActual['posicion'], $pointOldLevel, $montoOldLevel, $diasPuntos, $cod_orden);
        } else {
            // Puntos dentro del mismo nivel
            $monto = $pointsWin * $nivelActual['dinero_x_punto'];
            $cliente->AddDinero($monto, $cliente->cod_cliente, 3, $diasDinero, $cod_orden);
            $cliente->AddPuntos($cliente->cod_cliente, $nivelActual['posicion'], $pointsWin, $monto, $diasPuntos, $cod_orden);
        }
    }

    return (bool)$cliente->ActualizarSaldo($cliente->cod_cliente, $newBalance, $saldoActual, $diasSaldo, $cod_orden);
}

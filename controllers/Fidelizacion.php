<?php
	/*	Variables Heredadas del Index
		$method - POST, GET, PUT, DELETE, etc.
		$request - Url y variables GET
		$input - Solo metodo POST, PUT */

require_once "clases/cl_empresas.php";
require_once "clases/cl_clientes.php";
require_once "clases/cl_usuarios.php";
$Clempresas = new cl_empresas();
$Clclientes = new cl_clientes();
$Clusuarios = new cl_usuarios();

$divisor = 0;
$puntosxdivisor = 0;
$config = $Clempresas->getFidelizacion();
if($config){
    $divisor = $config['divisor_puntos'];
    $puntosxdivisor = $config['monto_puntos'];
}

if($method == "GET"){
	$num_variables = count($request);
	if($num_variables == 3){
		if($request[1] == "calcular"){
			$return = calcular($request[2]);
			showResponse($return);
		}
		if($request[1] == "calcular_orden"){
			$return = calcular_orden($request[2]);
			showResponse($return);
		}
	}

	$return['success']= 0;
	$return['mensaje']= "Evento no existente";
	showResponse($return);
}
else{
	$return['success']= 0;
	$return['mensaje']= "El metodo ".$method." para puntos no esta disponible.";
	showResponse($return);
}


/*FUNCIONES*/
function calcular($user_id){
	global $Clusuarios;
	global $Clclientes;
	$usuario = $Clusuarios->get2($user_id);
	if(!$usuario)
		return [ 'success' => 0, 'mensaje' => 'Usuario no existe'];
	
	if(!$Clclientes->getByUser($user_id))
		return [ 'success' => 0, 'mensaje' => 'Usuario no tiene un cliente creado'];

	//Ordenes por acumular del cliente
	$ordenes = $Clclientes->ordenes_faltantes($user_id);
	if(!$ordenes)
		return [ 'success' => 0, 'mensaje' => 'No hay ordenes por acumular'];
    
    try{
    	foreach($ordenes as $orden){
    		$order_id = $orden['cod_orden'];
        
    		//Restar Dinero
    		$pagosRestar = $Clclientes->getPagosDecrementar($order_id);
    		if($pagosRestar > 0){
    			debitPoints($pagosRestar);
    		}
    		
    		//Acumular
    		$pagosAcumular = $Clclientes->getPagosAumentar($order_id);
    		if($pagosAcumular > 0){
    			calculatePointsOrder($pagosAcumular, $order_id, $user_id);
    		}
    				
    		$Clclientes->orden_complete($order_id);
    	}
    }catch (Exception $e) {
		showResponse(['success' => 0, 'mensaje' => $e->getMessage()]);
	}
	return [ 'success' => 1, 'mensaje' => 'Orden acumulada correctamente'];
}

function calcular_orden($order_id){
	require_once "clases/cl_ordenes.php";
	$Clordenes = new cl_ordenes();

	$orden = $Clordenes->getOrden($order_id);
	if(!$orden)
		return [ 'success' => 0, 'mensaje' => 'Orden no existe'];

	return calcular($orden['cod_usuario']);
}

function calculatePointsOrder($amount, $orderId, $user_id){
	global $Clempresas;
	global $Clclientes;
	$credit = $Clempresas->getFidelizacion();


	if($credit){
		$divisor = $credit['divisor_puntos'];

		$wallet = getWallet($user_id);
		$amount += $wallet['saldo']; //Monto Factura más saldo anterior
		$pointsWin = intval($amount / $divisor);  //Puntos Ganados
		$newBalance = $amount - ($pointsWin * $divisor);    //Saldo restante, es el nuevo saldo

		if ($pointsWin > 0) { //Si ganó puntos, debo calcular dinero
			$newPoints = $wallet['puntos'] + $pointsWin;
			$credit_level = $wallet['nivel'];
			if ($newPoints > $credit_level['punto_final']) { //Verificar si supera el nivel actual
				//Buscar el siguiente nivel
				$newLevel = $Clclientes->getNivel($newPoints);
				if(!$newLevel)
					return false;

				//Puntos nuevo nivel
				$pointNewLevel = $newPoints - $newLevel['punto_inicial'];
				addDinerWalletByPoints($pointNewLevel, $wallet['client_id'], $newLevel, $orderId); //Dar Puntos

				//Puntos nivel anterior
				$pointOldLevel = $pointsWin - $pointNewLevel;
				addDinerWalletByPoints($pointOldLevel, $wallet['client_id'], $credit_level, $orderId); //Dar Puntos
			}else{
				//Dar puntos en el nivel que está
				addDinerWalletByPoints($pointsWin, $wallet['client_id'], $credit_level, $orderId); //Dar Puntos
			}
		}	

		$Clclientes->ActualizarSaldo($wallet['client_id'], $newBalance, $wallet['saldo'], $credit['cant_dias_caducidad_saldo'], $orderId);
		return true;
	}

	return false;
}

function addDinerWalletByPoints($pointsWin, $client_id, $credit_level, $order_id){
	global $Clclientes;
	global $Clempresas;
	['cant_dias_caducidad_puntos' => $pointsExpiration, 
	'cant_dias_caducidad_dinero' => $amountExpiration] = $Clempresas->getFidelizacion();

	$amountWin = $pointsWin * $credit_level['dinero_x_punto'];

	//Dinero
	$Clclientes->AddDinero($amountWin, $client_id, 3, $amountExpiration, $order_id);

	//Puntos
	$Clclientes->AddPuntos($client_id, $credit_level['posicion'], $pointsWin, $amountWin, $pointsExpiration, $order_id);
}

function debitPoints($amount){
	global $Clclientes;
	$balanceAvailable = $Clclientes->GetDinero();
	if($balanceAvailable < $amount){
		throw new \Exception('El dinero que se intenta usar es mayor al disponible en la billetera virtual');
		return false;
	}
	
	$wallets = $Clclientes->getDineroDesglose();
	foreach($wallets as $wallet){
		$id = $wallet['cod_cliente_dinero'];
		$balance = $wallet['saldo'];
		if($amount >= $balance){
			$amount = $amount - $balance;
			$Clclientes->ActualizarDinero($id, 0, 'I');
		}else{
			$newBalance = $balance - $amount;
            $amount = 0;
			$Clclientes->ActualizarDinero($id, $newBalance);
		}

		if($amount == 0){
			return true;
		}
	}
}

?>
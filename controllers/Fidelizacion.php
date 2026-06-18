<?php
	/*	Variables Heredadas del Index
		$method - POST, GET, PUT, DELETE, etc.
		$request - Url y variables GET
		$input - Solo metodo POST, PUT */

require_once "clases/cl_clientes.php";
require_once "clases/cl_usuarios.php";
require_once "helpers/fidelizacion/fidelizacionHelper.php";
$Clclientes = new cl_clientes();
$Clusuarios = new cl_usuarios();

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
    		
    		//Acumular puntos según tipo de fidelización configurado
    		procesarFidelizacion($order_id, cod_empresa);
    				
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
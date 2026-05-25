<?php
require_once "clases/cl_empresas.php";
require_once "clases/cl_sucursales.php";
require_once "clases/cl_ordenes.php";
require_once "clases/cl_usuarios.php";
$ClSucursales = new cl_sucursales();
$ClEmpresas = new cl_empresas();
$Clordenes = new cl_ordenes();
$ClUsuarios = new cl_usuarios();

if($method == "GET"){
	$num_variables = count($request);
	if($num_variables == 3){
		if($request[1] == "orden"){
			showResponse(detalleOrden($request[2]));
		}
	}

	$return['success']= 0;
	$return['mensaje']= "Evento no existente para órdenes";
	showResponse($return);
}else if($method == "POST"){
    $num_variables = count($request);
	if($num_variables == 1){
		showResponse(listaOrdenes($input));
	}
	else if($num_variables == 2){
		if($request[1] == "asignar"){
			showResponse(asignarOrden($input));
		}
		if($request[1] == "deleteAsignacion"){
			showResponse(deleteAsignacion($input));
		}
	}
}else{
	$return['success']= 0;
	$return['mensaje']= "El metodo ".$method." para Órdenes aun no esta disponible.";
	showResponse($return);
}

function listaOrdenes($input){
    global $Clordenes;
	//global $ClUsuarios;
// 	global $ClCouriers;
	
    $errorMsg = "";
	$datosObligatorios = array("business_id","status");
    if(!validate($datosObligatorios, $input, $errorMsg)){
        $return['success'] = 0;
        $return['mensaje'] = $errorMsg;
        showResponse($return);
    }
	extract($input);

	$ordenes = $Clordenes->getListOrdersFlota($business_id, $status);
	if($ordenes){
		foreach ($ordenes as $key => $item) {
// 			$ordenes[$key]['fecha'] = fechaLatinoShort($item['fecha']);
			
			//MOTORIZADO
	        $ordenes[$key]['motorizado'] = $Clordenes->getMotorizadoByOrden($item['cod_orden']);
		} 
		$return['success'] = 1;
		$return['mensaje'] = "Lista de Ordenes";
		$return['data'] = $ordenes;
	}else{
		$return['success'] = 0;
		$return['mensaje'] = "No hay órdenes";
	}
	return $return;
}

function detalleOrden($cod_orden){
	global $Clordenes;
	global $ClSucursales;
	global $ClUsuarios;

	$Clordenes->percent_iva = 15;

	//OBTENER DATOS EMPRESAS
	require_once "clases/cl_empresas.php";
	$ClEmpresas = new cl_empresas();
	

	$orden = $Clordenes->getOrdenByFlota($cod_orden);
	if($orden){
		$sucursal = $ClSucursales->getWithoutValidateBusiness($orden['cod_sucursal']);
	    $empresa = $ClEmpresas->getByCode($sucursal['cod_empresa']);

		$orden['latino']['fecha'] = fechaLatinoShort($orden['fecha']);
		$orden['latino']['hora'] = getHourToDateTime($orden['fecha']);
		
		$orden['retiro']['fecha'] = fechaLatinoShort($orden['hora_retiro']);
		$orden['retiro']['hora'] = getHourToDateTime($orden['hora_retiro']);

		$orden['sucursal'] = $sucursal;
		$orden['empresa'] = [
		    'nombre' => $empresa['nombre'],
		    'logo' =>  url_business_assets.$empresa['alias'].'/'.$empresa['logo'],
		];

// 		$orden['detalle'] = $Clordenes->getOrdenDetalle($cod_orden);
		$orden['pagos'] = $Clordenes->getOrdenPagos($cod_orden);
		
		//MOTORIZADO
		$motorizado = $Clordenes->getMotorizadoByOrden($cod_orden);
		$link = "";
		if($motorizado){
		    $link = $Clordenes->getLinkMotorizado($cod_orden);
		}
		$orden['motorizado'] = $motorizado;
		$orden['link'] = $link;
		
		//Cliente
		$orden['cliente'] = $ClUsuarios->get2($orden['cod_usuario']);
		
		//Cliente
		$orden['mis_motos'] = $ClEmpresas->getMotorizadosInternos();
		

		$return['success'] = 1;
		$return['mensaje'] = "Correcto";
		$return['data'] = $orden;
	}else{
		$return['success'] = 0;
		$return['mensaje'] = "Orden $cod_orden no encontrada";
	}
	return $return;
}

function asignarOrden($input){
    global $Clordenes;

    $errorMsg = "";
	$datosObligatorios = array("order_id","moto_id");
    if(!validate($datosObligatorios, $input, $errorMsg)){
        $return['success'] = 0;
        $return['mensaje'] = $errorMsg;
        showResponse($return);
    }
	extract($input);

	$orden = $Clordenes->setOrdenMotorizado($order_id, $moto_id);
	if($orden){
	    $token = passRandom();
	    $link = $Clordenes->setLinkToken($order_id, $token, '');
	
		$return['success'] = 1;
		$return['mensaje'] = "Asignación correcta";
		$return['motorizado'] = $Clordenes->getMotorizado($moto_id);
		$return['token'] = $token;
	}else{
		$return['success'] = 0;
		$return['mensaje'] = "Ocurrio un error al asignar motorizado";
	}
	return $return;
}

function deleteAsignacion($input){
    global $Clordenes;
	//global $ClUsuarios;
// 	global $ClCouriers;
	
    $errorMsg = "";
	$datosObligatorios = array("order_id");
    if(!validate($datosObligatorios, $input, $errorMsg)){
        $return['success'] = 0;
        $return['mensaje'] = $errorMsg;
        showResponse($return);
    }
	extract($input);

	$orden = $Clordenes->deleteMotorizadoAsignacion($order_id);
	if($orden){
	    $Clordenes->destroyLinkToken($order_id);
		$return['success'] = 1;
		$return['mensaje'] = "Motorizado removido";
	}else{
		$return['success'] = 0;
		$return['mensaje'] = "Ocurrio un error al remover el motorizado";
	}
	return $return;
}

?>
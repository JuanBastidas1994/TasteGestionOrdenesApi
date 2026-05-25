<?php
require_once "clases/cl_sucursales.php";
$ClSucursales = new cl_sucursales();

if($method == "GET"){
	$num_variables = count($request);
	if($num_variables == 1){
		$sucursales = $ClSucursales->lista();
        if(count($sucursales)>0){
            $return['success'] = 1;
            $return['mensaje'] = "Correcto";
            $return['data'] = $sucursales;
        }else{
            $return['success'] = 0;
            $return['mensaje'] = "No hay sucursales";
        }
        showResponse($return);
	}else if($num_variables == 2){
		$cod_orden = $request[1];
		showResponse(detalleSucursal($cod_orden));
	}else if($num_variables == 3){
		
	}

	$return['success']= 0;
	$return['mensaje']= "Evento no existente para órdenes";
	showResponse($return);
}
else if($method == "POST"){
	$num_variables = count($request);
	if($num_variables == 1){
	}else if($num_variables == 2){
	    $metodo = $request[1];
		if($metodo == "crear_restriccion"){
			showResponse(crearRestriccion($input));
		}else if($metodo == "eliminar_restriccion"){
			showResponse(eliminarRestriccion($input));
		}else if($metodo == "crear_altademanda"){
			showResponse(crearAltademanda($input));
		}else if($metodo == "eliminar_altademanda"){
			showResponse(eliminarAltademanda($input));
		}
	}else if($num_variables == 3){
	}
	
	$return['success']= 0;
	$return['mensaje']= "Evento no existente para sucursales";
	showResponse($return);
}	
else{
	$return['success']= 0;
	$return['mensaje']= "El metodo ".$method." para Órdenes aun no esta disponible.";
	showResponse($return);
}


function detalleSucursal($cod_sucursal){
	global $ClSucursales;
	$sucursal = $ClSucursales->get($cod_sucursal);
	if($sucursal){
        $sucursal['couriers'] = $ClSucursales->getCouriers($sucursal['id']);

		$return['success'] = 1;
		$return['mensaje'] = "Correcto";
		$return['data'] = $sucursal;
	}else{
		$return['success'] = 0;
		$return['mensaje'] = "Sucursal $cod_sucursal no encontrada";
	}
	return $return;
}

function crearRestriccion($input){
    global $ClSucursales;
    
    $errorMsg = "";
	$datosObligatorios = array("cod_sucursal", "tiempo");
	if(!validate($datosObligatorios, $input, $errorMsg)){
		$return['success'] = 0;
		$return['mensaje'] = $errorMsg;
		return $return;
	}
	extract($input);
	
	$fecha = fecha();
	if($tiempo != "all")
	    $fecha_fin = AddIntervalo2($fecha, $tiempo);
	else
	    $fecha_fin = fecha_only()." 23:59:59";
	
	$restriccion = $ClSucursales->crearRestriccion($cod_sucursal, $fecha, $fecha_fin);
	if($restriccion){
	    $return['success'] = 1;
		$return['mensaje'] = "Restricción creada correctamente";
		$return['fechaFin'] = $fecha_fin;
	}else{
	    $return['success'] = 0;
		$return['mensaje'] = "No se pudo crear la restricción, intentelo nuevamente";
	}
	return $return;
}

function eliminarRestriccion($input){
    global $ClSucursales;
    
    $errorMsg = "";
	$datosObligatorios = array("cod_sucursal");
	if(!validate($datosObligatorios, $input, $errorMsg)){
		$return['success'] = 0;
		$return['mensaje'] = $errorMsg;
		return $return;
	}
	extract($input);
	
	$fecha = fecha();
	$restriccion = $ClSucursales->restriccionDisponibilidadBySucursal($cod_sucursal, $fecha);
	if($restriccion){
	    if($ClSucursales->eliminarRestriccion($restriccion['cod_sucursal_festivos'])){
	        $return['success'] = 1;
		    $return['mensaje'] = "Restricción eliminada correctamente";
	    }else{
	        $return['success'] = 0;
		    $return['mensaje'] = "No se pudo eliminar la restricción";
	    }
	}else{
	    $return['success'] = 0;
		$return['mensaje'] = "No hay restriccion activa en estos momentos";
	}
	$return['restriccion'] = $restriccion;
	return $return;
}

function crearAltademanda($input){
    global $ClSucursales;
    
    $errorMsg = "";
	$datosObligatorios = array("cod_sucursal", "tiempo");
	if(!validate($datosObligatorios, $input, $errorMsg)){
		$return['success'] = 0;
		$return['mensaje'] = $errorMsg;
		return $return;
	}
	extract($input);
	
	$fecha = fecha();
	if($tiempo != "all")
	    $fecha_fin = AddIntervalo2($fecha, $tiempo);
	else
	    $fecha_fin = fecha_only()." 23:59:59";
	
	$restriccion = $ClSucursales->crearAltaDemanda($cod_sucursal, $fecha, $fecha_fin);
	if($restriccion){
	    $return['success'] = 1;
		$return['mensaje'] = "Se aplico la Alta demanda";
		$return['fechaFin'] = $fecha_fin;
	}else{
	    $return['success'] = 0;
		$return['mensaje'] = "No se pudo aplicar la alta demanda, intentelo nuevamente";
	}
	return $return;
}

function eliminarAltademanda($input){
    global $ClSucursales;
    
    $errorMsg = "";
	$datosObligatorios = array("cod_sucursal");
	if(!validate($datosObligatorios, $input, $errorMsg)){
		$return['success'] = 0;
		$return['mensaje'] = $errorMsg;
		return $return;
	}
	extract($input);
	
	$fecha = fecha();
	$restriccion = $ClSucursales->getAltaDemanda($cod_sucursal, $fecha);
	if($restriccion){
	    if($ClSucursales->eliminarAltaDemanda($restriccion['id'])){
	        $return['success'] = 1;
		    $return['mensaje'] = "Alta demanda eliminada correctamente";
	    }else{
	        $return['success'] = 0;
		    $return['mensaje'] = "No se pudo eliminar la alta demanda, intentalo de nuevo";
	    }
	}else{
	    $return['success'] = 0;
		$return['mensaje'] = "No hay Alta demanda activa en estos momentos";
	}
	$return['restriccion'] = $restriccion;
	return $return;
}










?>
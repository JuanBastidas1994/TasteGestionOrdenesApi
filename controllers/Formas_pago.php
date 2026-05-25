<?php
/*	Variables Heredadas del Index
		$method - POST, GET, PUT, DELETE, etc.
		$request - Url y variables GET
		$input - Solo metodo POST, PUT */

require_once "clases/cl_empresas.php";
$ClEmpresas = new cl_empresas();

	if($method == "GET"){
		$num_variables = count($request);
		if($num_variables == 1){
			$formaPago = $ClEmpresas->getFormasPagoEmpresa();
			if($formaPago){
			    //$formaPago = array_map('html_entity_decode', $formaPago);
			    $fpago = [];
			    foreach($formaPago as $fp){
			        $fpago[] = array_map('html_entity_decode', $fp);
			    }
				$return['success'] = 1;
				$return['mensaje'] = "Correcto";
				$return['forma_pago'] = $fpago;
			}else{
				$return['success'] = 0;
				$return['mensaje'] = "No hay datos";
			}
			showResponse($return);
		}else{
			$return['success']= 0;
			$return['mensaje']= "Url no valida para configuracion, por favor revisar los parametros";
			showResponse($return);
		}
	}
	else{
		$return['success']= 0;
		$return['mensaje']= "El metodo ".$method." para configuracion aun no esta disponible.";
		showResponse($return);
	}
?>
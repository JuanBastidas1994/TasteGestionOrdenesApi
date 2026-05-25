<?php
	/*	Variables Heredadas del Index
		$method - POST, GET, PUT, DELETE, etc.
		$request - Url y variables GET
		$input - Solo metodo POST, PUT */

require_once "clases/cl_usuarios.php";
require_once "clases/cl_clientes.php";
$ClUsuarios = new cl_usuarios();

	if($method == "GET"){
		$num_variables = count($request);
		if($num_variables == 2){
		    $cod_usuario = $request[1];
		    showResponse(detalleUsuario($cod_usuario));
		}
		$return['success']= 0;
		$return['mensaje']= "Evento no existente para usuarios";
		showResponse($return);
	}else if($method == "POST"){
	    $num_variables = count($request);
	    if($num_variables == 2){
			$first = $request[1];
			if($first=="login"){
				$return = login();
				showResponse($return);
			}
	    }
	}else{
		$return['success']= 0;
		$return['mensaje']= "El metodo ".$method." para usuarios aun no esta disponible.";
		showResponse($return);
	}

/*FUNCIONES*/
function login(){
	global $ClUsuarios;
	global $input;
	extract($input);

	if(!isset($email) || !isset($password)){
		$return['success'] = 0;
    	$return['mensaje'] = "Falta informacion";
		return $return;
	}

	$resp = $ClUsuarios->LoginWithBusiness($email,$password);
    if($resp){
        require_once "clases/cl_empresas.php";
        $ClEmpresas = new cl_empresas();
        if($ClEmpresas->isPermissionDesktopSystem($resp['cod_empresa'])){
            $permisos = $ClEmpresas->getPermisos($resp['cod_empresa']);
        
            $resp['permisos'] = $permisos;
            $return['success'] = 1;
    	    $return['mensaje'] = "Login correcto";
    	    $return['data'] = $resp;
        }else{
            $return['success'] = 0;
	        $return['mensaje'] = "La empresa no tiene habilitado el servicio de Aplicación Desktop, por favor comuníquese con su asesor";
        }
    }else{
        $return['success'] = 0;
	    $return['mensaje'] = "Usuario y/o password incorrectos, por favor verifique la información ingresada";
    }
    return $return;
}

function detalleUsuario($user_id){
    global $ClUsuarios;
    
    $usuario = $ClUsuarios->get2($user_id);
    if($usuario){
        $usuario['wallet'] = getWallet($user_id); 
        $usuario['ordenes_group'] = $ClUsuarios->getNumOrdersByStatus($user_id);
        $usuario['ordenes_list'] = $ClUsuarios->getOrdenes($user_id); 
        
        $return['success'] = 1;
		$return['mensaje'] = "Correcto";
		$return['data'] = $usuario;
    }else{
        $return['success'] = 0;
		$return['mensaje'] = "Usuario $user_id no encontrado";
    }
    return $return;
}



?>
<?php
/*	Variables Heredadas del Index
		$method - POST, GET, PUT, DELETE, etc.
		$request - Url y variables GET
		$input - Solo metodo POST, PUT */

require_once "clases/cl_notificaciones.php";
$ClNotificaciones = new cl_notificaciones();


if($method == "GET"){
	$return['success']= 0;
	$return['mensaje']= "Evento no existente para notificaciones";
	showResponse($return);
}
else if($method == "POST"){
	$num_variables = count($request);
	if($num_variables == 1){
        $metodo = $request[0];
		if($metodo == "notificar"){
            $info = $input;
            if(isset($info['topic']) && isset($info['title']) && isset($info['message']) && isset($info['type'])){
                showResponse(notificarUsuario($input));
			}
            else{
				$return['success'] = 0;
				$return['mensaje'] = "Información incorrecta, revise los parámetros enviados";
				showResponse($return);
			}
		}
	}
	
	$return['success']= 0;
	$return['mensaje']= "Evento no existente para Notificaciones";
	showResponse($return);
}	
else{
	$return['success']= 0;
	$return['mensaje']= "El metodo ".$method." para notificaciones aun no esta disponible.";
	showResponse($return);
}

function notificarUsuario($request){
    global $ClNotificaciones;
	$info = $request;
	
	$configNotificactions = $ClNotificaciones->getConfigNotification(cod_empresa);
	if($configNotificactions){
        $return['success'] = 1;
		$return['mensaje'] = $info;
		$isSent = sendNotifyFirebase($configNotificactions["token"], $info["title"], $info["topic"], $info["message"], 0, $info["type"]);
		if($isSent){
			$return['success'] = 1;
			$return['mensaje'] = "Notificación enviada";
			$return['mensaje2'] = $isSent;
			return $return;
		}
		else{
			$return['success'] = 0;
			$return['mensaje'] = "Error al intentar enviar la notificación";
		}
	}
	else{
		$return['success'] = 0;
		$return['mensaje'] = "Error al obtener la configuración de las notificaciones";
	}
	return $return;
}

?>
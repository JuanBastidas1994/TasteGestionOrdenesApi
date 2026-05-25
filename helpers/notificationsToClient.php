<?php

function sendMessageWhatsappVideo($orden){
    require_once "clases/cl_empresas.php";
    $Clempresas = new cl_empresas();
    
    if(!$Clempresas->getPermiso('NOTIFY_WHATSAPP')) return false;
    
    $phone = $orden['telefono'];
    if(strlen($phone) < 10) return false;
    
    require_once "clases/cl_ultramsg.php";
	$ClMessages = new cl_ultramsg();
	$ClMessages->setInstance('instance150737', 'br5wrz8e57z1t166'); //Instancia 400grados
	
// 	extract($orden);
	$texto = "La lucha es larga, la lucha es difícil, la lucha es necesaria. Pan, para los que saben de pan. 400 Grados";
	
	$url = "https://dashboard.mie-commerce.com/videos/entregada.mp4";
	
	return $ClMessages->sendVideo($phone, $url, $texto, 0);
}


?>
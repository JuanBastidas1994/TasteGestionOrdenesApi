<?php

	
function notifyAsignacionFlota($order_id, $flota_id, $texto_adicional){
	
	require_once "clases/cl_ordenes.php";
	$Clordenes = new cl_ordenes();
	
	$orden = $Clordenes->getOrderForNotify($order_id);
	if(!$orden) return;

	//Enviar mensajes por telegram al administrador
	sendMessageTelegram($orden, $flota_id, $texto_adicional);
}


function sendMessageTelegram($orden, $flota_id, $texto_adicional){
    
    require_once "clases/cl_empresas.php";
    $Clempresas = new cl_empresas();
    
    // global $Clempresas;
    if(!$Clempresas->getPermisoByBusiness('NOTIFY_TELEGRAM', $flota_id)) return false;
    
    require_once "clases/cl_telegram.php";
	$clTelegram = new cl_telegram();
	
	extract($orden);
	
	$chats = $clTelegram->getChatsAvailablesFlota($flota_id);
	if($chats){
	    $telegramMessage = buildTextTelegram($orden);
	    if($texto_adicional !== ""){
	        $telegramMessage .= "\n $texto_adicional";
	    }
    	foreach($chats as $chat){
    	   // $clTelegram->sendOrder($chat['chat_id'],$telegramMessage,'orderdetail_'.$cod_orden);
    	    $clTelegram->sendOrder($chat['chat_id'],$telegramMessage);
    	}
	}
    
}

function buildTextTelegram($orden){
	extract($orden);
	$tipo = ($is_envio == 1) ? "Delivery" : "Pickup";
	$emoji = ($is_envio == 1) ? '🛵' : '📦';
	$entrega = ($is_programado) ? dateTimeLatino($hora_retiro) : "Ahora";
	
	$texto = "<b>Nuevo pedido asignado por $empresa sucursal $sucursal (#$cod_orden)</b>\n";
	$texto .= "Cliente: <i>$nombre</i>\n";
	$texto .= "Total: <b>$$total</b>\n";
	$texto .= "$emoji $tipo, Entrega: $entrega\n";
	
	foreach($pagos as $pago){
        $id = $pago['id'];
        $nombre = $pago['nombre'];
        $monto = $pago['monto'];
        switch ($id) {
            case 'E':
                $emojiPayment = '💵';
                break;
            case 'T':
                $emojiPayment = '💳';
                break;
            case 'TB':
                $emojiPayment = '🏦';
                break;
            default:
                $emojiPayment = '❓';
                break;
        }
        $texto .= "$emojiPayment $nombre: $$monto\n";
    }
    
	return $texto;
}

?>
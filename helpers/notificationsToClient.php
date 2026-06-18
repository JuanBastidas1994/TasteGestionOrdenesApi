<?php

/* ==========================================================================
   NOTIFICACIONES PUSH AL CLIENTE (Expo)
   Para motorizados se sigue usando Firebase (notificarOrdenes en Ordenes.php),
   esto es exclusivo para notificar al cliente final que hizo la orden.
   ========================================================================== */

// Punto único de entrada: llamar esta función desde Ordenes.php
function notificarClientePush($orden, $estado){
	$cod_usuario = $orden['cod_usuario'];
	$is_envio    = $orden['is_envio'];
	$cod_orden   = isset($orden['id']) ? $orden['id'] : (isset($orden['cod_orden']) ? $orden['cod_orden'] : null);

	$tokens = getPushTokensCliente($cod_usuario);
	if(empty($tokens)) return false;

	$texto = getTextoClientePush($estado, $is_envio);

	return enviarExpoPush($tokens, $texto['titulo'], $texto['mensaje'], [
		"orden_id" => $cod_orden,
		"estado"   => $estado,
	]);
}

// Textos por estado y tipo de orden (delivery/pickup). Editar aquí los textos.
function getTextoClientePush($estado, $is_envio){
	$tipo = ($is_envio == 1) ? "delivery" : "pickup";

	$textos = [
		"ACEPTADA" => [
			"delivery" => [
				"titulo"  => "Tu orden ha sido aceptada",
				"mensaje" => "Ya estamos preparando tu pedido para enviártelo.",
			],
			"pickup" => [
				"titulo"  => "Tu orden ha sido aceptada",
				"mensaje" => "Ya estamos preparando tu pedido, te avisaremos cuando esté listo para recoger.",
			],
		],
		"PREPARANDO" => [
			"pickup" => [
				"titulo"  => "Tu pedido se está preparando",
				"mensaje" => "Ya estamos preparando tu pedido, no olvides venir a recogerlo.",
			],
		],
		"ASIGNADA" => [
			"delivery" => [
				"titulo"  => "Tu pedido fue asignado",
				"mensaje" => "Se le ha asignado un motorizado a tu pedido.",
			],
		],
		"ENVIANDO" => [
			"delivery" => [
				"titulo"  => "Tu pedido está en camino",
				"mensaje" => "La persona encargada salió con tu pedido, pronto te llamará para coordinar la entrega.",
			],
		],
		"ENTREGADA" => [
			"delivery" => [
				"titulo"  => "Tu pedido ha sido entregado con éxito",
				"mensaje" => "Disfruta tu pedido! Si tienes algún comentario, no dudes en calificarnos.",
			],
			"pickup" => [
				"titulo"  => "Tu pedido ha sido entregado con éxito",
				"mensaje" => "Gracias por recogerlo! Si tienes algún comentario, no dudes en calificarnos.",
			],
		],
		"NO_ENTREGADA" => [
			"delivery" => [
				"titulo"  => "No pudimos entregar tu pedido",
				"mensaje" => "Hubo un problema durante la entrega, nos pondremos en contacto contigo.",
			],
		],
	];

	if(isset($textos[$estado][$tipo])) return $textos[$estado][$tipo];
	if(isset($textos[$estado]['delivery'])) return $textos[$estado]['delivery'];

	return [
		"titulo"  => "Actualización de tu pedido",
		"mensaje" => "El estado de tu orden ha cambiado a " . strtolower($estado),
	];
}

// Tokens Expo del usuario (puede tener varios dispositivos)
function getPushTokensCliente($cod_usuario){
	$sql = "SELECT token FROM tb_push_tokens WHERE cod_usuario = :cod_usuario";
	$registros = Conexion::buscarVariosRegistro($sql, [':cod_usuario' => $cod_usuario]);
	if(!$registros) return [];
	return array_column($registros, 'token');
}

// Envío crudo a la API de Expo, en chunks de 100 (límite de Expo por request)
function enviarExpoPush($tokens, $titulo, $mensaje, $data = []){
	if(empty($tokens)) return false;

	$mensajes = [];
	foreach($tokens as $token){
		$mensajes[] = [
			"to"    => $token,
			"title" => $titulo,
			"body"  => $mensaje,
			"data"  => $data,
		];
	}

	$respuestas = [];
	foreach(array_chunk($mensajes, 100) as $chunk){
		$ch = curl_init("https://exp.host/--/api/v2/push/send");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, [
			"Content-Type: application/json",
			"Accept: application/json",
			"Accept-Encoding: gzip, deflate",
		]);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($chunk));
		$respuestas[] = curl_exec($ch);
		curl_close($ch);
	}

	return $respuestas;
}


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

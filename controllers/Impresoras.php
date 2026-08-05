<?php
/*	Variables Heredadas del Index
		$method - POST, GET, PUT, DELETE, etc.
		$request - Url y variables GET
		$input - Solo metodo POST, PUT */

if($method == "GET"){
	$num_variables = count($request);
	if($num_variables == 2){
		$cod_sucursal = $request[1];
		showResponse(listarPorSucursal($cod_sucursal));
	}else if($num_variables == 3){
		$cod_sucursal = $request[1];
		$estacion_id = $request[2];
		showResponse(listarPorEstacion($cod_sucursal, $estacion_id));
	}

	$return['success']= 0;
	$return['mensaje']= "Evento no existente para impresoras";
	showResponse($return);
}
else if($method == "POST"){
	$num_variables = count($request);
	if($num_variables == 1){
		showResponse(crearImpresora($input));
	}else if($num_variables == 2){
		$metodo = $request[1];
		if($metodo == "actualizar"){
			showResponse(actualizarImpresora($input));
		}else if($metodo == "eliminar"){
			showResponse(eliminarImpresora($input));
		}else if($metodo == "reportar"){
			showResponse(reportarImpresoras($input));
		}
	}

	$return['success']= 0;
	$return['mensaje']= "Evento no existente para impresoras";
	showResponse($return);
}
else{
	$return['success']= 0;
	$return['mensaje']= "El metodo ".$method." para impresoras aun no esta disponible.";
	showResponse($return);
}

/*FUNCIONES*/
function listarPorSucursal($cod_sucursal){
	$query = "SELECT * FROM tb_impresoras WHERE cod_sucursal = ? ORDER BY estacion_id, tipo";
	$impresoras = Conexion::buscarVariosRegistro($query, [$cod_sucursal]);

	$return['success'] = 1;
	$return['mensaje'] = "Correcto";
	$return['data'] = $impresoras;
	return $return;
}

function listarPorEstacion($cod_sucursal, $estacion_id){
	$query = "SELECT * FROM tb_impresoras WHERE cod_sucursal = ? AND estacion_id = ?";
	$impresoras = Conexion::buscarVariosRegistro($query, [$cod_sucursal, $estacion_id]);

	$return['success'] = 1;
	$return['mensaje'] = "Correcto";
	$return['data'] = $impresoras;
	return $return;
}

function crearImpresora($input){
	$errorMsg = "";
	$datosObligatorios = array("cod_sucursal", "estacion_id", "nombre", "tipo");
	if(!validate($datosObligatorios, $input, $errorMsg)){
		$return['success'] = 0;
		$return['mensaje'] = $errorMsg;
		return $return;
	}
	extract($input);

	$size = isset($size) ? $size : "80";
	$paginas = isset($paginas) ? $paginas : 1;

	$query = "INSERT INTO tb_impresoras (cod_empresa, cod_sucursal, estacion_id, nombre, tipo, size, paginas, fecha_creacion, fecha_visto)
			   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
	$ok = Conexion::ejecutar($query, [cod_empresa, $cod_sucursal, $estacion_id, $nombre, $tipo, $size, $paginas, fecha(), fecha()]);

	if($ok){
		$return['success'] = 1;
		$return['mensaje'] = "Impresora creada correctamente";
		$return['cod_impresora'] = Conexion::lastId();
	}else{
		$return['success'] = 0;
		$return['mensaje'] = "No se pudo crear la impresora";
	}
	return $return;
}

function actualizarImpresora($input){
	$errorMsg = "";
	$datosObligatorios = array("cod_impresora", "tipo");
	if(!validate($datosObligatorios, $input, $errorMsg)){
		$return['success'] = 0;
		$return['mensaje'] = $errorMsg;
		return $return;
	}
	extract($input);

	$size = isset($size) ? $size : "80";
	$paginas = isset($paginas) ? $paginas : 1;

	$query = "UPDATE tb_impresoras SET tipo = ?, size = ?, paginas = ? WHERE cod_impresora = ?";
	$ok = Conexion::ejecutar($query, [$tipo, $size, $paginas, $cod_impresora]);

	$return['success'] = $ok ? 1 : 0;
	$return['mensaje'] = $ok ? "Impresora actualizada correctamente" : "No se pudo actualizar la impresora";
	return $return;
}

function eliminarImpresora($input){
	$errorMsg = "";
	$datosObligatorios = array("cod_impresora");
	if(!validate($datosObligatorios, $input, $errorMsg)){
		$return['success'] = 0;
		$return['mensaje'] = $errorMsg;
		return $return;
	}
	extract($input);

	$query = "DELETE FROM tb_impresoras WHERE cod_impresora = ?";
	$ok = Conexion::ejecutar($query, [$cod_impresora]);

	$return['success'] = $ok ? 1 : 0;
	$return['mensaje'] = $ok ? "Impresora eliminada correctamente" : "No se pudo eliminar la impresora";
	return $return;
}

function reportarImpresoras($input){
	$errorMsg = "";
	$datosObligatorios = array("cod_sucursal", "estacion_id", "nombres");
	if(!validate($datosObligatorios, $input, $errorMsg)){
		$return['success'] = 0;
		$return['mensaje'] = $errorMsg;
		return $return;
	}
	extract($input);

	foreach($nombres as $nombre){
		$existentes = Conexion::buscarVariosRegistro(
			"SELECT cod_impresora FROM tb_impresoras WHERE cod_sucursal = ? AND estacion_id = ? AND nombre = ? LIMIT 1",
			[$cod_sucursal, $estacion_id, $nombre]
		);

		if(count($existentes) > 0){
			Conexion::ejecutar("UPDATE tb_impresoras SET fecha_visto = ? WHERE cod_impresora = ?", [fecha(), $existentes[0]['cod_impresora']]);
		}else{
			Conexion::ejecutar(
				"INSERT INTO tb_impresoras (cod_empresa, cod_sucursal, estacion_id, nombre, tipo, size, paginas, fecha_creacion, fecha_visto)
				 VALUES (?, ?, ?, ?, NULL, '80', 1, ?, ?)",
				[cod_empresa, $cod_sucursal, $estacion_id, $nombre, fecha(), fecha()]
			);
		}
	}

	$return['success'] = 1;
	$return['mensaje'] = "Impresoras reportadas correctamente";
	return $return;
}
?>

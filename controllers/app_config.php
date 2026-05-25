<?php
/*	Variables Heredadas del Index
		$method - POST, GET, PUT, DELETE, etc.
		$request - Url y variables GET
		$input - Solo metodo POST, PUT */
require_once "clases/cl_usuarios.php";
require_once "clases/cl_productos.php";
require_once "clases/cl_categorias.php";
$Clproductos = new cl_productos();
$Clcategorias = new cl_categorias();

	if($method == "GET"){
		$num_variables = count($request);
		if($num_variables == 2){
			$first = $request[1];
			if($first=="home"){
				$return = infoHome();
				showResponse($return);
			}
			if($first=="home2"){ //HOME CON CODIGO SUCURSAL
				$return = infoHome2();
				showResponse($return);
			}
			if($first=="categorias"){
				$return = infoCategorias();
				showResponse($return);
			}
			if($first=="banners"){
				$return = lstBanners();
				showResponse($return);
			}
			if($first=="menu-digital"){
			   $return = menuDigital(); 
			   showResponse($return);
			}
		}
		if($num_variables == 3){
			$first = $request[1];
			if($first=="home"){ //HOME CON CODIGO SUCURSAL
			    $cod_sucursal = $request[2];
				$return = infoHome($cod_sucursal);
				showResponse($return);
			}
			if($first=="home2"){ //HOME CON CODIGO SUCURSAL
			    $cod_sucursal = $request[2];
				$return = infoHome2($cod_sucursal);
				showResponse($return);
			}
			if($first=="pagina"){ //PAGINA INDICAR ALIAS
			    $aliasPagina = $request[2];
				$return = infoPagina($aliasPagina);
				showResponse($return);
			}
			if($first=="anuncios-web"){
				$return = anuncioWebDetalle($request[2]);
				showResponse($return);
			}
		}
		if($num_variables == 4){
			$first = $request[1];
			if($first=="home"){ //HOME CON CODIGO SUCURSAL
			    $cod_sucursal = $request[2];
			    $cod_usuario = $request[3];
				$return = infoHome($cod_sucursal, $cod_usuario);
				showResponse($return);
			}
			if($first=="home2"){ //HOME CON CODIGO SUCURSAL
			    $cod_sucursal = $request[2];
			    $cod_usuario = $request[3];
				$return = infoHome2($cod_sucursal, $cod_usuario);
				showResponse($return);
			}
		}
		
		$return['success']= 0;
		$return['mensaje']= "Url no valida para configuracion, por favor revisar los parametros";
		showResponse($return);
	}
	else{
		$return['success']= 0;
		$return['mensaje']= "El metodo ".$method." para configuracion aun no esta disponible.";
		showResponse($return);
	}
	
	
/*FUNCIONES*/
function infoHome($cod_sucursal = 0, $cod_usuario=0){
    global $Clproductos;
    
    if($cod_sucursal == 0){
        $cod_sucursal = sucursaldefault;
    }
	
	$data = [];
	$x=0;
	if($cod_usuario > 0){
		$Clusuarios = new cl_usuarios();
		//VALIDAR SI TIENE CEDULA EN FIDELIZACION
		if(fidelizacion){
			$usuario = $Clusuarios->get($cod_usuario);
			if($usuario){
				if($usuario['num_documento'] == ""){
					$motivo['descripcion'] = "Para poder acumular puntos necesitas registrar tu numero de documento puedes ir a tu cuenta para realizar esta acción";

					$data[$x]['titulo'] = "No tienes cédula registrada";
					$data[$x]['forma'] = "BLOQUE_RIGHT";
					$data[$x]['tipo'] = "FALTA_INFORMACION";
					$data[$x]['num_columnas'] = 1;
					$data[$x]['items'] = $motivo;
					$x++;
				}
			}
		}

    	//PEDIDO PROGRAMADO
        $pedidos = $Clusuarios->getPedidosProgramados($cod_usuario);
        if($pedidos){
            list($dia,$hora) = explode(" ",$pedidos['fecha_retiro']);

            if($pedidos['is_envio'] == 1)
                $pedidos['descripcion'] = "Te enviaremos tu pedido el día ".fechaLatinoShortWeekday($dia)." a las ".$hora." desde ".$pedidos['sucursal'];
            else
    	        $pedidos['descripcion'] = "Retira tu pedido en ".$pedidos['sucursal']." el día ".fechaLatinoShortWeekday($dia)." a las ".$hora;

            $data[$x]['titulo'] = "Tienes un pedido programado";
            $data[$x]['forma'] = "BLOQUE_RIGHT";
            $data[$x]['tipo'] = "PEDIDO_PROGRAMADO";
            $data[$x]['num_columnas'] = 1;
            $data[$x]['items'] = $pedidos;
            $x++;
        }		
	}    
	
	//OTRAS OPCIONES
	$tipoEsquema = (isset($_GET['tipo'])) ? $_GET['tipo'] : 'APP';

	$query = "SELECT titulo, forma, tipo, num_columnas, detalle, cod_detalle
	        FROM tb_web_esquema WHERE cod_empresa = ".cod_empresa." AND plataforma = '$tipoEsquema' ORDER BY posicion ASC";
	$resp = Conexion::buscarVariosRegistro($query);
	foreach ($resp as $secciones) {
	    $data[$x] = $secciones;
	    
		$tipo = $secciones['tipo'];
		$cod = $secciones['cod_detalle'];
		
		if($tipo == "ordenar"){
		    $data[$x]['items'] = $Clproductos->listaModuloWeb($cod, $cod_sucursal);
		    $data[$x]['width'] = 300;
		    $data[$x]['height'] = 300;
		}
		else if($tipo == "anuncios"){
		    $query = "SELECT width, height FROM tb_anuncio_cabecera WHERE cod_anuncio_cabecera = $cod";
		    $ac = Conexion::buscarRegistro($query);
		    if($ac){
		        $data[$x]['width'] = $ac['width'];
		        $data[$x]['height'] = $ac['height'];
				$data[$x]['id'] = $cod;
		    }
		    
		    
		    $query = "SELECT titulo, subtitulo, imagen as image_min, text_boton, accion_id, url_boton as accion_desc FROM tb_anuncio_detalle WHERE cod_anuncio_cabecera = $cod ORDER BY posicion";
		    $resp = Conexion::buscarVariosRegistro($query);
		    $j=0;
		    foreach ($resp as $anuncio) {
            	$resp[$j]['image_min'] = url.$anuncio['image_min'];
            	$j++;
            }
		    
			$data[$x]['items'] = $resp;
		}
		$x++;
	}


	/*MODALES*/
	$modales = [];

	$query = "SELECT * from tb_empresa_modal_cumple WHERE estado = 'A' AND cod_empresa = ".cod_empresa;
	$cumpleModal = Conexion::buscarRegistro($query);
	if($cumpleModal){
		$query = "SELECT * FROM tb_usuarios WHERE cod_usuario = $cod_usuario AND fecha_nacimiento LIKE '%".fechaFormat("-m-d")."%'";
		$cumple = Conexion::buscarRegistro($query);
		if($cumple){
			$modal['imagen'] = url.$cumpleModal['imagen'].'?v='.datetime_format($cumpleModal['fecha_actualizacion']);
			$modal['accion_id'] = 'INFO';
			$modal['accion_desc'] = '';
			$modales[0] = $modal;
		}
	}

	$fecha = fecha();
	$query = "SELECT m.imagen, m.accion_id, m.accion_desc
				FROM tb_modal_eventos m
				WHERE m.fecha_inicio <= '$fecha'
				AND m.fecha_fin >= '$fecha'
				AND m.cod_empresa = ".cod_empresa." LIMIT 0,3";
	$pmodal = Conexion::buscarVariosRegistro($query);
	foreach ($pmodal as $modal) {
		$modal['imagen'] = url.$modal['imagen'];
		$modales[] = $modal;
	}

	$return['success'] = 1;
	$return['mensaje'] = "App Home";
	$return['data'] = $data;
	$return['modales'] = $modales;
    return $return;
}

function infoHome2($cod_sucursal = 0, $cod_usuario=0){
    global $Clproductos;
	global $Clcategorias;
	$cod_empresa = cod_empresa;
    
    if($cod_sucursal == 0){
        $cod_sucursal = sucursaldefault;
    }
	
	$data = [];
	$x=0;
	if($cod_usuario > 0){
		$Clusuarios = new cl_usuarios();
		//VALIDAR SI TIENE CEDULA EN FIDELIZACION
		if(fidelizacion){
			$usuario = $Clusuarios->get($cod_usuario);
			if($usuario){
				if($usuario['num_documento'] == ""){
					$motivo['descripcion'] = "Para poder acumular puntos necesitas registrar tu numero de documento puedes ir a tu cuenta para realizar esta acción";

					$data[$x]['titulo'] = "No tienes cédula registrada";
					$data[$x]['forma'] = "BLOQUE_RIGHT";
					$data[$x]['tipo'] = "FALTA_INFORMACION";
					$data[$x]['num_columnas'] = 1;
					$data[$x]['items'] = $motivo;
					$x++;
				}
			}
		}

    	//PEDIDO PROGRAMADO
        $pedidos = $Clusuarios->getPedidosProgramados($cod_usuario);
        if($pedidos){
            list($dia,$hora) = explode(" ",$pedidos['fecha_retiro']);

            if($pedidos['is_envio'] == 1)
                $pedidos['descripcion'] = "Te enviaremos tu pedido el día ".fechaLatinoShortWeekday($dia)." a las ".$hora." desde ".$pedidos['sucursal'];
            else
    	        $pedidos['descripcion'] = "Retira tu pedido en ".$pedidos['sucursal']." el día ".fechaLatinoShortWeekday($dia)." a las ".$hora;

            $data[$x]['titulo'] = "Tienes un pedido programado";
            $data[$x]['forma'] = "BLOQUE_RIGHT";
            $data[$x]['tipo'] = "PEDIDO_PROGRAMADO";
            $data[$x]['num_columnas'] = 1;
            $data[$x]['items'] = $pedidos;
            $x++;
        }		
	}    
	
	//OTRAS OPCIONES
	$query = "SELECT fd.cod_front_pagina_detalle as id, fd.cod_tipo as tipo, fd.titulo, fd.forma, fd.num_columnas, fd.detalle, fd.detalle2, fd.cod_detalle, fd.html, fd.classname, fd.showTitle  
			FROM tb_front_paginas f, tb_front_pagina_detalle fd
			WHERE f.cod_front_pagina = fd.cod_front_pagina
			AND f.cod_empresa = $cod_empresa
			AND f.home = 1
			ORDER BY fd.posicion ASC";
	$resp = Conexion::buscarVariosRegistro($query);
	foreach ($resp as $secciones) {
	    $data[$x] = $secciones;
	    
		$tipo = $secciones['tipo'];
		$cod = $secciones['cod_detalle'];
		

		if($tipo == "ordenar"){
		    $data[$x]['items'] = $Clproductos->listaModuloWeb($cod, $cod_sucursal);
		    $data[$x]['width'] = 300;
		    $data[$x]['height'] = 300;
		}
		if($tipo == "anuncios"){
		    $query = "SELECT width, height FROM tb_anuncio_cabecera WHERE cod_anuncio_cabecera = $cod";
		    $ac = Conexion::buscarRegistro($query);
		    if($ac){
		        $data[$x]['width'] = $ac['width'];
		        $data[$x]['height'] = $ac['height'];
				//$data[$x]['id'] = $cod;
		    }
		    
		    $query = "SELECT titulo, subtitulo, imagen as image_min, text_boton, accion_id, url_boton as accion_desc FROM tb_anuncio_detalle WHERE cod_anuncio_cabecera = $cod ORDER BY posicion";
		    $resp = Conexion::buscarVariosRegistro($query);
		    $j=0;
		    foreach ($resp as $anuncio) {
            	$resp[$j]['image_min'] = url.$anuncio['image_min'];
            	$j++;
            }
		    
			$data[$x]['items'] = $resp;
		}
		if($tipo == "categorias"){
		    $data[$x]['items'] = $Clcategorias->lista();
		}
		if($tipo == "blog"){
			require_once "clases/cl_noticias.php";
			$Clnoticias = new cl_noticias();
		    $data[$x]['items'] = $Clnoticias->listaByCategoria($cod);
		}
		if($tipo == "html"){
			$data[$x]['html'] = editor_decode($secciones['html']);
		}
		$x++;
	}	


	/*MODALES*/
	$modales = [];

	$query = "SELECT * from tb_empresa_modal_cumple WHERE estado = 'A' AND cod_empresa = ".cod_empresa;
	$cumpleModal = Conexion::buscarRegistro($query);
	if($cumpleModal){
		$query = "SELECT * FROM tb_usuarios WHERE cod_usuario = $cod_usuario AND fecha_nacimiento LIKE '%".fechaFormat("-m-d")."%'";
		$cumple = Conexion::buscarRegistro($query);
		if($cumple){
			$modal['imagen'] = url.$cumpleModal['imagen'].'?v='.datetime_format($cumpleModal['fecha_actualizacion']);
			$modal['accion_id'] = 'INFO';
			$modal['accion_desc'] = '';
			$modales[0] = $modal;
		}
	}

	$fecha = fecha();
	$query = "SELECT m.imagen, m.accion_id, m.accion_desc
				FROM tb_modal_eventos m
				WHERE m.fecha_inicio <= '$fecha'
				AND m.fecha_fin >= '$fecha'
				AND m.cod_empresa = ".cod_empresa." LIMIT 0,3";
	$pmodal = Conexion::buscarVariosRegistro($query);
	foreach ($pmodal as $modal) {
		$modal['imagen'] = url.$modal['imagen'];
		$modales[] = $modal;
	}

	$return['success'] = 1;
	$return['mensaje'] = "App Home";
	$return['data'] = $data;
	$return['modales'] = $modales;
    return $return;
}

function infoPagina($alias="", $cod_sucursal=0){
	global $Clproductos;
	global $Clcategorias;
	$cod_empresa = cod_empresa;

	if($cod_sucursal == 0){
        $cod_sucursal = sucursaldefault;
    }

	$data = [];
	$x=0;
	$query = "SELECT fd.cod_front_pagina_detalle as id, fd.cod_tipo as tipo, fd.titulo, fd.forma, fd.num_columnas, fd.detalle, fd.detalle2, fd.cod_detalle, fd.html, fd.classname, fd.showTitle  
			FROM tb_front_paginas f, tb_front_pagina_detalle fd
			WHERE f.cod_front_pagina = fd.cod_front_pagina
			AND f.cod_empresa = $cod_empresa
			AND f.alias = '$alias'
			ORDER BY fd.posicion ASC";
	$resp = Conexion::buscarVariosRegistro($query);
	foreach ($resp as $secciones) {
	    $data[$x] = $secciones;
	    
		$tipo = $secciones['tipo'];
		$cod = $secciones['cod_detalle'];
		

		if($tipo == "ordenar"){
		    $data[$x]['items'] = $Clproductos->listaModuloWeb($cod, $cod_sucursal);
		    $data[$x]['width'] = 300;
		    $data[$x]['height'] = 300;
		}
		if($tipo == "anuncios"){
		    $query = "SELECT width, height FROM tb_anuncio_cabecera WHERE cod_anuncio_cabecera = $cod";
		    $ac = Conexion::buscarRegistro($query);
		    if($ac){
		        $data[$x]['width'] = $ac['width'];
		        $data[$x]['height'] = $ac['height'];
				//$data[$x]['id'] = $cod;
		    }
		    
		    $query = "SELECT titulo, subtitulo, imagen as image_min, text_boton, accion_id, url_boton as accion_desc FROM tb_anuncio_detalle WHERE cod_anuncio_cabecera = $cod ORDER BY posicion";
		    $resp = Conexion::buscarVariosRegistro($query);
		    $j=0;
		    foreach ($resp as $anuncio) {
            	$resp[$j]['image_min'] = url.$anuncio['image_min'];
            	$j++;
            }
		    
			$data[$x]['items'] = $resp;
		}
		if($tipo == "categorias"){
		    $data[$x]['items'] = $Clcategorias->lista();
		}
		if($tipo == "blog"){
			require_once "clases/cl_noticias.php";
			$Clnoticias = new cl_noticias();
		    $data[$x]['items'] = $Clnoticias->listaByCategoria($cod);
		}
		if($tipo == "html"){
			$data[$x]['html'] = editor_decode($secciones['html']);
		}
		$x++;
	}	


	$return['success'] = 1;
	$return['mensaje'] = "Página";
	$return['data'] = $data;
    return $return;
}

function infoCategorias(){
    require_once "clases/cl_categorias.php";
    $Clcategorias = new cl_categorias();
    
    $respCategorias = $Clcategorias->lista();
    
    $return['success'] = 1;
	$return['mensaje'] = "Correcto";
	$return['data'] = $respCategorias;
	return $return;
}

function lstBanners(){
	$query = "SELECT titulo, subtitulo, descuento as text_adicional, text_boton, url_boton, image_min as imagen, posicion
			FROM tb_banner 
			WHERE estado IN('A') AND cod_empresa = ".cod_empresa." ORDER BY posicion ASC LIMIT 0,5";
	$resp = Conexion::buscarVariosRegistro($query);
	foreach ($resp as $key=>$banner) {
		$resp[$key]['imagen'] = url.$banner['imagen'];
	}

	$return['success'] = 1;
	$return['mensaje'] = "Correcto";
	$return['data'] = $resp;
	return $return; 
}


function menuDigital(){
	$cod_empresa = cod_empresa;
	$query = "SELECT mi.imagen
                FROM tb_menu_digital m, tb_menu_digital_imagenes mi
                WHERE m.cod_menu_digital = mi.cod_menu_digital
                AND m.cod_empresa = $cod_empresa
                AND mi.estado = 'A'
                ORDER BY mi.posicion";
	$resp = Conexion::buscarVariosRegistro($query);
	foreach ($resp as $key => $anuncio) {
		$resp[$key]['imagen'] = url.$anuncio['imagen'];
		$info = getimagesize(urlUpload.$anuncio['imagen']);
		if($info){
		    $resp[$key]['ancho'] = $info[0];
		    $resp[$key]['alto'] = $info[1];
		}
		
	}
	$return['success'] = 1;
	$return['mensaje'] = "Lista imágenes menu digital";
	$return['data'] = $resp;
	return $return;
}

function anuncioWebDetalle($id){
	$categoriasGenerales = [];
	$cod_empresa = cod_empresa;
	
	$limit = (isset($_GET['limit'])) ? $_GET['limit'] : 999;
	$query = "SELECT titulo, subtitulo, imagen as image_min, text_boton, accion_id, url_boton as accion_desc, categorias, descripcion 
				FROM tb_anuncio_detalle WHERE cod_anuncio_cabecera = $id AND cod_empresa = $cod_empresa AND estado = 'A' ORDER BY posicion LIMIT 0,$limit";
	$resp = Conexion::buscarVariosRegistro($query);
	foreach ($resp as $key => $anuncio) {
		$categorias = $anuncio['categorias'];
		unset($resp[$key]['categorias']);

		$resp[$key]['image_min'] = url.$anuncio['image_min'];
		if($categorias !== ""){
			$resp[$key]['categorias'] = explode(",",$categorias);
			fillArrayItemsNoRepeat($categoriasGenerales, $resp[$key]['categorias']);
		}
		else
			$resp[$key]['categorias'] = [];
	}
	$return['success'] = 1;
	$return['mensaje'] = "Lista Anuncio web";
	$return['categorias'] = $categoriasGenerales;
	$return['data'] = $resp;
	return $return;
}

function fillArrayItemsNoRepeat(&$fill, $newArray){
	foreach($newArray as $key => $item){
		if(!in_array($item, $fill)){
			$fill[] = $item;
		}
	}
}


?>
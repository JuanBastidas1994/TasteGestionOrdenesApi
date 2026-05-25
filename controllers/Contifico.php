<?php
require_once "clases/cl_usuarios.php";
require_once "clases/cl_contifico.php";
require_once "clases/cl_ordenes.php";
require_once "clases/cl_productos.php";
$ClUsuarios = new cl_usuarios();
$ClContifico = new cl_contifico();
$ClOrdenes = new cl_ordenes();
$ClProductos = new cl_productos();

if($method == "GET"){
	$num_variables = count($request);
	if($num_variables == 1){
	}else if($num_variables == 2){
		$cedula = $request[1];
		showResponse(getOrdenesByDocumento($cedula));
	}else if($num_variables == 3){
		
	}

	$return['success']= 0;
	$return['mensaje']= "Evento no existente para Contífico";
	showResponse($return);
}
else if($method == "POST"){
	$num_variables = count($request);
	if($num_variables == 1){
	}else if($num_variables == 2){
	}else if($num_variables == 3){
        if($request[1] == "inventario") {
            showResponse(setInventario($request[2])); //ING=ingreso EGR=egreso
        }
	}
	
	$return['success']= 0;
	$return['mensaje']= "Evento no existente para órdenes";
	showResponse($return);
}
else{
	$return['success']= 0;
	$return['mensaje']= "El metodo ".$method." para Contífico aun no esta disponible.";
	showResponse($return);
}

function getOrdenesByDocumento($code){
    global $ClUsuarios;
    global $ClContifico;
    global $ClOrdenes;
    
    $code = $ClUsuarios->getPurchaseCode($code);
    if(!$code){
        $return['success'] = 0;
        $return['mensaje'] = "Codigo no existente o caducado";
        $return['errorCode'] = "CLIENTE_INEXISTENTE";
        return $return;
    }
    $cedula = $code['num_documento'];

    $usuario = $ClUsuarios->getbyNumDocumento($cedula);
    if($usuario){
        $documentos = $ClContifico->getDocumentosByCedula($cedula);
        if($documentos === false){
            $return['success'] = 0;
		    $return['mensaje'] = "Error contifico ".$ClContifico->msgError;
            $return['documentos'] = $documentos;
        }else{
            if(count($documentos) === 0){
                $return['success'] = 0;
		        $return['mensaje'] = "No hay documentos el día de hoy";
                return $return;
            }

            $numDocsPoints = 0;
            foreach($documentos as $key => $documento){
                /*INICIO VERIFICAR ID REPETIDO*/
                $existe = $ClOrdenes->getRunfood($documento['id']);
                if($existe){
                    $documentos[$key]['dio_puntos'] = false;
                    $documentos[$key]['error'] = "Id ya existe";
                }else{
                    if($ClContifico->saveOrden($documento, $usuario['cod_usuario'])){
                        $numDocsPoints = $numDocsPoints + 1;
                        $documentos[$key]['dio_puntos'] = true;
                    }else{
                        $documentos[$key]['dio_puntos'] = false;
                        $documentos[$key]['error'] = "No pudo guardar la orden";
                    }
                }
                /*FIN VERIFICAR ID REPETIDO*/
            }

            $mensaje = "No ha documentos pendientes por calcular";
            if($numDocsPoints > 0){
                $mensaje = "Calculando puntos de $numDocsPoints ordenes";
                $ClUsuarios->unsubscribeCode($code['codigo'],0);
            }

            $return['success'] = 1;
		    $return['mensaje'] = $mensaje;
            $return['num_documentos'] = count($documentos);
            $return['documentos_calculados'] = $numDocsPoints;
		    $return['documentos_contifico'] = $documentos;
        }
    }else{
        $return['success'] = 0;
		$return['mensaje'] = "Cliente con cédula $cedula no encontrado, debe descargarse la app";
    }
    return $return;
}

function setInventario($tipo) {
    global $ClOrdenes;
    global $ClProductos;
    global $ClContifico;
    global $input;
    extract($input);

    $orden = $ClOrdenes->getOrden($cod_orden);
    if(!$orden) {
        $return['success'] = 0;
        $return['mensaje'] = "Orden no existe";
        return $return;
    }

    $contificoSucursal = $ClContifico->getInfoBySucursal($orden["cod_sucursal"]);
    if(!$contificoSucursal){
        $return["success"] = 0;
        $return["mensaje"] = "La sucursal no tiene configurado un pto de emisión";
        return false;
    } 
    $ClContifico->API = $contificoSucursal["api"];

    if((int)$contificoSucursal["inventario"] == 0) {
        $return['success'] = 0;
        $return['mensaje'] = "No hay permiso para descontar inventario";
        return $return;
    }

    $detalleOrden = $ClOrdenes->getOrdenDetalle($cod_orden);
    if(!$detalleOrden) {
        $return['success'] = 0;
        $return['mensaje'] = "Orden detalle no existe";
        return $return;
    }

    $detalles = []; //Se envía a contifico

    foreach ($detalleOrden as $detOrden) {
        /* 
        //OBTENER INGREDIENTES DEL PRODUCTO
        $productoIngrendientes = $ClProductos->getProductoIngredientes($detOrden["product_id"]);
        $productoOpciones = $ClProductos->getProductoOpciones($detOrden["product_id"]);

        if($productoIngrendientes) {
            foreach ($productoIngrendientes as $prodIngredientes) {
                $pIng = [];
                $pIng["producto_id"] = $prodIngredientes["id_contifico"];
                $pIng["cantidad"] = number_format($prodIngredientes["valor"] * $detOrden["cantidad"], 2);
                $pIng["precio"] = $prodIngredientes["precio"];
                
                $detalles[] = $pIng;
            }
        } */

        //OBTENER INGREDIENTES DE LAS OPCIONES DEL PRODUCTO
        $opciones = $detOrden["opciones"];
        if($opciones) {
            foreach ($opciones as $opcion) {
                foreach ($opcion["detalles"] as $detalle) {
                    //Buscar el producto contifico cuando la opcion detalle este ligado a un producto taste
                    $productoFromOpcionDetalle = $ClProductos->getProductFromOpcionDetalleIsDatabase($detalle["id"],$contificoSucursal["cod_contifico_empresa"]);
                    if($productoFromOpcionDetalle){
                        $pIng = [];
                        $pIng["producto_id"] = $productoFromOpcionDetalle["id"];
                        $pIng["cantidad"] = number_format($detalle["cantidad"] * $detOrden["cantidad"], 2);
                        $pIng["precio"] = $productoFromOpcionDetalle["precio"];
                        
                        $detalles[] = $pIng;
                    }

                    //Buscar ingredientes en los productos
                    $productoOpcionesIngrendientes = $ClProductos->getProductoOpcionesIngredientes($detalle["id"], $contificoSucursal["cod_contifico_empresa"]);
                    if($productoOpcionesIngrendientes) {
                        foreach ($productoOpcionesIngrendientes as $prodOpcIngredientes) {
                            $pIng = [];
                            $pIng["producto_id"] = $prodOpcIngredientes["id"];
                            $pIng["cantidad"] = number_format(($prodOpcIngredientes["valor"] * $detalle["cantidad"]) * $detOrden["cantidad"], 2);
                            $pIng["precio"] = $prodOpcIngredientes["precio"];
                            
                            $detalles[] = $pIng;
                        }
                    }
                }
            }
        }
        
        //OBTENER RECIPIENTES DE LA ORDEN
        $recipientes = $ClOrdenes->getRecipientesByRuc($cod_orden, cod_empresa, $contificoSucursal["cod_contifico_empresa"]);
        foreach($recipientes as $recipiente){
            $pIng = [];
            $pIng["producto_id"] = $recipiente["id"];
            $pIng["cantidad"] = number_format($recipiente["cantidad"], 2);
            $pIng["precio"] = $recipiente["precio"];
            
            $detalles[] = $pIng;
        }
        
    }

    $msj = "descontó";
    if($tipo == "ING")
        $msj = "ingresó";
    
    if(count($detalles) > 0) {

        $inventario["tipo"] = $tipo;
        $inventario["fecha"] = date_format(date_create(fecha_only()), 'd/m/Y');  ;
        $inventario["bodega_id"] = $contificoSucursal["id_bodega"];
        $inventario["detalles"] = $detalles;
        $inventario["descripcion"] = "Compra mediante la WEB";

       /*  $return['success'] = 1;
        $return['mensaje'] = "Se $msj inventario";
        $return['data'] = $inventario;
        return $return; */

        $respInventario = $ClContifico->setInventario($inventario);
        if($respInventario) {
            if(isset($respInventario["codigo"])) {
                $ClOrdenes->saveOrdenInventario($cod_orden, $ClContifico->cod_contifico_empresa, $tipo, $respInventario["codigo"], $respInventario["id"]);
                $return['success'] = 1;
                $return['mensaje'] = "Se $msj inventario";
                $return['respcontifico'] = $respInventario;
                return $return;
            }
            else {
                $return['success'] = 0;
                $return['mensaje'] = "No se $msj inventario, " . $respInventario["mensaje"];
                $return['data'] = $respInventario;
                return $return;
            }
        }
        $return['success'] = 0;
        $return['mensaje'] = "Error, no se $msj inventario";
        return $return;
    }
    $return['success'] = 0;
    $return['mensaje'] = "No se $msj inventario";
    return $return;
}
?>
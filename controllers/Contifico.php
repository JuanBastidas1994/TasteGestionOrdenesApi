<?php
require_once "clases/cl_usuarios.php";
require_once "clases/cl_contifico.php";
require_once "clases/cl_ordenes.php";
$ClUsuarios = new cl_usuarios();
$ClContifico = new cl_contifico();
$ClOrdenes = new cl_ordenes();

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

/**
 * Endpoint legacy, reemplazado por ContificoProvider::adjustInventory() (llamado automáticamente
 * desde controllers/Facturacion.php). Se deja como tripwire: si algo todavía le pega, debe
 * notarse en vez de mover inventario en paralelo al flujo nuevo (riesgo de doble descuento).
 */
function setInventario($tipo) {
    $return['success'] = 0;
    $return['mensaje'] = "Función de facturación obsoleta, por favor actualizar!";
    return $return;
}
?>
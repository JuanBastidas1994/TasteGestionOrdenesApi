<?php
/*	Variables Heredadas del Index
		$method - POST, GET, PUT, DELETE, etc.
		$request - Url y variables GET
		$input - Solo metodo POST, PUT */
require_once "clases/cl_ordenes.php";
require_once "clases/cl_sucursales.php";
$Clordenes = new cl_ordenes();
$ClSucursales = new cl_sucursales();

if($method == "POST"){
	$num_variables = count($request);
	if($num_variables == 1){
		showResponse(printOrder($input));
	}
}
else{
	$return['success']= 0;
	$return['mensaje']= "El metodo ".$method." para configuracion aun no esta disponible.";
	showResponse($return);
}
	
	
/*FUNCIONES*/
function printOrder($input){
	global $Clordenes;

	$errorMsg = "";
	$datosObligatorios = array("cod_orden","impresoras");
    if(!validate($datosObligatorios, $input, $errorMsg)){
        $return['success'] = 0;
        $return['mensaje'] = $errorMsg;
        showResponse($return);
    }
	extract($input);

	if(count($impresoras) == 0){
        $return['success'] = 0;
        $return['mensaje'] = "No tienes una impresora configurada";
        return $return;
    }

	$orden = $Clordenes->getOrdenPrint($cod_orden);
	if($orden){
		foreach($impresoras as $key => $item){
            if($item['tipo']=="CAJA")
                $impresoras[$key]['detalle'] = getPrintCaja($orden, $item['size']);
            else
                $impresoras[$key]['detalle'] = getPrintCocina($orden, $item['size']);    
        }

		$return['success'] = 1;
		$return['mensaje'] = "Desglose impresión";
		$return['impresoras'] = $impresoras;
        $return['orden'] = $orden;
	}else{
		$return['success'] = 0;
		$return['mensaje'] = "Orden $cod_orden no encontrada";
	}
	return $return;
}

function getPrintCaja($orden, $size){
    $maxLenght = 42;
    if($size==58)
        $maxLenght = 32;

    extract($orden);
    $print = [];
    addLine($fecha, "CENTER", $print);
    addLine(name_site, "CENTER", $print);
    addLine("Pedido #".$cod_orden, "CENTER", $print);
    addLine($entrega, "CENTER", $print);

    if($orden['is_envio'] == 0){
        addLine("Retiro ".$orden['hora_retiro_latino'], "CENTER", $print);
    }
    
    addLine("Cliente: ".$nombre." ".$apellido, "LEFT", $print);
    // addLine("Correo: ".$correo, "LEFT", $print);
    addLine("Telf.: ".$telefono, "LEFT", $print);
    addLine("Observación: ".replaceUnicode($observacion), "MULTILINEA", $print);
    
    // Datos de facturacion
    if($orden['datos_facturacion']){
        addLine("DATOS FACTURACION", "CENTER", $print);
        $fact = $orden['datos_facturacion'];
        addLine("Razon Social: ".$fact['nombre'], "LEFT", $print);
        addLine("N. Doc.: ".$fact['num_documento'], "LEFT", $print);
        addLine("Correo: ".$fact['correo'], "LEFT", $print);
        addLine("Telf.: ".$fact['telefono'], "LEFT", $print);
        addLine("Direccion: ".replaceUnicode($fact['direccion']), "MULTILINEA", $print);
    }


    addLine("PRODUCTOS", "CENTER", $print);
    addLine("------------------------", "CENTER", $print);

    foreach($orden['detalle'] as $item){
        $productText = $item['cantidad']." - ".$item['nombre'];
        // if(cod_empresa == 70 || cod_empresa == 152){
        //     $productText .= ' ('.$item['categoria'].')';
        // }
        
        $lines = getMultiline($productText, $maxLenght);
        foreach($lines as $line){
                addLine($line, "LEFT", $print);
        }

		foreach($item['opciones'] as $opcion){
			addLine("   * ".$opcion['text'], "LEFT", $print);
			foreach($opcion['detalles'] as $detalle){
				addLine("     - ".$detalle['cantidad']." x ".replaceUnicode($detalle['text']), "LEFT", $print);
			}
		}

        if($item['comentarios'] !== ""){
            addLine("   * Comentario: ".replaceUnicode($item['comentarios']), "MULTILINEA", $print);
            addLine("------------------------", "CENTER", $print);
        }    
    }

    addTextRight("SUBTOTAL:", "$".number_format($subtotal,2), $maxLenght, $print);
    addTextRight("DESCUENTO:", "$".number_format($descuento,2), $maxLenght, $print);
    addTextRight("ENVIO:", "$".number_format($envio,2), $maxLenght, $print);
    addTextRight("IVA:", "$".number_format($iva,2), $maxLenght, $print);
    addTextRight("TOTAL:", "$".number_format($total,2), $maxLenght, $print);

    addLine("PAGOS", "CENTER", $print);
    addLine("------------------------", "CENTER", $print);
    foreach($orden['pagos'] as $item){
        addLine(html_entity_decode($item['descripcion']).": $".$item['monto'], "LEFT", $print);
        if($item['forma_pago'] == "T"){
            if($item['lote'] !== ""){
                addLine("Lote: ".$item['lote'], "LEFT", $print);
            }
        }
    }

    addLine("", "LEFT", $print);
    addLine("", "LEFT", $print);
    addLine("", "LEFT", $print);
    addLine("Gracias por su compra", "CENTER", $print);
    return $print;
}

function getPrintCocina($orden, $size){
    $maxLenght = 42;
    if($size==58)
        $maxLenght = 32;

    extract($orden);
    $print = [];
    addLine($fecha, "CENTER", $print);
    addLine(name_site, "CENTER", $print);
    addLine("Pedido #".$cod_orden, "CENTER", $print);
    addLine($entrega, "CENTER", $print);
    if($orden['is_envio'] == 0){
        addLine("Retiro ".$orden['hora_retiro_latino'], "CENTER", $print);
    }

    addLine("Cliente: ".$nombre." ".$apellido, "LEFT", $print);
    addLine("Observación: ".replaceUnicode($observacion), "MULTILINEA", $print);

    addLine("PRODUCTOS", "CENTER", $print);
    addLine("------------------------", "CENTER", $print);
    
    $businessPrintCategory = [70, 16, 152];

    foreach($orden['detalle'] as $item){
        $cantidad = $item['cantidad'];
        $nombre = $item['nombre'];
        $categoria = $item['categoria'];
        
        $productText = "$cantidad - $nombre";
        if(in_array(cod_empresa, $businessPrintCategory))
            $productText = "$cantidad - ($categoria) $nombre";
        
        $lines = getMultiline($productText, $maxLenght);
        foreach($lines as $line){
                addLine($line, "LEFT", $print);
        }

		foreach($item['opciones'] as $opcion){
			addLine("   * ".$opcion['text'], "LEFT", $print);
			foreach($opcion['detalles'] as $detalle){
				addLine("     - ".$detalle['cantidad']." x ".replaceUnicode($detalle['text']), "LEFT", $print);
			}
		}

        if($item['comentarios'] !== ""){
            addLine("   * Comentario: ".replaceUnicode($item['comentarios']), "MULTILINEA", $print);
            addLine("------------------------", "CENTER", $print);
        }  
        addLine("------------------------", "CENTER", $print);
    }
    addLine("Gracias por su compra", "CENTER", $print);
    return $print;
}

function addLine($texto, $tipo, &$print){
    $aux['texto'] = $texto;
    $aux['tipo'] = $tipo;
    $print[] = $aux;
}

function addTextRight($texto, $value, $max, &$print){
    $value = str_pad($value, 10, " ", STR_PAD_LEFT);
    $texto = $texto . $value;
    $texto = str_pad($texto, $max, " ", STR_PAD_LEFT);
    $aux['texto'] = $texto;
    $aux['tipo'] = "LEFT";
    $print[] = $aux;
}

function getMultiline($texto, $max){
    $lines = [];
    
    $x = 0;
    $ini = 0;
    $text = $texto;
    
    
    while(strlen($text) > $max){
        $end = mb_strpos($text, ' ', $max - 10);
        $lines[] = mb_substr($text, $ini, $end);
        
        $text = mb_substr($text, $end, strlen($text));
    }
    if(strlen($text) > 0){
        $lines[] = $text;
    }
    
    return $lines;
}

function replaceUnicode($string){
    $search  = array('u00c1', 'u00e1', 'u00c9', 'u00e9', 'u00cd', 'u00ed', 'u00d3', 'u00f3', 'u00da', 'u00fa', 'u00d1', 'u00f1', 'u00bf');
    $replace = array('A', 'a', 'E', 'e', 'I', 'i', 'O', 'o', 'U', 'u', 'N', 'n', '');
    return str_replace($search, $replace, $string);
}

?>
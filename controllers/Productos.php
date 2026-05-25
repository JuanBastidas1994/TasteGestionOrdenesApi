<?php
	/*	Variables Heredadas del Index
		$method - POST, GET, PUT, DELETE, etc.
		$request - Url y variables GET
		$input - Solo metodo POST, PUT */

require_once "clases/cl_productos.php";
$ClProductos = new cl_productos();

if($method == "GET"){
    $num_variables = count($request);
    if($num_variables == 1){
	}else if($num_variables == 2){
		if($request[1] == "ingredientes"){
			$return['success']= 1;
    		$return['mensaje']= "Lista de ingredientes";
			$return['ingredientes']= $ClProductos->allIngredientes();;
    		showResponse($return);
		}
	}else if($num_variables == 3){
        if($request[1] == "ingredientes"){
            showResponse(getIngredientesByProduct($request[2]));
        }
        if($request[1] == "opciones"){
            showResponse(getOpcionesByProduct($request[2]));
        }
    }

	$return['success']= 0;
	$return['mensaje']= "Evento no existente para productos";
	showResponse($return);
}else if($method == "POST"){
}else{
    $return['success']= 0;
    $return['mensaje']= "El metodo ".$method." para usuarios aun no esta disponible.";
    showResponse($return);
}

//Ingredientes
function getIngredientesByProduct($product_id){
	global $ClProductos;

	//VALIDACION DE LA ORDEN
	$producto = $ClProductos->getInfoBasic($product_id);
	if(!$producto){
		$return['success'] = 0;
		$return['mensaje'] = "No existe el producto";
		return $return;
	}
	$producto['image_min'] = url.$producto['image_min'];
    $producto['ingredientes'] = $ClProductos->getProductoIngredientes($product_id);

	$recipientes = $ClProductos->opciones($product_id);
	if($recipientes){
		$return['success'] = 1;
		$return['mensaje'] = "Ingredientes escogidos";
		$return['producto'] = $producto;
		$return['opciones'] = $recipientes;
	}else{
		$return['success'] = 0;
		$return['mensaje'] = "No hay ingredientes creados";
	}
	return $return;
}

//Opciones
function getOpcionesByProduct($product_id){
	global $ClProductos;

	//VALIDACION DE LA ORDEN
	$producto = $ClProductos->getInfoBasic($product_id);
	if(!$producto){
		$return['success'] = 0;
		$return['mensaje'] = "No existe el producto";
		return $return;
	}
	$producto['image_min'] = url.$producto['image_min'];
    $producto['ingredientes'] = $ClProductos->getProductoIngredientes($product_id);

	$recipientes = $ClProductos->opciones($product_id);
	if($recipientes){
		$return['success'] = 1;
		$return['mensaje'] = "Ingredientes escogidos";
		$return['producto'] = $producto;
		$return['opciones'] = $recipientes;
	}else{
		$return['success'] = 0;
		$return['mensaje'] = "No hay opciones creados para este producto";
	}
	return $return;
}
?>
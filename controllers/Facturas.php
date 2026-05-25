<?php
	/*	Variables Heredadas del Index
		$method - POST, GET, PUT, DELETE, etc.
		$request - Url y variables GET
		$input - Solo metodo POST, PUT */

require_once "clases/cl_contifico.php";
require_once "clases/cl_facturas.php";
require_once "clases/cl_productos.php";
$ClFacturas = new cl_facturas();
$Clproductos = new cl_productos();
define('cod_sistema_facturacion',1);

//CONTIFICO
$Clcontifico = new cl_contifico();

	if($method == "GET"){
		$num_variables = count($request);
        if($num_variables == 1){
			$sucursales = $ClSucursales->lista();
			if(count($sucursales)>0){
				$return['success'] = 1;
				$return['mensaje'] = "Correcto";
				$return['data'] = $sucursales;
			}else{
				$return['success'] = 0;
				$return['mensaje'] = "No hay sucursales";
			}
			showResponse($return);
		}

		$return['success']= 0;
		$return['mensaje']= "Evento no existente";
		showResponse($return);
	}
	else if($method == "POST"){
		$num_variables = count($request);
		if($num_variables == 1){
			
		}else if($num_variables == 2){
		    if($request[1] == "electronica"){
			    $return = facturarElectonicamente();
			    showResponse($return);
		    }
            if($request[1] == "anular"){
			    $return = anularElectonicamente();
			    showResponse($return);
		    }
            if($request[1] == "cobro"){
			    $return = cobroElectronico();
			    showResponse($return);
		    }
		}
		
		$return['success']= 0;
		$return['mensaje']= "Evento no existente en Methodo POST";
		showResponse($return);
	}
	else{
		$return['success']= 0;
		$return['mensaje']= "El metodo ".$method." para Login aun no esta disponible.";
		showResponse($return);
	}


/*FUNCIONES*/
function facturarElectonicamente(){
    global $ClFacturas;
    global $Clcontifico;
    global $input;
	
    extract($input);
	
	$datosObligatorios = array("id");
	foreach ($datosObligatorios as $key => $value) {
		if (!array_key_exists($value, $input)) {
		    $return['success'] = 0;
    		$return['mensaje'] = "Falta informacion, Error: Campo $value es obligatorio";
			return $return;
		}
	}

    require_once "clases/cl_ordenes.php";
    $ClOrdenes = new cl_ordenes();
    $orden = $ClOrdenes->get_orden_array($id);
    if(!$orden) {
        $return['success'] = 0;
        $return['mensaje'] = "La orden no existe";
        return $return;
    }
	
	if(ExistFacturaToOrden($id)){
        $return['success'] = 0;
        $return['mensaje'] = "La orden $id ya tiene una factura creada";
        return $return;
    }
    
    //NUEVO
    $infoFacturacion = $Clcontifico->getInfoBySucursal($orden["cod_sucursal"]);
    if(!$infoFacturacion){
        $return["success"] = 0;
        $return["mensaje"] = "La sucursal no tiene configurado un pto de emisión";
        return $return;
    } 

    $Clcontifico->API = $infoFacturacion["api"];

    if((int)$infoFacturacion["facturar"] == 0) {
        $return["mensaje"] = "La opción de facturar no está habilitada";
        $return["success"] = -1;
        return $return;
    }
    
    $msgError = "";
    $factura = armarFactura($id, false, $infoFacturacion, $cod_sucursal, $msgError);
    if(!$factura){
        $return['success'] = 0;
        $return['mensaje'] = $msgError;
        $return['detail'] = "Error en la funcion armar factura";
        return $return;
    }

    $return['factura'] = $factura;
    
    $respFactura = $Clcontifico->CreateFactura($factura);
    $idContifico = isset($respFactura['id']) ? $respFactura['id'] : 0;
    if($idContifico !== 0){
        $sentToSRI = ""; 
        $respSRI = $Clcontifico->sendToSRI($idContifico);
        $return['respSRI'] = $respSRI;
        if(isset($respSRI["autorizacion"])) {
            $sentToSRI = "EMITIDA_SRI"; 
        }
        $return['success'] = 1;
        $return['mensaje'] = "Factura creada correctamente";
        $Clcontifico->incrementSecuencial($cod_sucursal, $infoFacturacion["tipo_documento"]);
        saveOrdenFactura($id, $respFactura['id'], $respFactura['documento'], $infoFacturacion["cod_contifico_empresa"], $infoFacturacion["tipo_documento"], $sentToSRI);

    }else{
        if(isset($respFactura['mensaje'])){
            $return['success'] = 0;
            $return['mensaje'] = "Error al crear la factura. Detalle: ".$respFactura['mensaje'];
            $Clcontifico->saveErrorFactura($id, $respFactura['mensaje']);
        }else{
            $return['success'] = 0;
            $return['mensaje'] = "No se pudo crear la factura ".$Clcontifico->msgError;
            $Clcontifico->saveErrorFactura($id, $Clcontifico->msgError);
        }
    }
    $return['respContifico'] = $respFactura;
    return $return;
    
}

function anularElectonicamente(){
    global $ClFacturas;
    global $Clcontifico;
    global $input;
	
	$datosObligatorios = array("id");
	foreach ($datosObligatorios as $key => $value) {
		if (!array_key_exists($value, $input)) {
		    $return['success'] = 0;
    		$return['mensaje'] = "Falta informacion, Error: Campo $value es obligatorio";
			return $return;
		}
	}
	
	extract($input);

    require_once "clases/cl_ordenes.php";
    $ClOrdenes = new cl_ordenes();
    $orden = $ClOrdenes->get_orden_array($id);
    if(!$orden) {
        $return['success'] = 0;
        $return['mensaje'] = "La orden no existe";
        return $return;
    }
	
	if(!ExistFacturaToOrden($id)){
        $return['success'] = 0;
        $return['mensaje'] = "La Factura $id no tiene una factura electronica creada";
        return $return;
    }

    //NUEVO
    $infoFacturacion = $Clcontifico->getInfoBySucursal($orden["cod_sucursal"]);
    if(!$infoFacturacion){
        $return["success"] = 0;
        $return["mensaje"] = "La sucursal no tiene configurado un pto de emisión";
        return false;
    } 

    $Clcontifico->API = $infoFacturacion["api"];

    if((int)$infoFacturacion["facturar"] == 0) {
        $return["mensaje"] = "La opción de facturar no está habilitada";
        $return["success"] = -1;
        return $return;
    }  
    
    $msgError = "";
    $factura = armarFactura($id, true, $infoFacturacion, $cod_sucursal, $msgError);
    if(!$factura){
        $return['success'] = 0;
        $return['mensaje'] = $msgError;
        return $return;
    }
    $return['factura'] = $factura;

    $respFactura = $Clcontifico->EditFactura($factura);
    if(isset($respFactura['id'])){
        $return['success'] = 1;
        $return['mensaje'] = "Factura anulada correctamente";
        AnularOrdenFactura($id);
    }else{
        if(isset($respFactura['mensaje'])){
            $return['success'] = 0;
            $return['mensaje'] = "Error al anular la factura. Detalle: ".$respFactura['mensaje'];
            $Clcontifico->saveErrorFactura($id, $respFactura['mensaje']);
        }else{
            $return['success'] = 0;
            $return['mensaje'] = "No se pudo anular la factura ".$Clcontifico->msgError;
            $Clcontifico->saveErrorFactura($id, $Clcontifico->msgError);
        }
        
    }
    $return['respContifico'] = $respFactura;

    return $return;
}

function cobroElectronico(){
    global $ClFacturas;
    global $Clcontifico;
    global $input;
	
	
	$datosObligatorios = array("id");
	foreach ($datosObligatorios as $key => $value) {
		if (!array_key_exists($value, $input)) {
		    $return['success'] = 0;
    		$return['mensaje'] = "Falta informacion, Error: Campo $value es obligatorio";
			return $return;
		}
	}
	
	extract($input);

    $factura = ExistFacturaToOrden($id);
    if(!$factura){
        $return['success'] = 0;
        $return['mensaje'] = "La Factura $id no tiene una factura electronica creada";
        return $return;
    }else{
        if($factura['estado'] == "ANULADA"){
            $return['success'] = 0;
            $return['mensaje'] = "No se puede realizar cobros a una factura anulada";
            return $return;
        }
    }

    $pago = $ClFacturas->lastPayment($id);
    if(!$pago){
        $return['success'] = 0;
        $return['mensaje'] = "No se ha encontrado nuevos pagos, por favor realizar la información";
        return $return;
    }

    $data = [
        "forma_cobro" => getFormaPago($pago['cod_tipo_pago']),
        "monto" => ($pago['valor_pagado'] - $pago['valor_cambio']),
        "fecha" => date("d/m/Y", strtotime($pago['fecha'])),
        "tipo_ping" => "D"

    ];
    $return['factura'] = $factura;
    $return['pago'] = $pago;
    $return['cobro'] = $data;
    $payment = $Clcontifico->AddPayment($factura['id'], $data);
    if(isset($payment['id'])){
        $return['success'] = 1;
        $return['mensaje'] = "Cobro agregado correctamente a la factura";
    }else{
        if(isset($payment['mensaje'])){
            $return['success'] = 0;
            $return['mensaje'] = "Error al agregar el cobro a la factura. Detalle: ".$payment['mensaje'];
        }else{
            $return['success'] = 0;
            $return['mensaje'] = "No se pudo agregar el cobro a la factura ".$Clcontifico->msgError;
        }
        
    }
    $return['respContifico'] = $payment;
    return $return;

}

function armarFactura($cod_orden, $anular, $infoFacturacion, &$codSucursal, &$mensaje){
    global $Clcontifico;
    global $Clproductos;
    global $cod_empresa;
    require_once "clases/cl_ordenes.php";
    $ClOrdenes = new cl_ordenes();
    $orden = $ClOrdenes->get_orden_array($cod_orden);
    $porcentaje_iva = iva;
    $divisorIva = 1 + ($porcentaje_iva/100);
    
    if($orden){

            //OBTENER PRODUCTO "ADICIONALES" 
            $idAdicionalesEnProducto = ""; // DOMICILIO 2 BAKLAVA - DKVeZwLJYSGAna8P
            $adicionalesContifico = getEnvioyAdicionalByAlias("ADICIONALES", $cod_empresa, $infoFacturacion['cod_contifico_empresa']);
            if($adicionalesContifico){
                $idAdicionalesEnProducto = $adicionalesContifico['id'];
            }
            
            $acumBase12 = 0;
            $acumBase0 = 0;

            $codSucursal = $orden['cod_sucursal'];

		 	$contifico['pos'] = $infoFacturacion['pos'];
		 	if($anular){
                $factElectronica = ExistFacturaToOrden($cod_orden);
                if(!$factElectronica){
                    $mensaje = "La orden $cod_orden no tiene una factura creada";
                    return false;
                }
                if($infoFacturacion['tipo_documento'] == "FAC") {
                    $contifico['tipo_documento'] = "FAC";
                 }
                else if($infoFacturacion['tipo_documento'] == "DNA") {
                    $contifico['tipo_documento'] = "DNA";
                }
				$contifico['anulado'] = true;
				$contifico['estado'] = 'A'; //-> A de anulado
                $contifico['documento'] = $factElectronica['num_factura']; //buscar numero de factura a anular
                $contifico['id'] = $factElectronica['clave_acceso'];
		 	}else{
                 if($infoFacturacion['tipo_documento'] == "FAC") {
                    $contifico['tipo_documento'] = "FAC";
                    $contifico['documento'] = $infoFacturacion['emisor']."-".$infoFacturacion['ptoemision']."-".str_pad($infoFacturacion['secuencial'], 9, "0", STR_PAD_LEFT);
                    $contifico['estado'] = "P";
                 }
                else if($infoFacturacion['tipo_documento'] == "DNA") {
                    $contifico['tipo_documento'] = "DNA";
                    $contifico['documento'] = str_pad($infoFacturacion['secuencial_dna'], 5, "0", STR_PAD_LEFT);
                    $contifico['estado'] = "P";
                }
            }
		 	$newDate = date("d/m/Y", strtotime($orden['fecha']));
	    	$contifico['fecha_emision'] = $newDate;
	    	$contifico['autorizacion'] = "123456789"; // -> PONER CUALQUIER NUMERO, CONTIFICO CAMBIA LA AUTORIZACION
	    	$contifico['caja_id'] = ""; // -> Preguntar como saber cual es la caja!!
	    	$contifico['electronico'] = true;
	    	
	    	/*CLIENTE*/
	    	if($orden['datos_facturacion']){
	    	    $usuario = $orden['datos_facturacion'];
	    	}else{
	    	    require_once "clases/cl_usuarios.php";
    	    	$Clusuarios=new cl_usuarios();
    	    	$usuario = $Clusuarios->get($orden['cod_usuario']);
	    	}
	    	
	    	if($usuario){
	    	    if($usuario['num_documento'] !== ""){
                    if(strlen($usuario['num_documento']) == 13){
                        $cliente['ruc'] = $usuario['num_documento'];
                        $cliente['cedula'] = substr($usuario['num_documento'],0,10);
                    }
                    else{
                        $cliente['cedula'] = $usuario['num_documento'];  
                        $cliente['ruc'] = $usuario['num_documento']."001";
                    }
    	    		
    	    		$cliente['razon_social'] = $usuario['nombre'];
    	    		$cliente['telefonos'] = $usuario['telefono'];
    	    		$cliente['direccion'] = $usuario['direccion'];
    	    		$cliente['tipo'] = "N";
    	    		$cliente['email'] = $usuario['correo'];
    	    		$cliente['es_extranjero'] = false;
    	    	    $contifico['cliente'] = $cliente;
	    	    }else{
	    	        $cliente['cedula'] = "9999999999";  
                    $cliente['ruc'] = "9999999999001";
	    	        $cliente['razon_social'] = "Consumidor Final";
    	    		$cliente['telefonos'] = "0999999999";
    	    		$cliente['direccion'] = "Consumidor final";
    	    		$cliente['tipo'] = "N";
    	    		$cliente['email'] = "00000000@00.com";
    	    		$cliente['es_extranjero'] = false;
    	    	    $contifico['cliente'] = $cliente;
	    	    }
	    	}
	    	
	    	
	    	/*VENDEDOR*/
	    	$vendedor['ruc'] = "0952423606001";
    		$vendedor['cedula'] = "0952423606";
    		$vendedor['razon_social'] = "Vendedor";
    		$vendedor['telefonos'] = "0999999999";
    		$vendedor['direccion'] = "Juan montalvo";
    		$vendedor['tipo'] = "N";
    		$vendedor['email'] = "juankbastidasjuve@gmail.com";
    		$vendedor['es_extranjero'] = false;
    		$contifico['vendedor'] = $vendedor;
    		
    		/*DETALLE DE LA FACTURA*/
            $detalle=[];
            $x=0;
            foreach($orden['detalle'] as $item){
                $resp = getProductoById($item['cod_producto'], $infoFacturacion['cod_contifico_empresa']);
                if($resp){
                    $detalle[$x] = [
                            'producto_id' => $resp['id'],
                            'cantidad' => $item['cantidad'],
                            'precio' => $item['precio_no_tax'],
                            'porcentaje_iva' => $porcentaje_iva,
                            'porcentaje_descuento' => $item['descuento_porcentaje'],
                            'base_cero' => 0,
                            'base_gravable' => $item['subtotal_12'],
                            'base_no_gravable' => $item['subtotal_0'],
                            'valor_ice' => 0,
                            'porcentaje_ice' => 0,
                        ];

                    //AGREGAR ADICIONALES COMO OTRO PRODUCTO
                    if($item["adicional_no_tax_unidad"] > 0) {
                        
                        if($cod_empresa == 24){ //DANILO
                            
                            $acumAdicionalNoDatabase = 0;
                            $opciones = $ClOrdenes->decodificarOpcionesByDetalle($item['descripcion']);
                            foreach($opciones as $opcion){
                                
                                $cod_opcion = $opcion['id'];
                                
                                if(getOpcionIsDatabase($cod_opcion)){
                                    $details = $opcion['detalles'];
                                    
                                    foreach($details as $detail){
                                        
                                        if($detail['aumentar_precio'] == 1){
                                        
                                            $optionitem = getOptionDetalle($detail['id']);
                                            if(!$optionitem){
                                                $mensaje = "Ocurrió un problema al encontrar la opción ".$detail['nombre'];
                                                return false;
                                            }
                                            
                                            
                                            $optionContifico = getProductoById($optionitem['cod_producto'], $infoFacturacion['cod_contifico_empresa']);
                                            if(!$optionContifico){
                                                $mensaje = "No existe el producto ".$optionContifico['nombre']." en el sistema, por favor verificar ";
                                                return false;
                                            }
                                            
                                            $x++;
                                            $quantity = $detail['cantidad'] * $item['cantidad'];
                                            $price = number_format($optionitem['precio_no_tax'],2);
                                            $base0 = ($optionitem['cobra_iva'] == 0) ? $price * $quantity : 0;
                                            $base12 = ($optionitem['cobra_iva'] == 1) ? $price * $quantity : 0;
                                            $detalle[$x] = [
                                                'producto_id' => $optionContifico['id'],
                                                'cantidad' => $quantity,
                                                'precio' => $price,
                                                'porcentaje_iva' => $porcentaje_iva,
                                                'porcentaje_descuento' => 0,
                                                'base_cero' => 0,
                                                'base_gravable' => number_format($base12,2),
                                                'base_no_gravable' => number_format($base0,2),
                                                'valor_ice' => 0,
                                                'porcentaje_ice' => 0,
                                            ];
                                            
                                        }
                                        
                                    }
                                }
                                
                            }
                        }else{
                            
                            $x++;
                            
                            if($idAdicionalesEnProducto === ""){
                                $mensaje = "No esta ligado los adicionales con contífico, por favor ir al módulo de integraciones";
                                $mensaje = $item;
                                $mensaje = $ClOrdenes->decodificarOpcionesByDetalle($item['descripcion']);
                                return false;
                            }
                            
                            $detalle[$x]['producto_id'] = $idAdicionalesEnProducto;
                            $detalle[$x]['cantidad'] = $item['cantidad']; 
                            $detalle[$x]['precio'] = $item["adicional_no_tax_unidad"];
                            $detalle[$x]['porcentaje_iva'] = $porcentaje_iva; 
                            $detalle[$x]['porcentaje_descuento'] = 0; 
                            $detalle[$x]['base_cero'] = 0;
                            $detalle[$x]['base_gravable'] = $item["adicional_no_tax_total"];
                            $detalle[$x]['base_no_gravable'] = 0;
                            $detalle[$x]['valor_ice'] = 0;
                            $detalle[$x]['porcentaje_ice'] = 0;
                        }
                        
                    }
					$x++;
                }
                else {
                    $mensaje = "No existe el producto ".$item['nombre']." en el sistema, por favor verificar ";
                    return false;
                }
            }
            
            /*AUMENTAR EL ENVIO COMO PRODUCTO*/
            if($orden['envio']>0){
                $iva = 0;
                $envioBase0 = 0;
                $envioBase12 = 0;
                $gravaIva = empresaGravaIva($cod_empresa);
                if($gravaIva == 1){
                    $iva = 12;
                    $envioBase12 = $orden['envio'];
                    $acumBase12 = $acumBase12 + $envioBase12;
                }else{
                    $envioBase0 = $orden['envio'];
                    $acumBase0 = $acumBase0 + $envioBase0;
                }
                
                $resp = getEnvioyAdicionalByAlias("ENVIO_DOMICILIO", $cod_empresa, $infoFacturacion['cod_contifico_empresa']);
                if($resp){
                    $detalle[$x]['producto_id'] = $resp['id'];
                    $detalle[$x]['cantidad'] = 1; 
                    $detalle[$x]['precio'] = $orden['envio']; 
                    $detalle[$x]['porcentaje_iva'] = $iva; 
                    $detalle[$x]['porcentaje_descuento'] = 0; 
                    $detalle[$x]['base_cero'] = 0;
                    $detalle[$x]['base_gravable'] = $envioBase12;
                    $detalle[$x]['base_no_gravable'] = $envioBase0;
                    $detalle[$x]['valor_ice'] = 0;
    				$detalle[$x]['porcentaje_ice'] = 0;
                }else{
                    $mensaje = "No esta ligado el servicio a Domicilio con contífico, por favor ir al módulo de integraciones";
                    return false;
                }
            }
            
            /*INFO DE LA FACTURA*/
            $contifico['detalles'] = $detalle;
            $contifico['descripcion'] = "N. Orden ".$orden['cod_orden'];
			$contifico['subtotal_0'] = $orden['subtotal0'];
			$contifico['subtotal_12'] = $orden['subtotal12'];
			$contifico['iva'] = $orden['iva'];
			$contifico['servicio'] = 0;
			$contifico['total'] = $orden['total'];
			$contifico['adicional1'] = "";
			$contifico['adicional2'] = "";
			
			/*FORMA DE PAGO*/
			$pagos=[];
            $x=0;
            foreach($orden['pagos'] as $item){
                $pagos[$x]['forma_cobro'] = getFormaPago($item['forma_pago']);
                $pagos[$x]['monto'] = $item['monto'];
                $pagos[$x]['numero_cheque'] = NULL;
                $pagos[$x]['tipo_ping'] = "D";
                $x++;
            }
             $contifico['cobros'] = $pagos;
			
            // GUARDAR LOGS
            $file = "logArmarFactura.log";
            $fecha = fecha();
            $log = "[$fecha] " . json_encode($contifico);
            file_put_contents($file, PHP_EOL . $log, FILE_APPEND);

			return $contifico;
                
    }else{
        $mensaje = "No se encontro informacion de la orden en el sistema";
        return false;
    }
}

//FUNCIONES ORDEN CONTIFICO

function ExistFacturaToOrden($cod_orden){
    $query = "SELECT * 
                FROM tb_orden_factura_electronica 
                WHERE cod_orden = $cod_orden 
                AND estado IN ('CREADA', 'EMITIDA_SRI')";
    $resp = Conexion::buscarRegistro($query);
    return $resp;
}

function saveOrdenFactura($pcod_orden, $pclaveAcceso, $pnumFactura, $cod_contifico_empresa, $tipo_documento, $estado = "CREADA"){
    $query = "INSERT INTO tb_orden_factura_electronica(cod_orden, num_factura, clave_acceso, estado, cod_sistema_facturacion, cod_contifico_empresa, tipo) 
            VALUES('$pcod_orden','$pnumFactura','$pclaveAcceso','$estado','".cod_sistema_facturacion."', $cod_contifico_empresa, '$tipo_documento')";
    $resp = Conexion::ejecutar($query, NULL);
    return $resp;
}

function AnularOrdenFactura($pcod_orden){
    $query = "UPDATE tb_orden_factura_electronica 
                SET estado = 'ANULADA' 
                WHERE cod_orden = $pcod_orden";
    $resp = Conexion::ejecutar($query, NULL);
    return $resp;
}

function getFormaPago($forma){
    if($forma == "E")
        return "EF";
    else if($forma == "T")
        return "TC";  
    else if($forma == "P")
        return "EF";  
    else if($forma == "DB")
        return "EF";
    else
        return "EF";
}

//FUNCIONES PRODUCTO CONTIFICO
function setProductoById($id, $cod_producto){
    $query = "INSERT INTO tb_productos_facturacion(id, cod_producto, cod_sistema_facturacion) 
            VALUES('$id', $cod_producto, 1)";
    $resp = Conexion::ejecutar($query, NULL);
    return $resp;
}

function getProductoById($cod_producto, $cod_contifico_empresa){
    $query = "SELECT * 
                FROM tb_productos_facturacion 
                WHERE cod_producto = $cod_producto
                AND cod_contifico_empresa = $cod_contifico_empresa";
    $resp = Conexion::buscarRegistro($query);
    return $resp;
}

function getOpcionIsDatabase($cod_opcion){
    $query = "SELECT * FROM tb_productos_opciones WHERE cod_producto_opcion = $cod_opcion";
    $resp = Conexion::buscarRegistro($query);
    if($resp){
        if($resp['isDatabase'] == 1)
            return true;
    }
    return false;
}

function getOptionDetalle($cod_opcion_detalle){
    $query = "SELECT p.cod_producto, p.nombre, p.precio, p.precio_no_tax, p.cobra_iva 
            FROM tb_productos_opciones_detalle od
            INNER JOIN tb_productos p ON p.cod_producto = od.item
            WHERE od.cod_producto_opciones_detalle = $cod_opcion_detalle";
    $resp = Conexion::buscarRegistro($query);
    return $resp;
}

function empresaGravaIva($cod_empresa){
    $query = "SELECT * FROM tb_empresas WHERE cod_empresa = $cod_empresa";
    $resp = Conexion::buscarRegistro($query);
    if($resp){
        return $resp['envio_grava_iva'];
    }
    return 0;
}

function getEnvioyAdicionalByAlias($alias, $cod_empresa, $ruc_id){
    $query = "SELECT * FROM tb_productos_envio_facturacion 
            WHERE alias = '$alias' 
            AND cod_empresa = $cod_empresa 
            AND cod_contifico_empresa = $ruc_id
            AND cod_sistema_facturacion = ".cod_sistema_facturacion;
    $resp = Conexion::buscarRegistro($query);
    return $resp;
}

function noRound($value, $option) { 
    if($option) {
        $noRound = explode(".", $value);
        if(count($noRound) > 1)
            return $noRound[0] . "." . substr($noRound[1], 0, 2);
        return $value;
    }
    return number_format($value, 2);
}
?>
<?php
	/*	Variables Heredadas del Index
		$method - POST, GET, PUT, DELETE, etc.
		$request - Url y variables GET
		$input - Solo metodo POST, PUT */

require_once "clases/cl_runfood.php";
require_once "clases/cl_facturas.php";
require_once "clases/cl_productos.php";
$ClFacturas = new cl_facturas();
$Clproductos = new cl_productos();
define('cod_sistema_facturacion',3);

$ClRunfood = new cl_runfood();

	if($method == "POST"){
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
    ini_set('serialize_precision',4);
    global $ClFacturas;
    global $ClRunfood;
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
    $infoFacturacion = $ClRunfood->getSucursal($orden["cod_sucursal"]);
    if(!$infoFacturacion){
        $return["success"] = 0;
        $return["mensaje"] = "La sucursal no tiene configurado un pto de emisión";
        return $return;
    }

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
    mylogFile("runfood.log", $ClRunfood->armarTrama($factura), "TRAMA"); //TRAMA ENVIADA A RUNFOOD

    $invoice = $ClRunfood->sendInvoice($factura);
    if(!$invoice){
        return [ 'success' => 0, 'mensaje' => 'No se pudo enviar la factura a Runfood '.$ClRunfood->msgError ];
    }

    if(!isset($invoice['id'])){
        return [ 'success' => 0, 'mensaje' => 'Runfood no respondio con un ID valido de Orden' ];
    }

    if(!saveOrdenFactura($id, $invoice['id'], $invoice['id'], $orden["cod_sucursal"], $infoFacturacion['tipo_documento'])){
        return [ 'success' => 0, 'mensaje' => 'La orden se envio a runfood pero no se pudo ligar con la orden de taste' ];
    }
    
    
    return [ 
        'success' => 1, 
        'mensaje' => 'Orden Enviada a Runfood correctamente',
        'senddata' => $factura,
        'respRunfood' => $invoice
    ];

    
    http_response_code(200);
	echo json_encode($return, JSON_NUMERIC_CHECK);
	exit();
}

function anularElectonicamente(){
    global $ClRunfood;
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
    $orden = $ClOrdenes->getOrdenAnulada($id);
    if(!$orden) {
        $return['success'] = 0;
        $return['mensaje'] = "Una orden no puede anularse electronicamente si no se ha anulado localmente";
        return $return;
    }
    
    //NUEVO
    $infoFacturacion = $ClRunfood->getSucursal($orden["cod_sucursal"]);
    if(!$infoFacturacion){
        $return["success"] = 0;
        $return["mensaje"] = "La sucursal no tiene configurado un pto de emisión";
        return false;
    }
    
    $motivo = $ClOrdenes->getMotivoAnulacion($id);
    
    $data = ["id" => $orden['num_factura'],"idUsuario" => $ClRunfood->userId,"motivo" => $motivo];
    mylogFile("runfood_anulacion.log", json_encode($data), "TRAMA ANULACION"); 
    $invoice = $ClRunfood->revertInvoice($orden['num_factura'], $motivo);
    if(!$invoice){
        return [ 'success' => 0, 'mensaje' => 'No se pudo anular la factura en Runfood '.$ClRunfood->msgError ];
    }
    
    if(!isset($invoice['id'])){
        return [ 'success' => 0, 'mensaje' => 'Runfood no respondio con un ID valido de Orden' ];
    }

    if(!AnularOrdenFactura($id)){
        return [ 'success' => 0, 'mensaje' => 'La orden se anulo en runfood pero no se pudo cambiar a estado anulada internamente en taste' ];
    }
    

    return [ 
        'success' => 1, 
        'mensaje' => 'Orden Anulada en Runfood correctamente',
        'senddata' => $factura,
        'respRunfood' => $invoice
    ];
}

function armarFactura($cod_orden, $anular, $infoFacturacion, &$codSucursal, &$mensaje){
    global $Clcontifico;
    global $Clproductos;
    global $cod_empresa;
    require_once "clases/cl_usuarios.php";
    require_once "clases/cl_ordenes.php";
    $Clusuarios = new cl_usuarios();
    $ClOrdenes = new cl_ordenes();
    
    $orden = $ClOrdenes->get_orden_array($cod_orden);
    $porcentaje_iva = iva;
    $divisorIva = 1 + ($porcentaje_iva/100);
    
    if(!$orden){
        $mensaje = "No se encontro informacion de la orden en el sistema";
        return false;
    }

    //OBTENER PRODUCTO "ADICIONALES" 
    $idAdicionalesEnProducto = "";
    $adicionalesContifico = getEnvioyAdicionalByAlias("ADICIONALES", $cod_empresa, $infoFacturacion['cod_sucursal']);
    if($adicionalesContifico){
        $idAdicionalesEnProducto = $adicionalesContifico['id'];
    }
    $codSucursal = $orden['cod_sucursal'];
    
    //Ventas
    $ventasObj = null;
    $ventasObj['documento'] = ($infoFacturacion['tipo_documento'] === "FAC") ? 1 : 3;
    $ventasObj['base0'] = $orden['subtotal0'];
    $ventasObj['baseIva'] = $orden['subtotal12'];
    $ventasObj['iva'] = $orden['iva'];
    $ventasObj['descuento'] = $orden['descuento'];
    $ventasObj['total'] = $orden['total'];
    $ventasObj['propina'] = 0;
    $ventasObj['servicio'] = 0;
    
    /*Cliente*/
    if($orden['datos_facturacion']){
        $usuario = $orden['datos_facturacion'];
    }else{
        $usuario = $Clusuarios->get($orden['cod_usuario']);
    }
    if($usuario){
        $ventasObj['validarCedula'] = true;
        $ventasObj['cedula'] = $usuario['num_documento'];
        $ventasObj['direccion'] = $usuario['direccion'];
        $ventasObj['email'] = $usuario['correo'];
        $ventasObj['fechaNacimiento'] = "1994-12-07";
        $ventasObj['razonSocial'] = $usuario['nombre'];
        $ventasObj['nombreComercial'] = $usuario['nombre'];
        $ventasObj['telefono'] = $usuario['telefono'];
    }
    
    /*DETALLE DE LA FACTURA*/
    $detalle = [];
    $x = 0;
    $idDetalle = 0;

    foreach($orden['detalle'] as $item){

        // ITEM PRINCIPAL
        $resp = getProductoById($item['cod_producto'], $infoFacturacion['cod_sucursal']);
        
        //OPCIONES
        $resultado = ['principales' => [], 'adicionales' => []];
        if(!empty($item['opciones'])) {
            $resultado = armarAdicionalesFactura(
                $item['opciones'], $item['cantidad'], $infoFacturacion['cod_sucursal'], $idDetalle
            );
        }
        
        //PROCESO DE ASIGNACION
        if($resp){
            $idDetalle++;
            $base0 = 0;
            $base12 = 0;
            if($item['cobra_iva'] == 1)
                $base12 = $item['precio'];
            else
                $base0 = $item['precio'];
            
            $pNoTax = noRound($item['precio'] / $divisorIva, false);
            $pNoTax12 = noRound(($base12 / $divisorIva) * $item['cantidad'], false);
            $pNoTax0 = noRound(($base0 / $divisorIva) * $item['cantidad'], false);
            
            $baseGravable12 = $pNoTax12 - ($pNoTax12 * ($item["descuento_porcentaje"]) / 100);
            $baseGravable0 = $pNoTax0 - ($pNoTax0 * ($item["descuento_porcentaje"]) / 100);

            $detalle[$x]['id'] = intval($resp['id']);
            $detalle[$x]['codigo'] = intval($resp['id']);
            $detalle[$x]['descripcion'] = $item['nombre'];
            $detalle[$x]['pagaIva'] = ($item['cobra_iva'] == 1) ? true : false;
            $detalle[$x]['_IVA_'] = ($item['cobra_iva'] == 1) ? 12 : 0;
            $detalle[$x]['esComponente'] = false;
            $detalle[$x]['habilitado'] = true;
            $detalle[$x]['pvp1'] = $item["precio"];
            $detalle[$x]['observacion'] = "";
            $detalle[$x]['cantidad'] = intval($item['cantidad']);
            $detalle[$x]['pvpSeleccionado'] = "pvp1";
            $detalle[$x]['descuento'] = number_format($item['descuento'], 2);
            $detalle[$x]['idDetalle'] = $idDetalle;
            $detalle[$x]['dinamico'] = false;
            $detalle[$x]['cantidadExceso'] = 0;
            
            if (!empty($resultado['adicionales'])) {
                $detalle[$x]['dinamico'] = true;
                $detalle[$x]['articulosDinamicos'] = $resultado['adicionales'];
            }
            
            foreach ($resultado['principales'] as $principal) {
                $x++;
                $idDetalle++;
                $principal['idDetalle'] = $idDetalle;
                $detalle[$x] = $principal;
            }
        }else{
            // Padre no ligado a Runfood, los adicionales van al PRIMER principal
            $primerPrincipal = true;
            foreach ($resultado['principales'] as $principal) {
                $idDetalle++;
                $principal['idDetalle'] = $idDetalle;
        
                if ($primerPrincipal && !empty($resultado['adicionales'])) {
                    $principal['dinamico'] = true;
                    $principal['articulosDinamicos'] = $resultado['adicionales'];
                    $primerPrincipal = false;
                }
        
                $detalle[$x] = $principal;
                $x++;
            }
        }
    }
    
    /*AUMENTAR EL ENVIO COMO PRODUCTO*/
    if($orden['envio'] > 0){
        $gravaIva = empresaGravaIva($cod_empresa);
        $resp = getEnvioyAdicionalByAlias("ENVIO_DOMICILIO", $cod_empresa, $infoFacturacion['cod_sucursal']);
        if($resp){
            $idDetalle++;
            $detalle[$x]['id'] = intval($resp['id']);
            $detalle[$x]['codigo'] = $resp['id'];
            $detalle[$x]['descripcion'] = "Envío a Domicilio";
            $detalle[$x]['pagaIva'] = ($gravaIva == 1) ? true : false;
            $detalle[$x]['_IVA_'] = ($item['cobra_iva'] == 1) ? 12 : 0;
            $detalle[$x]['esComponente'] = false;
            $detalle[$x]['habilitado'] = true;
            $detalle[$x]['pvp1'] = (float)number_format($orden['envio'], 2);
            $detalle[$x]['observacion'] = "";
            $detalle[$x]['cantidad'] = 1;
            $detalle[$x]['pvpSeleccionado'] = "pvp1";
            $detalle[$x]['descuento'] = number_format(0, 2);
            $detalle[$x]['idDetalle'] = $idDetalle;
            $detalle[$x]['dinamico'] = false;
            $detalle[$x]['cantidadExceso'] = 0;
        }else{
            $mensaje = "No esta ligado el servicio a Domicilio con Runfood, por favor ir al módulo de integraciones";
            return false;
        }
    }
    $ventasObj['detalle'] = $detalle;
    
    /*FORMA DE PAGO*/
    $pagos = [];
    $x = 0;
    foreach($orden['pagos'] as $item){
        $pago = getFormaPago($item['forma_pago'], $infoFacturacion['cod_sucursal']);
        $pagos[$x]['idFormaPago'] = $pago['id'];
        $pagos[$x]['monto'] = number_format($item['monto'], 2);
        $pagos[$x]['idMarcaTarjeta'] = NULL;
        $pagos[$x]['idTipoTarjeta'] = NULL;
        $pagos[$x]['numeroTransaccion'] = NULL;
        $pagos[$x]['propina'] = 0;
        $x++;
    }
    $ventasObj['pagos'] = $pagos;

    //Pedido
    $pedidoObj = null;
    $pedidoObj['estado'] = "A";
    $pedidoObj['base0'] = number_format($orden['subtotal0'], 2);
    $pedidoObj['baseIva'] = $orden['subtotal12'];
    $pedidoObj['iva'] = $orden['iva'];
    $pedidoObj['descuentoTotal'] = $orden['descuento'];
    $pedidoObj['total'] = $orden['total'];
    $pedidoObj['propina'] = 0;
    $pedidoObj['mesa'] = null;
    $pedidoObj['formaDespacho'] = ($orden['is_envio'] == "1") ? "DELIVERY" : "PICKUP";
    $pedidoObj['maxIdDetalle'] = count($detalle);
    $pedidoObj['detalle'] = $detalle;
    $pedidoObj['ventas'] = $ventasObj;
    
    // GUARDAR LOGS
    $file = "logArmarFacturaRunfood.log";
    $fecha = fecha();
    $log = "[$fecha] " . json_encode($pedidoObj, JSON_NUMERIC_CHECK);
    file_put_contents($file, PHP_EOL . $log, FILE_APPEND);

    return $pedidoObj;
}

function armarAdicionalesFactura($opciones, $cantidad, $idBussinessInvoices, &$indiceInicial) {
    $detallesPrincipales = [];
    $detallesAdicionales = [];

    if (!$opciones) {
        return ['principales' => $detallesPrincipales, 'adicionales' => $detallesAdicionales];
    }

    global $Clproductos;

    foreach ($opciones as $opcion) {
        foreach ($opcion["detalles"] as $detalle) {

            // ¿Este detalle tiene mapeo directo a Runfood?
            $mappedProduct = $Clproductos->getProductoFromOpcionDetalleFacturacion(
                $detalle["id"], $idBussinessInvoices
            );

            if ($mappedProduct) {
                // → Va como producto principal (mismo nivel que el producto padre)
                $indiceInicial++;
                $detallesPrincipales[] = [
                    'id'              => intval($mappedProduct['id_runfood']),
                    'codigo'          => intval($mappedProduct['id_runfood']),
                    'pvp1'            => 0, // El precio ya está en el producto padre
                    'cantidad'        => intval($cantidad),
                    'descripcion'     => $mappedProduct['nombre_runfood'] ?? "",
                    'pagaIva'         => false,
                    '_IVA_'           => 0,
                    'esComponente'    => false,
                    'habilitado'      => true,
                    'observacion'     => "",
                    'pvpSeleccionado' => "pvp1",
                    'descuento'       => 0,
                    'idDetalle'       => $indiceInicial,
                    'dinamico'        => false,
                    'cantidadExceso'  => 0,
                ];
                continue; // No procesar ingredientes de esta opción
            }

            // → Flujo normal: isDatabase o ingredientes
            $productoFromOpcionDetalle = $Clproductos->getProductFromOpcionDetalleIsDatabase(
                $detalle["id"], $idBussinessInvoices
            );

            if ($productoFromOpcionDetalle) {
                $indiceInicial++;
                $detallesAdicionales[] = [
                    'id'              => intval($productoFromOpcionDetalle["id"]),
                    'codigo'          => intval($productoFromOpcionDetalle['id']),
                    'pvp1'            => $productoFromOpcionDetalle["precio"],
                    'cantidad'        => number_format($detalle["cantidad"] * $cantidad, 2),
                    'descripcion'     => "",
                    'pagaIva'         => false,
                    '_IVA_'           => 0,
                    'esComponente'    => false,
                    'habilitado'      => true,
                    'observacion'     => "",
                    'pvpSeleccionado' => "pvp1",
                    'descuento'       => 0,
                    'idDetalle'       => $indiceInicial,
                    'dinamico'        => false,
                    'cantidadExceso'  => 0,
                ];
            }

            $ingredientes = $Clproductos->getProductoOpcionesIngredientes(
                $detalle["id"], $idBussinessInvoices
            );

            if ($ingredientes) {
                foreach ($ingredientes as $ing) {
                    $indiceInicial++;
                    $detallesAdicionales[] = [
                        'id'              => intval($ing["id"]),
                        'codigo'          => intval($ing['id']),
                        'pvp1'            => $ing["precio"],
                        'cantidad'        => number_format(($ing["valor"] * $detalle["cantidad"]) * $cantidad, 2),
                        'descripcion'     => $ing["ingrediente"],
                        'pagaIva'         => false,
                        '_IVA_'           => 0,
                        'esComponente'    => false,
                        'habilitado'      => true,
                        'observacion'     => "",
                        'pvpSeleccionado' => "pvp1",
                        'descuento'       => 0,
                        'idDetalle'       => $indiceInicial,
                        'dinamico'        => false,
                        'cantidadExceso'  => 0,
                    ];
                }
            }
        }
    }

    return ['principales' => $detallesPrincipales, 'adicionales' => $detallesAdicionales];
}



function armarAdicionalesFacturaDeprecated($opciones, $cantidad, $idBussinessInvoices, &$indiceInicial){
    $detalles = [];
    if($opciones){
        global $Clproductos;
        foreach ($opciones as $opcion) {
            foreach ($opcion["detalles"] as $detalle) {
                $productoFromOpcionDetalle = $Clproductos->getProductFromOpcionDetalleIsDatabase($detalle["id"], $idBussinessInvoices);
                // dd_min($productoFromOpcionDetalle);
                if($productoFromOpcionDetalle){
                    $indiceInicial++;
                    $detalles[] = [
                        'id' => intval($productoFromOpcionDetalle["id"]),
                        'codigo' => intval($productoFromOpcionDetalle['id']),
                        'pvp1' => $productoFromOpcionDetalle["precio"],
                        'cantidad' => number_format($detalle["cantidad"] * $cantidad, 2),
                        'descripcion' => "",
                        'pagaIva' => false,
                        '_IVA_' => 0,
                        'esComponente' => false,
                        'habilitado' => true,
                        'observacion' => "",
                        'pvpSeleccionado' => "pvp1",
                        'descuento' => 0,
                        'idDetalle' => $indiceInicial,
                        'dinamico' => false,
                        'cantidadExceso' => 0,
                    ];
                    
                }

                $productoOpcionesIngrendientes = $Clproductos->getProductoOpcionesIngredientes($detalle["id"], $idBussinessInvoices);
                if($productoOpcionesIngrendientes) {
                    foreach ($productoOpcionesIngrendientes as $prodOpcIngredientes) {
                        $indiceInicial++;
                        $detalles[] = [
                            'id' => intval($prodOpcIngredientes["id"]),
                            'codigo' => intval($prodOpcIngredientes['id']),
                            'pvp1' => $prodOpcIngredientes["precio"],
                            'cantidad' => number_format(($prodOpcIngredientes["valor"] * $detalle["cantidad"]) * $cantidad, 2),
                            'descripcion' => $prodOpcIngredientes["ingrediente"],
                            'pagaIva' => false,
                            '_IVA_' => 0,
                            'esComponente' => false,
                            'habilitado' => true,
                            'observacion' => "",
                            'pvpSeleccionado' => "pvp1",
                            'descuento' => 0,
                            'idDetalle' => $indiceInicial,
                            'dinamico' => false,
                            'cantidadExceso' => 0,
                        ];
                        
                    }
                }
            }
        }
    }
    return $detalles;
}

function dd_min($var) {
    echo json_encode($var);
    // echo '<pre>' . htmlspecialchars(print_r($var, true), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</pre>';
    exit;
}

//FUNCIONES ORDEN CONTIFICO
function ExistFacturaToOrden($cod_orden){
    $query = "SELECT * 
                FROM tb_orden_factura_electronica 
                WHERE cod_orden = $cod_orden 
                AND estado = 'CREADA'";
    $resp = Conexion::buscarRegistro($query);
    return $resp;
}

function saveOrdenFactura($pcod_orden, $pclaveAcceso, $pnumFactura, $cod_contifico_empresa, $tipo_documento){
    $query = "INSERT INTO tb_orden_factura_electronica(cod_orden, num_factura, clave_acceso, estado, cod_sistema_facturacion, cod_contifico_empresa, tipo) 
            VALUES('$pcod_orden','$pnumFactura','$pclaveAcceso','CREADA','".cod_sistema_facturacion."', $cod_contifico_empresa, '$tipo_documento')";
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

function getFormaPago($forma, $cod_contifico_empresa){
    $query = "SELECT *
            FROM tb_formas_pago_facturacion
            WHERE cod_forma_pago = '$forma'
            AND cod_contifico_empresa = $cod_contifico_empresa";
    return Conexion::buscarRegistro($query);
}

//FUNCIONES PRODUCTO CONTIFICO
function getProductoById($cod_producto, $cod_contifico_empresa){
    $query = "SELECT * 
                FROM tb_productos_facturacion 
                WHERE cod_producto = $cod_producto
                AND cod_contifico_empresa = $cod_contifico_empresa";
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
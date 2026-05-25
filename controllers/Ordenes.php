<?php
/*	Variables Heredadas del Index
		$method - POST, GET, PUT, DELETE, etc.
		$request - Url y variables GET
		$input - Solo metodo POST, PUT */

require_once "clases/cl_ordenes.php";
require_once "clases/cl_couriers.php";
require_once "clases/cl_usuarios.php";
require_once "clases/cl_sucursales.php";
require_once "clases/cl_productos.php";
$Clordenes = new cl_ordenes();
$ClCouriers = new cl_couriers();
$ClUsuarios = new cl_usuarios();
$ClSucursales = new cl_sucursales();
$ClProductos = new cl_productos();


if($method == "GET"){
	$num_variables = count($request);
	if($num_variables == 1){
		
	}else if($num_variables == 2){
		if(is_numeric($request[1])){
			$cod_orden = $request[1];
			showResponse(detalleOrden($cod_orden));
		}
		else if($request[1] == "historial"){
			if(!isset($_GET["sucursal_id"])){
				$sucursal_id = $_GET["sucursal_id"];
				$return["success"] = 0;
				$return["mensaje"] = "Falta el ID de la sucursal";
				showResponse($return);
			}
			else
				showResponse(historialOrdenes($_GET["sucursal_id"]));
		}
		else if($request[1] == "sin-finalizar"){
			if(!isset($_GET["sucursal_id"])){
				$sucursal_id = $_GET["sucursal_id"];
				$return["success"] = 0;
				$return["mensaje"] = "Falta el ID de la sucursal";
				showResponse($return);
			}
			else
				showResponse(ordenesSinFinalizar($_GET["sucursal_id"]));
		}
		else if($request[1] == "finalizar-antiguas"){
			if(!isset($_GET["sucursal_id"])){
				$return["success"] = 0;
				$return["mensaje"] = "Falta el ID de la sucursal";
				showResponse($return);
			}
			else
				showResponse(finalizarAntiguas($_GET["sucursal_id"]));
		}
	}else if($num_variables == 3){
		if($request[1] == "rezagadas"){
			showResponse(ordenesRezagadas($request[2]));
		}
		if($request[1] == "motorizados"){
			showResponse(getMisMotorizados($request[2]));
		}
		if($request[1] == "recipientes"){
			showResponse(getRecipientes($request[2]));
		}
		if($request[1] == "cierre-diario"){
			showResponse(cierreDiario($request[2]));
		}
	}
	else if($num_variables == 4){
		if($request[1] == "programadas"){
			$cod_sucursal = $request[2];
			$fecha = $request[3];
			showResponse(ordenesProgramadas($request[2], $request[3]));
		}
	}

	$return['success']= 0;
	$return['mensaje']= "Evento no existente para órdenes";
	showResponse($return);
}
else if($method == "POST"){
	$num_variables = count($request);
	if($num_variables == 1){
		showResponse(listaOrdenes($input));
	}else if($num_variables == 2){
		$metodo = $request[1];
		if($metodo == "lista-mapa"){
		    showResponse(listaOrdenesMapa($input));
		}else if($metodo == "asignar"){
			showResponse(asignarOrden($input));
		}else if($metodo == "asignar-masiva"){
			showResponse(asignarOrdenesMasivas($input));
		}else if($metodo == "asignar-flota"){
			showResponse(asignarFlota($input));
		}else if($metodo == "set-estado"){
			showResponse(setEstado($input));
		}else if($metodo == "cancelar"){
			showResponse(cancelarOrden($input));
		}else if($metodo == "cancelar-courier"){
			showResponse(cancelarAsignacion($input));
		}else if($metodo == "revertir-pago"){
			showResponse(revertirPago($input));
		}else if($metodo == "generar-link"){
			showResponse(generarLink($input));
		}else if($metodo == "set-recipiente"){
			showResponse(setRecipientes($input));
		}
	}else if($num_variables == 3){
		
	}
	
	$return['success']= 0;
	$return['mensaje']= "Evento no existente para órdenes";
	showResponse($return);
}	
else{
	$return['success']= 0;
	$return['mensaje']= "El metodo ".$method." para Órdenes aun no esta disponible.";
	showResponse($return);
}

function listaOrdenes($input){
    global $Clordenes;
	//global $ClUsuarios;
	global $ClCouriers;
	
    $errorMsg = "";
	$datosObligatorios = array("estado","tipo","cod_sucursal");
    if(!validate($datosObligatorios, $input, $errorMsg)){
        $return['success'] = 0;
        $return['mensaje'] = $errorMsg;
        showResponse($return);
    }
	extract($input);

	$ordenes = $Clordenes->listaGestionOrdenes($estado, $tipo, $cod_sucursal);
	if($ordenes){
		foreach ($ordenes as $key => $item) {
			$ordenes[$key]['creada']['fecha'] = fechaLatinoShort($item['fecha']);
			$ordenes[$key]['creada']['hora'] = getHourToDateTime($item['fecha']);
			$ordenes[$key]['creada']['agoText'] = 
				hoursAgoDate($item['fecha'],$ordenes[$key]['creada']['agoDias'],$ordenes[$key]['creada']['agoHoras'],$ordenes[$key]['creada']['agoMinutos']);
				
		    //Retiro
			$ordenes[$key]['retiro']['fecha'] = fechaLatinoShort($item['hora_retiro']);
			$ordenes[$key]['retiro']['hora'] = getHourToDateTime($item['hora_retiro']);
			
			$ordenes[$key]['timeline'] = $Clordenes->getOrdenTimeline($item['id']);
			
			//COURIER
			if($item['cod_courier'] != 101)
	            $ordenes[$key]['courier'] = $ClCouriers->get($item['cod_courier']);
	        else
	            $ordenes[$key]['courier'] = $ClCouriers->getFlota($item['id']);
	            
			//MOTORIZADO
	        $ordenes[$key]['motorizado'] = $Clordenes->getMotorizadoByOrden($item['id']);
			$ordenes[$key]['driverViaApi'] = false;
			if($estado == "ASIGNADA" && !$ordenes[$key]['motorizado']){
				$courier_id = $ordenes[$key]['courier']['id'];
				if($courier_id == 1 || $courier_id == 5){	//GACELA o PEDIDOS YA
					$ordenes[$key]['motorizado'] = findDriverFromApi($courier_id, $item['token'], $item['id'],  $item['cod_sucursal']);
					$ordenes[$key]['driverViaApi'] = true;
				}
			}
	        
	        //COLOR
	        $color = "bg-warning-light";
	        if($item['is_envio'] == "1"){
	            $color = "bg-info-light";
	        }
	        
	        if($item['estado'] == "ENTRANTE" && $item['is_programado'] == "1"){
				if($item['hora_retiro'] < fecha_only()){
					$color = "bg-dark-light";
				}
	        }
	        if($item['estado'] == "ENTREGADA"){
	            $color = "bg-success-light";
	        }
	        $ordenes[$key]['bg-color'] = $color;
		} 
		$return['success'] = 1;
		$return['mensaje'] = "Lista de Ordenes";
		$return['data'] = $ordenes;
	}else{
		$return['success'] = 0;
		$return['mensaje'] = "No hay órdenes tipo $tipo";
	}
	return $return;
}

function listaOrdenesMapa($input){
    global $Clordenes;
	//global $ClUsuarios;
	global $ClCouriers;
	
    $errorMsg = "";
	$datosObligatorios = array("cod_sucursal");
    if(!validate($datosObligatorios, $input, $errorMsg)){
        $return['success'] = 0;
        $return['mensaje'] = $errorMsg;
        showResponse($return);
    }
	extract($input);
	
	$range = isset($rango) ? $rango : false;

    $coordenadas = [];
	$ordenes = $Clordenes->listaForMap($cod_sucursal, $range);
	if($ordenes){
	    $jitter = 0.00003;
		foreach ($ordenes as $key => $item) {
		    $color = getColorByStatus($item['estado'], $item['is_express']);
		    $coordenadas[] = [
		        'id' => $item['id'],
		        'lat' => $item['latitud'] + $key * $jitter,
		        'lng' => $item['longitud'] + $key * $jitter,
		        'is_express' => $item['is_express'],
		        'cliente' => $item['nombre'],
		        'estado' => $item['estado'],
		        'icon' => "https://maps.google.com/mapfiles/ms/icons/".$color."-dot.png"
		    ];
		    $ordenes[$key]['color'] = $color;
		} 
		$return['success'] = 1;
		$return['mensaje'] = "Lista de Ordenes";
		$return['data'] = $ordenes;
		$return['coordenadas'] = $coordenadas;
	}else{
		$return['success'] = 0;
		$return['mensaje'] = "No hay órdenes tipo $tipo";
	}
	return $return;
}

function getColorByStatus($estado, $is_express){
    if ($is_express == 1) {
        return "red";
    }
    
    $colores = [
    "ENTRANTE" => "blue",
    "ASIGNADA" => "orange",
    "EN_CAMINO" => "yellow",
    "ENTREGADA" => "green",
  ];

  $color = $colores[$estado];
  return $color;
}

function detalleOrden($cod_orden){
	global $Clordenes;
	global $ClCouriers;
	global $ClUsuarios;
	global $ClSucursales;

	$Clordenes->percent_iva = 12;

	//OBTENER DATOS EMPRESAS
	require_once "clases/cl_empresas.php";
	$ClEmpresas = new cl_empresas();
	$empresa = $ClEmpresas->get();
	if($empresa) {
		$Clordenes->percent_iva = (float)$empresa["impuesto"];
	}

	$orden = $Clordenes->getOrden($cod_orden);
	if($orden){
		$sucursal = $ClSucursales->get($orden['cod_sucursal']);
		$Clordenes->sucursal_grava_iva = $sucursal["grava_iva"];

		$orden['latino']['fecha'] = fechaLatinoShort($orden['fecha']);
		$orden['latino']['hora'] = getHourToDateTime($orden['fecha']);
		
		$orden['retiro']['fecha'] = fechaLatinoShort($orden['hora_retiro']);
		$orden['retiro']['hora'] = getHourToDateTime($orden['hora_retiro']);

		$orden['usuario'] = $ClUsuarios->get2($orden['cod_usuario']);
		$orden['sucursal'] = $sucursal;

		$orden['detalle'] = $Clordenes->getOrdenDetalle($cod_orden);
		$orden['pagos'] = $Clordenes->getOrdenPagos($cod_orden);

		//COURIER
		$orden['courier'] = $ClCouriers->get($orden['cod_courier']);
		
		//MOTORIZADO
		$orden['motorizado'] = $Clordenes->getMotorizadoByOrden($cod_orden);
		
		//DATOS DE FACTURACION
		$orden['datos_facturacion'] = $Clordenes->getDatosFacturacion($cod_orden);
		
		//Factura
		$orden['factura'] = $Clordenes->isFactElectronica($cod_orden);
		
		//MAPA O CIUDAD
		$orden['is_map'] = 1; 
		if($orden['latitud'] == "0" && $orden['latitud'] == "0"){
		     $ciudad = $Clordenes->getCiudadDestino($cod_orden);
		     if($ciudad){
		         $orden['is_map'] = 0;
		         $orden['ciudad'] = $ciudad;
		     }
		}
		
		//TIMELINE
		$orden['timeline'] = $Clordenes->getOrdenTimeline($cod_orden);
		$a=array("estado"=>"ENTRANTE","fecha"=>$orden['latino']['fecha']." ".$orden['latino']['hora'],"descripcion"=>"Cliente creó la orden");
		array_unshift($orden['timeline'],$a);
		
		if($orden['estado'] == "ANULADA" || $orden['estado'] == "CANCELADA"){
			$orden['motivo_cancelacion'] = $Clordenes->getMotivoAnulacion($cod_orden);
		}
		if($orden['estado'] == "NO_ENTREGADA"){
			$orden['courier_cancelacion'] = $Clordenes->getCourierAnulacion($cod_orden);
		}

		$return['success'] = 1;
		$return['mensaje'] = "Correcto";
		$return['data'] = $orden;
	}else{
		$return['success'] = 0;
		$return['mensaje'] = "Orden $cod_orden no encontrada";
	}
	return $return;
}


//ASIGNAR ORDEN
function asignarOrden($input){
	global $Clordenes;
	global $ClUsuarios;
	global $ClCouriers;
	global $ClSucursales;

	$errorMsg = "";
	$datosObligatorios = array("cod_courier", "cod_orden");
	if(!validate($datosObligatorios, $input, $errorMsg)){
		$return['success'] = 0;
		$return['mensaje'] = $errorMsg;
		return $return;
	}
	extract($input);

	//VALIDACION COURIER
	$courier = $ClCouriers->get(intval($cod_courier));
	if(!$courier){
		$return['success'] = 0;
		$return['mensaje'] = "Courier no existe, por favor revisa la información";
		return $return;
	}
	$cod_courier = $courier['id'];

	//VALIDACION DE LA ORDEN
	$orden = $Clordenes->getOrden($cod_orden);
	if(!$orden){
		$return['success'] = 0;
		$return['mensaje'] = "No existe la orden $cod_orden";
		return $return;
	}
	if($orden['estado']!="ENTRANTE" && $orden['estado']!="ACEPTADA" && $orden['estado']!="NO_ENTREGADA"){
		$return['success'] = 0;
		$return['mensaje'] = "Solo se pueden asignar órdenes en estado ENTRANTE, ASIGNADA y NO ENTREGADA, esta orden se encuentra en estado ".$orden['estado'];
		return $return;
	}
	if($orden['is_envio']==0){
		$return['success'] = 0;
		$return['mensaje'] = "Las órdenes Pickup no pueden ser asignadas a Couriers, por favor revisar la información";
		return $return;
	}
	$orden['usuario'] = $ClUsuarios->get2($orden['cod_usuario']);

	// GUARDAR DATOS DE SUCURSAL
	$orden['sucursal'] = $ClSucursales->get(intval($orden['cod_sucursal']));

	//EMPEZAR A ASIGNAR
	$proveedor = "";
	$tokens = null;
	$respCourier = null;
	$asignacion = false;
	$msgError = "";
	if($cod_courier == 1){	//GACELA
		$tokens = $ClCouriers->getTokensGacela(intval($orden['cod_sucursal']));
		$asignacion = asignarGacela(json_encode($tokens), $orden, $msgError, $respCourier);
		$proveedor = "GACELA"; 
	}else if($cod_courier == 3){	//PICKER
		$tokens = $ClCouriers->getTokensPicker(intval($orden['cod_sucursal']));
		$asignacion = asignarPicker($tokens, $orden, $msgError, $respCourier);
		$proveedor = "PICKER";
	}else if($cod_courier == 2){	//LAAR
		$orden['destino'] = $Clordenes->getOrdenDestino($cod_orden);
		$tokens = $ClCouriers->getTokensLaar(intval($orden['cod_sucursal']));
		$asignacion = asignarLaar($tokens, $orden, $msgError, $respCourier);
		$proveedor = "LAAR";
	}else if($cod_courier == 4){	//INLOG
		$orden['detalle'] = $Clordenes->getOrdenDetalle($cod_orden);
		$orden['destino'] = $Clordenes->getOrdenDestino($cod_orden);
		$tokens = $ClCouriers->getTokensInlog(intval($orden['cod_sucursal']));
		$asignacion = asignarInlog($tokens, $orden, $msgError, $respCourier);
		if($asignacion){
		    ExecuteRemoteQuery(url_api."correos/orden_nueva_inlog.php?alias=".alias."&id=$cod_orden");
		}
		$proveedor = "INLOG";
	}else if($cod_courier == 5){	//PEDIDOSYA
		$tokens = $ClCouriers->getTokensPedidosYa(intval($orden['cod_sucursal']));
		$asignacion = asignarPedidosYa($tokens, $orden, $msgError, $respCourier);
		$proveedor = "PEDIDOSYA";
	}else if($cod_courier == 99){	//MIS MOTORIZADOS
		$proveedor = "MIS MOTORIZADOS";
		$asignacion = $Clordenes->asignarMotorizado($cod_orden, $motorizado_id, fecha());
		if($asignacion){
			$Clordenes->setCourier($cod_orden, "", $cod_courier);
			$Clordenes->setOrdenMotorizado($cod_orden, $motorizado_id);

			$sucursal = $ClSucursales->getBusiness($orden['cod_sucursal']);
			//NOTIFICAR
			$notificar["message"] = $sucursal["nom_empresa"] ." - ". $sucursal["nom_sucursal"] . " te ha asignado una orden";
			$notificar["id"] = "motorizados";
			$notificar["order_status"] = "ASIGNADA";
			$notificar["cod_usuario"] = $motorizado_id;
			$notificar = json_encode($notificar);
			$return["notificar_moto"] = notificarOrdenes($notificar);
		}
	}

	if($asignacion){
		$return['success'] = 1;
		$return['mensaje'] = "Orden asignada correctamente";
			
		//NOTIFICAR
		$notificar = [];
		$notificar["id"] = "usuarios";
		$notificar["order_status"] = "ASIGNADA";
		$notificar["cod_usuario"] = $orden['cod_usuario'];
		$notificar = json_encode($notificar);
		$return["notificar"] = notificarOrdenes($notificar);
	}else{
		$error = ($msgError !== "") ? $msgError : "No se pudo asignar la orden";
		$return['success'] = 0;
		$return['mensaje'] = $error;
		$Clordenes->saveOrdenError($cod_orden, "COURIER", $proveedor, $error);
	}
	
	$return['courier'] = $courier;
	$return['tokens'] = $tokens;
	$return['respCourier'] = $respCourier;
	$return['orden'] = $orden;
	return $return;
}

function asignarOrdenesMasivas($input){
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
    
	global $Clordenes;
	global $ClUsuarios;
	global $ClCouriers;
	global $ClSucursales;

	$errorMsg = "";
	$datosObligatorios = array("id_orders", "motorizado_id");
	if(!validate($datosObligatorios, $input, $errorMsg)){
		$return['success'] = 0;
		$return['mensaje'] = $errorMsg;
		return $return;
	}
	extract($input);
	
	$motorizado = $Clordenes->getMotorizado($motorizado_id);
	if(!$motorizado){
	    return [ 'success' => 0, 'mensaje' => 'Motorizado no encontrado' ];
	}

	$ordenes_validas = $Clordenes->getOrdenesMasivas($id_orders);
	if (count($ordenes_validas) === 0) {
		$return['success'] = 0;
		$return['mensaje'] = "No hay ordenes por asignar o no son validas";
		return $return;
	}
    
    $id_validos = array_column($ordenes_validas, 'cod_orden');
    // 1. Actualizar estado y courier
    $Clordenes->actualizarEstadoOrdenes($id_validos);
    
    // 2. Asignar motorizado y actualizar info
    $Clordenes->asignarMotorizadoYActualizarOrdenes($ordenes_validas, $motorizado);
	
	    
	$return['success'] = 1;
	$return['mensaje'] = "Orden asignada correctamente";
	$return['ids_validos'] = $ordenes_validas;
	return $return;
}

function asignarFlota($input){
	global $Clordenes;
	global $ClUsuarios;
	global $ClCouriers;
	global $ClSucursales;

	$errorMsg = "";
	$datosObligatorios = array("cod_flota", "cod_orden");
	if(!validate($datosObligatorios, $input, $errorMsg)){
		$return['success'] = 0;
		$return['mensaje'] = $errorMsg;
		return $return;
	}
	extract($input);

	//VALIDACION COURIER
	$cod_courier = 101;

	//VALIDACION DE LA ORDEN
	$orden = $Clordenes->getOrden($cod_orden);
	if(!$orden){
		$return['success'] = 0;
		$return['mensaje'] = "No existe la orden $cod_orden";
		return $return;
	}
	if($orden['estado']!="ENTRANTE" && $orden['estado']!="ACEPTADA" && $orden['estado']!="NO_ENTREGADA"){
		$return['success'] = 0;
		$return['mensaje'] = "Solo se pueden asignar órdenes en estado ENTRANTE, ASIGNADA y NO ENTREGADA, esta orden se encuentra en estado ".$orden['estado'];
		return $return;
	}
	if($orden['is_envio']==0){
		$return['success'] = 0;
		$return['mensaje'] = "Las órdenes Pickup no pueden ser asignadas a Couriers, por favor revisar la información";
		return $return;
	}
	$orden['usuario'] = $ClUsuarios->get2($orden['cod_usuario']);
	
	
	if(!$Clordenes->setFlota($cod_orden, $cod_flota))
	    return [ 'success' => 0, 'mensaje' => 'No se pudo asignar la flota' ];
	
	if(!$Clordenes->setCourier($cod_orden, '', 101))
	    return [ 'success' => 0, 'mensaje' => 'No se pudo asignar el courier' ];
	
	$texto_adicional = "";
	$business_link = [ 163, 164, 171, 70 ];
	if(in_array(cod_empresa, $business_link)){
    	$token = passRandom();
    	$link = $Clordenes->setLinkToken($cod_orden, $token, '');
    	if($link){
    	    $linkOrder = "https://pedidos.demo.mie-commerce.com/pedidos/?id=$token";
    	    $texto_adicional = "El link de la orden es ".$linkOrder;
    	}
	}    
	    
	require_once "helpers/notificationUser.php";
	notifyAsignacionFlota($cod_orden, $cod_flota, $texto_adicional);
	    
	$return['success'] = 1;
	$return['mensaje'] = "Orden asignada correctamente";
	$return['orden'] = $orden;
	return $return;
}

function asignarGacela($tokens_, $orden, &$msgError="", &$respCourier=null) {
	try {
		// * 24 nov 2023
		global $Clordenes;

		$tokens = json_decode($tokens_, true);

		if(!$tokens) {
			$msgError = "No está configurado Gacela para esta sucursal, por favor revisar";
			return false;
		}

		require_once "clases/cl_gacela.php";
		$ClGacela = new cl_gacela($tokens_);
		$respCourier = (object)$ClGacela->crearOrder($orden);
		
		if(!$respCourier) {
			$msgError = "No se pudo conectar con Gacela, por favor intente nuevamente";
			return false;
		}

		if($respCourier->status != "200") {
			$adicional = $respCourier->message ? $respCourier->message : "";
			$msgError = "Error al crear la orden en Gacela " . $adicional;
			return false;
		}

		if($respCourier->data) {
			$dataGacela = (object)$respCourier->data;
			$Clordenes->setCourier($orden['id'], $dataGacela->job_token, 1);
			$msgError = $dataGacela->status;
			return true;
		}
		else {
			$adicional = isset($dataGacela->message) ? $dataGacela->message : "";
			$msgError = "Error al crear la orden en Gacela " . $adicional;
			return false;
		}
	} 
	catch (\ErrorException $e) {
		$adicional = $e->getMessage();
		$msgError = "Error catch. " . $adicional;
		return false;
	}
}

function asignarPicker($tokens, $orden, &$msgError="", &$respCourier=null){
	global $Clordenes;
	if(!$tokens){
		$msgError = "No está configurado Picker para esta sucursal, por favor revisar";
		return false;
	}

	require_once "clases/cl_picker.php";
	$ClPicker = new cl_picker($tokens['api'],$tokens['ambiente']);
	$data = $ClPicker->crearOrder($orden);
	$respCourier = $data;
	if(isset($data->statusCode)){
		if(intval($data->statusCode) == 200){
			$idPicker = $data->data->_id;
			//$idPicker = $data->data->bookingNumericId;
			$Clordenes->setCourier($orden['id'], $idPicker, 3);
			$msgError = "";
			return true;
		}else{
			$adicional = isset($data->message) ? $data->message : "";
			$adicional = str_replace('\'', '', $adicional);
			$msgError = "Error al crear la orden en Picker ".$adicional;
			return false;
		}
	}else{
		$msgError = "Error al crear la orden en Picker por favor vuelva a intentarlo";
		return false;
	}
}

function asignarLaar($tokens, $orden, &$msgError="", &$respCourier=null){
	global $Clordenes;
	if(!$tokens){
		$msgError = "No está configurado Laar para esta sucursal, por favor revisar";
		return false;
	}

	if(!$orden['destino']){
		$msgError = "La orden no tiene configurado un lugar de Destino, por favor revisar la orden";
		return false;
	}

	require_once "clases/cl_laar.php";
	$ClLaar = new cl_laar($tokens['token'],$tokens['username'],$tokens['password']);
	$data = $ClLaar->crearGuia($orden);
	$respCourier = $data;
	if(isset($data->guia)){
		$Clordenes->setCourier($orden['id'], $data->guia, 2);
		$msgError = "";
		return true;
	}else{
		$adicional = isset($data->Message) ? $data->Message : "";
		$msgError = "Error al crear la orden en Laar ".$adicional;
		return false;
	}
}

function asignarInlog($tokens, $orden, &$msgError="", &$respCourier=null){
	global $Clordenes;
	if(!$tokens){
		$msgError = "No está configurado Inlog para esta sucursal, por favor revisar";
		return false;
	}

	if(!$orden['destino']){
		$msgError = "La orden no tiene configurado un lugar de Destino, por favor revisar la orden";
		return false;
	}

	require_once "clases/cl_inlog.php";
	$ClInlog = new cl_inlog($tokens['token'],$tokens['idCliente']);
	$data = $ClInlog->crearGuia($orden);
	$respCourier = $data;
	if(isset($data->sts)){
		if($data->sts == 1){
			$Clordenes->setCourier($orden['id'], $data->insert_id, 4);
			$msgError = "";
			return true;
		}else{
			$adicional = isset($data->msg) ? $data->msg : "";
			$msgError = "Error al crear la orden en Inlog: ".$adicional;
			return false;
		}
	}else{
		$adicional = isset($ClInlog->msgError) ? $ClInlog->msgError : "";
		$msgError = "Error al crear la orden en Inlog ".$adicional;
		return false;
	}
}

function asignarPedidosYa($tokens, $orden, &$msgError="", &$respCourier=null){
	global $Clordenes;
	global $ClSucursales;

	if(!$tokens){
		$msgError = "No está configurado PedidosYa para esta sucursal, por favor revisar";
		return false;
	}

	$sucursal = $ClSucursales->get($orden["cod_sucursal"]);
	if(!$sucursal) {
		$msgError = "Sucursal no encontrada";
		return false;
	}
	$sucursal["ciudad"] = "Guayaquil";
	$ciudad = $ClSucursales->getInfoByCiudad($sucursal["cod_ciudad"]);
	if($ciudad)
		$sucursal["ciudad"] = $ciudad["nombre"];

	require_once "clases/cl_pedidosya.php";
	$ClPedidosYa = new cl_pedidosya($tokens['token'], $tokens['ambiente']);

	$orden["peso"] = 0;
	$orden["volumen"] = 0;
	$ordenDetalle = $Clordenes->getOrdenDetalle($orden["id"]);
	if($ordenDetalle) {
		$ClProductos = new cl_productos();
		foreach ($ordenDetalle as $ordDet) {
			$producto = $ClProductos->get($ordDet["product_id"]);
			if($producto) {
				$orden["peso"] = $orden["peso"] + ($producto["peso"] * $ordDet["cantidad"]);
				$orden["volumen"] = $orden["volumen"] + ($producto["volumen"] * $ordDet["cantidad"]);
			}
		}
	}

	$orden["instructionDelivery"] = "Ya se realizó el cobro de este pedido";
	$orden["collectMoney"] = 0;
	if($tokens["recolectar_dinero"] == 1) {
		$formasPago = $Clordenes->getOrdenPagos($orden["id"]);
		if($formasPago) {
			foreach ($formasPago as $fp) {
				if($fp["forma_pago"] == "E") {
					$ClPedidosYa->collectMoney = true;
					$orden["instructionDelivery"] = "Se debe cobrar en efectivo " . number_format($fp["monto"], 2);
					$orden["collectMoney"] = number_format($fp["monto"], 2);
				}
			}
		}
	}
		
	$data = $ClPedidosYa->createOrder($sucursal, $orden);
	$respCourier = $data;
	if(isset($data["status"])) {
		if(is_numeric($data["status"])) {
			$adicional = isset($data["message"]) ? $data["message"] : "";
			$msgError = "Error al crear la orden en PedidosYa ".$adicional;
			return false;
		}else{
			$Clordenes->setCourier($orden['id'], $data["shippingId"], 5);
			$msgError = $data["status"];
			return true;
		}
	}
	else {
		$msgError = "Error desconocido";
		return false;
	}
}

//Buscar conductor
function findDriverFromApi($courier_id, $delivery_token, $order_id, $office_id){
	global $Clordenes;
	global $ClCouriers;

	if($courier_id == 1){ //GACELA
		$tokens = $ClCouriers->getTokensGacela(intval($office_id));
		if($tokens){
			require_once "clases/cl_gacela.php";
			$ClGacela = new cl_gacela($tokens['api'],$tokens['token'],$tokens['ambiente']);
			$tracking = $ClGacela->trackingOrder($delivery_token);

			
			// GUARDAR LOGS
            $file = "logs/logFindDriverGacela.log";
            $fecha = fecha();
            $log = "[$fecha] - [$delivery_token]" . json_encode($tracking);
            file_put_contents($file, PHP_EOL . $log, FILE_APPEND);


			if(isset($tracking->driver)){
				extract($tracking->driver);
				if($Clordenes->setMotorizadoToOrder($order_id, $name, $lastname, $document, $plate, $photo, $phone)){
					return $Clordenes->getMotorizadoByOrden($order_id);
				}

			}
		}
	}
	if($courier_id == 5){ //PEDIDOS YA
		$tokens = $ClCouriers->getTokensPedidosYa(intval($office_id));
		if($tokens){
			require_once "clases/cl_pedidosya.php";
			$ClPedidosYa = new cl_pedidosya($tokens['token'], $tokens['ambiente']);
			$tracking = $ClPedidosYa->tranckingOrder($delivery_token);
			if(!isset($tracking['status'])){
				$photo = "https://dashboard.mie-commerce.com/assets/img/pedidosya.png";
				if($Clordenes->setMotorizadoToOrder($order_id, $tracking['deliveryName'], "", "", "", $photo, "0999999999")){
					return $Clordenes->getMotorizadoByOrden($order_id);
				}
			}
		}
	}
	return false;
}

//CAMBIAR ESTADO
function setEstado($input){
	global $Clordenes;

	$errorMsg = "";
	$datosObligatorios = array("estado", "cod_orden");
	if(!validate($datosObligatorios, $input, $errorMsg)){
		$return['success'] = 0;
		$return['mensaje'] = $errorMsg;
		return $return;
	}
	extract($input);

	//VALIDACION ESTADO
	$estadosDisponibles = array("PREPARANDO","ACEPTADA","ENVIANDO","ENTREGADA","NO_ENTREGADA");
	if(!in_array($estado, $estadosDisponibles)){
		$return['success'] = 0;
		$return['mensaje'] = "El estado proporcionado no es un estado válido, por favor revisar ";
		return $return;
	}

	//VALIDACION DE LA ORDEN
	$orden = $Clordenes->getOrden($cod_orden);
	if(!$orden){
		$return['success'] = 0;
		$return['mensaje'] = "No existe la orden $cod_orden";
		return $return;
	}
	if($orden['estado']=="ANULADA"){
		$return['success'] = 0;
		$return['mensaje'] = "Esta orden se encuentra anulada, no se puede cambiar de estado!";
		return $return;
	}
	if($orden['is_envio']==0 && $estado=="ENVIANDO"){
		$return['success'] = 0;
		$return['mensaje'] = "Las órdenes Pickup no pueden ser cambiadas a estado ENVIANDO por favor revisar";
		return $return;
	}
	if($orden['is_envio']==1 && $estado=="PREPARANDO"){
		$return['success'] = 0;
		$return['mensaje'] = "Las órdenes Delivery no pueden ser cambiadas a estado PREPARANDO por favor revisar";
		return $return;
	}

	if($estado == "ANULADA"){
		$userAction = 1;
		$comentarios = (isset($input['motivo'])) ? $input['motivo'] : "";
		$resp = $Clordenes->AnularFactura($cod_orden, $comentarios, $userAction);
	}else{
		$resp = $Clordenes->setEstado($cod_orden, $estado);
	}

	$MinusEstado = strtolower($estado);
	if($resp){
		$return['success'] = 1;
		$return['mensaje'] = "Orden cambiada a $MinusEstado correctamente";
		$return['newStatus'] = $estado;
		$return['is_envio'] = (int)$orden["is_envio"];
		if($estado == "ENTREGADA"){
		    //204 es 400Grados
            if(cod_empresa == 70 || cod_empresa == 204){
                require_once "helpers/notificationsToClient.php";
                $return['ultramsg'] = sendMessageWhatsappVideo($orden);
                $return['video'] = "Se intento enviar";
            }
		}
	}else{
		$return['success'] = 0;
		$return['mensaje'] = "No se pudo cambiar de estado la orden a ".$MinusEstado;
	}
	$return['orden'] = $orden;
	return $return;
}

function cancelarOrden($input){ //TODO: Terminar Cancelar Orden
	global $Clordenes;
	global $ClCouriers;

	$errorMsg = "";
	$datosObligatorios = array("estado", "cod_orden");
	if(!validate($datosObligatorios, $input, $errorMsg)){
		$return['success'] = 0;
		$return['mensaje'] = $errorMsg;
		return $return;
	}
	extract($input);

	//VALIDACION DE LA ORDEN
	$orden = $Clordenes->getOrden($cod_orden);
	if(!$orden){
		$return['success'] = 0;
		$return['mensaje'] = "No existe la orden $cod_orden";
		return $return;
	}
	if($orden['estado']=="ANULADA"){
		$return['success'] = 0;
		$return['mensaje'] = "Esta orden se encuentra anulada, no se puede cambiar de estado!";
		return $return;
	}

	if($input['motivo']==""){//VALIDAR MOTIVO VACÍO
		$return['success'] = 0;
		$return['mensaje'] = "Por favor ingrese un motivo de cancelación";
		return $return;
	}
	$motivo = $input['motivo'];
	$casher_id = isset($input['casher_id']) ? $input['casher_id'] : 1;

	$extraMessage = "";

	$courier = $orden['cod_courier'];
	if($courier == 1){	//GACELA
		$extraMessage = " No se puede cancelar la asignación de la orden en Gacela desde esta plataforma, por favor contacte al Call Center de Gacela.";

		/*
			? ESPERAR A GACELA QUE HABILITE EL ENDPOINT DE CANCELACION

		$tokens = $ClCouriers->getTokensGacela(intval($orden['cod_sucursal']));
		require_once "clases/cl_gacela.php";
		$ClGacela = new cl_gacela($tokens['api'],$tokens['token'],$tokens['ambiente']);
		$respInfo = null;
		$data = $ClGacela->cancelarOrder($orden['order_token'],$respInfo);
		  
		if($respInfo['http_code'] !== 200){
			$return['success'] = 0;
			$return['mensaje'] = $data->status;
			return $return;
		} 
		*/
	}
	else if($courier == 5){	//PEDIDOSYA
		$tokens = $ClCouriers->getTokensPedidosYa(intval($orden['cod_sucursal']));
		require_once "clases/cl_pedidosya.php";
		$ClPedidosYa = new cl_pedidosya($tokens['token'], $tokens['ambiente']);
		$data = $ClPedidosYa->cancelOrder($orden['order_token'], $motivo);
		if(isset($data['status'])){
			if(is_numeric($data)) {
				$return['success'] = 0;
				$return['mensaje'] = $data["message"];
				return $return;
			}
		}
	}
	else if($courier == 3){	//PICKER
		$tokens = $ClCouriers->getTokensPicker(intval($orden['cod_sucursal']));
		require_once "clases/cl_picker.php";
		$ClPicker = new cl_picker($tokens['api'],$tokens['ambiente']);
		$data = $ClPicker->cancelOrder($orden['order_token'], "NOT_NEEDED");
		if(isset($data->statusCode)){
    		if(intval($data->statusCode) !== 200){
    		    $adicional = isset($data->message) ? $data->message : "";
    			$return['success'] = 0;
			    $return['mensaje'] = "Error al anular la orden en Picker por favor vuelva a intentarlo ".$adicional;
			    return $return;
    		}
    	}else{
    		$return['success'] = 0;
			$return['mensaje'] = "Error al anular la orden en Picker por favor vuelva a intentarlo";
			return $return;
    	}
	}

	$resp = $Clordenes->AnularOrden($cod_orden, $motivo, $casher_id);

	$MinusEstado = strtolower($estado);
	if($resp){
		$return['success'] = 1;
		$return['mensaje'] = "Orden anulada correctamente" . $extraMessage;
		$return['newStatus'] = $estado;
		$return['anularPago'] = $Clordenes->isPagoTarjeta($cod_orden);
		$return['anularFactura'] = $Clordenes->isFactElectronica($cod_orden);
	}else{
		$return['success'] = 0;
		$return['mensaje'] = "No se pudo cambiar de estado la orden a ".$MinusEstado;
	}
	$return['orden'] = $orden;
	return $return;
}

function cancelarAsignacion($input){
	global $Clordenes;
	global $ClCouriers;

	$errorMsg = "";
	$datosObligatorios = array("cod_orden");
	if(!validate($datosObligatorios, $input, $errorMsg)){
		$return['success'] = 0;
		$return['mensaje'] = $errorMsg;
		return $return;
	}
	extract($input);

	//VALIDACION DE LA ORDEN
	$orden = $Clordenes->getOrden($cod_orden);
	if(!$orden){
		$return['success'] = 0;
		$return['mensaje'] = "No existe la orden $cod_orden";
		return $return;
	}

	//VALIDACION ESTADO
	$estado = $orden['estado'];
	$MinusEstado = strtolower($estado);
	$estadosDisponibles = array("ASIGNADA","ENVIANDO","NO_ENTREGADA");
	if(!in_array($estado, $estadosDisponibles)){
		$return['success'] = 0;
		$return['mensaje'] = "No se puede cancelar una asignación si está en estado $MinusEstado";
		return $return;
	}

	$courier = $orden['cod_courier'];

	$extraMessage = "";

	if($courier == 1){	//GACELA
		$extraMessage = " No se puede cancelar la asignación de la orden en Gacela desde esta plataforma, por favor contacte al Call Center de Gacela.";
		/* 
			? ESPERAR A GACELA QUE HABILITE EL ENDPOINT DE CANCELACION

		$tokens = $ClCouriers->getTokensGacela(intval($orden['cod_sucursal']));
		require_once "clases/cl_gacela.php";
		$ClGacela = new cl_gacela($tokens['api'],$tokens['token'],$tokens['ambiente']);
		$respInfo = null;
		$data = $ClGacela->cancelarOrder($orden['order_token'],$respInfo);
		if($respInfo['http_code'] !== 200){
			$return['success'] = 0;
			$return['mensaje'] = $data->status;
			return $return;
		}
		*/
	}
	else if($courier == 5){	//PEDIDOSYA
		$tokens = $ClCouriers->getTokensPedidosYa(intval($orden['cod_sucursal']));
		require_once "clases/cl_pedidosya.php";
		$ClPedidosYa = new cl_pedidosya($tokens['token'], $tokens['ambiente']);
		$data = $ClPedidosYa->cancelOrder($orden['order_token'], "Asignación cancelada por el admin");
		if(isset($data['status'])){
			if(is_numeric($data['status'])) {
				$return['success'] = 0;
				$return['mensaje'] = $data["message"];
				return $return;
			}
		}
	}
	else if($courier == 3){	//PICKER
		$tokens = $ClCouriers->getTokensPicker(intval($orden['cod_sucursal']));
		require_once "clases/cl_picker.php";
		$ClPicker = new cl_picker($tokens['api'],$tokens['ambiente']);
		$data = $ClPicker->cancelOrder($orden['order_token'], "TAKING_TOO_LONG");
		if(isset($data->statusCode)){
    		if(intval($data->statusCode) !== 200){
    		    $adicional = isset($data->message) ? $data->message : "";
    			$return['success'] = 0;
			    $return['mensaje'] = "Error al anular la orden en Picker por favor vuelva a intentarlo ".$adicional;
			    return $return;
    		}
    	}else{
    		$return['success'] = 0;
			$return['mensaje'] = "Error al anular la orden en Picker por favor vuelva a intentarlo";
			return $return;
    	}
	}
	else if($courier == 100){ //LINK PARA ENVIAR A WHATSAPP
	    $Clordenes->destroyLinkToken($cod_orden);
	}
	else if($courier == 101){ //LINK PARA FLOTAS
	    $Clordenes->destroyLinkToken($cod_orden);
	    $Clordenes->removeFlotaOrden($cod_orden);
	}
	//En el caso de ser mis motorizados debería notificar al motorizado

    $Clordenes->deleteMotorizadoAsignacion($cod_orden);
	$resp = $Clordenes->cancelarAsignacion($cod_orden);
	if($resp){
		$Clordenes->saveLastOrderToken($cod_orden, $orden['order_token']);
		$return['success'] = 1;
		$return['mensaje'] = "Courier cancelado correctamente" . $extraMessage;
	}else{
		$return['success'] = 0;
		$return['mensaje'] = "No se pudo cancelar la asignación al courier, por favor vuelva a intentarlo";
	}
	return $return;
}

//REVERTIR PAGO
function revertirPago($input){
    global $Clordenes;

	$errorMsg = "";
	$datosObligatorios = array("cod_orden");
	if(!validate($datosObligatorios, $input, $errorMsg)){
		$return['success'] = 0;
		$return['mensaje'] = $errorMsg;
		return $return;
	}
	extract($input);
	
	//VALIDACION DE LA ORDEN
	$orden = $Clordenes->getOrden($cod_orden);
	if(!$orden){
		$return['success'] = 0;
		$return['mensaje'] = "No existe la orden $cod_orden";
		return $return;
	}
	
	$pagoTarjeta = $Clordenes->isPagoTarjeta($cod_orden);
	if($pagoTarjeta){
	    $identificadorPago = $pagoTarjeta['observacion'];
	    $monto = number_format($pagoTarjeta['monto'],2);
	    
	   // $pagoTarjeta['cod_proveedor_botonpagos'] = 2;
	    
	    if($pagoTarjeta['cod_proveedor_botonpagos'] == 1){ //DATAFAST
	        require_once "clases/cl_datafast.php";
	        $Cldatafast = new cl_datafast($orden['cod_sucursal']);
	        $errorMsg = "";
	        $refund = [];
            if($Cldatafast->refund($identificadorPago, $monto, $errorMsg, $refund)){
                $return['success'] = 1;
                $return['mensaje'] = 'Devolución de Dinero Exitosa';
            }else{
                $return['success'] = 0;
                $return['mensaje'] = "Error al revertir. Detalles: ".$errorMsg;
            }
            $return['responseDatafast'] = $refund;
	    }
		else if($pagoTarjeta['cod_proveedor_botonpagos'] == 2){                              //PAYMENTEZ
	        require_once "clases/cl_paymentez.php";
	        $ClPaymentez = new cl_paymentez(cod_empresa, $orden['cod_sucursal']);
	        $errorMsg = "";
	        $refund = [];
            if($ClPaymentez->refund($identificadorPago, $errorMsg, $refund)){
                $return['success'] = 1;
                $return['mensaje'] = 'Devolución de Dinero Exitosa';
            }else{
                $return['success'] = 0;
                if($errorMsg == "Invalid Status")
                    $errorMsg = "puede ser porque ya se anulo anteriormente o porque se excedio del tiempo de anulacion. Revisar la plataforma de Paymentez";
                $return['mensaje'] = "Error al revertir. ".$errorMsg;
            }
            $return['responsePaymentez'] = $refund;
	        
	    }
		else if($pagoTarjeta['cod_proveedor_botonpagos'] == 3){ //PAYPHONE
			require_once "clases/cl_payphone.php";
			$Clpayphone = new cl_payphone(cod_empresa);
			$errorMsg = "";
			$refund = $Clpayphone->refund($identificadorPago);
			if($refund){
                $return['success'] = 1;
                $return['mensaje'] = 'Devolución de Dinero Exitosa';
            }else{
                $return['success'] = 0;
                $return['mensaje'] = "Error al revertir. Detalles: ".$refund["respuesta_payphone"]["message"];
            }
		}
	}else{
	    $return['success'] = 0;
        $return['mensaje'] = "No hay pagos con tarjeta";
	}
	return $return;
}

function historialOrdenes($cod_sucursal){
	global $Clordenes;
	
	$historial = $Clordenes->getHistoryOrders($cod_sucursal);
	if($historial){
		foreach($historial as &$hist){
			switch($hist["estado"]){
				case "PUNTO_RECOGIDA":
					$hist["estado_mensaje"] = "Llegué a la tienda";
					break;
				case "ENVIANDO":
					$hist["estado_mensaje"] = "En camino a entregar el pedido";
					break;
				case "PUNTO_ENTREGA":
					$hist["estado_mensaje"] = "Llegué al lugar de entrega";
					break;
				case "ENTREGADA":
					$hist["estado_mensaje"] = "Entregué correctamente el paquete";
					break;
			}
		}
		$return['success'] = 1;
		$return['mensaje'] = "Historial de órdenes";
		$return['data'] = $historial;
	} else {
		$return['success'] = 0;
		$return['mensaje'] = "No hay historial de órdenes";
	}
	return $return;
}

function ordenesRezagadas($cod_sucursal){
	global $Clordenes;

	$ordenes = $Clordenes->getOrdenesRezagadas($cod_sucursal);
	if($ordenes){
		$cantidad = $ordenes['cantidad'];
		if($cantidad > 0){
			$total = number_format($ordenes['total'],2);
			$return['success'] = 1;
			$return['mensaje'] = "Tienes $cantidad órdenes pendientes de asignación, Total de $".$total;
			$return['cantidad'] = intval($cantidad);
			$return['ordenes'] = $Clordenes->getOrdenesRezagadasArr($cod_sucursal);
			return $return;
		}
	}

	$return['success'] = 0;
	$return['mensaje'] = "No hay ordenes pendientes";
	return $return;
}

//RECIPIENTES
function getRecipientes($cod_orden){
	global $Clordenes;
	global $ClSucursales;
	global $ClUsuarios;

	//VALIDACION DE LA ORDEN
	$orden = $Clordenes->getOrden($cod_orden);
	if(!$orden){
		$return['success'] = 0;
		$return['mensaje'] = "No existe la orden $cod_orden";
		return $return;
	}
	
	$recipientes = $Clordenes->getRecipientes($cod_orden, cod_empresa);
	if($recipientes){
		$return['success'] = 1;
		$return['mensaje'] = "Recipientes escogidos";
		$return['orden'] = $orden;
		$return['recipientes'] = $recipientes;
	}else{
		$return['success'] = 0;
		$return['mensaje'] = "No hay recipientes creados";
		$return['sucursal'] = $sucursal;
	}
	return $return;
}

//VALIDACION MIS MOTORIZADOS
function getMisMotorizados($cod_orden){
	global $Clordenes;
	global $ClSucursales;
	global $ClUsuarios;

	//VALIDACION DE LA ORDEN
	$orden = $Clordenes->getOrden($cod_orden);
	if(!$orden){
		$return['success'] = 0;
		$return['mensaje'] = "No existe la orden $cod_orden";
		return $return;
	}

	$sucursal = $ClSucursales->get($orden['cod_sucursal']);
	$motorizados = $ClUsuarios->getMotorizadosCercanos($sucursal['latitud'], $sucursal['longitud'], $orden['cod_sucursal'], 100);
	if($motorizados){
		$orden['sucursal'] = $sucursal;

		$return['success'] = 1;
		$return['mensaje'] = "Motorizados cercanos";
		$return['motorizados'] = $motorizados;
		$return['orden'] = $orden;
	}else{
		$return['success'] = 0;
		$return['mensaje'] = "No hay motorizados cercanos o disponibles";
		$return['sucursal'] = $sucursal;
	}
	return $return;
}

function notificarOrdenes($data){
	$data = json_decode($data, true);
	$id = $data["id"];
	$order_status = $data["order_status"];
	$cod_usuario = $data["cod_usuario"];
	$message = "";
	
	$url_api = url_api . 'notificar';
	$topic = "usuario" . $cod_usuario;
	$type = "NOTIFICACION";
    if($id == "motorizados"){
		$url_api = url_api_motorizados . 'usuarios/notificar' ;
		$topic = "motorizado" . $cod_usuario;
		$order_status = "ASIGNADA_MOTORIZADOS";
		$type = "PEDIDOS";
		if(isset($data["message"])){
			$message = $data["message"];
		}
	}

	$textOrderStatus = getTextOrderStatus($order_status);
	$title = $textOrderStatus["titulo"];
	if("message" == "")
		$message = $textOrderStatus["descripcion"];

    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => $url_api,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => '{
            "topic": "'.$topic.'",
            "title": "'.$title.'",
            "message": "'.$message.'",
            "type": "'.$type.'"
        }',
        CURLOPT_HTTPHEADER => array(
            "Api-Key: " . api_key,
            "Content-Type: application/json",
            "Cookie: PHPSESSID=bf1fb6b5b19e11c3cc408853609c121b"
        ),
    ));

    $response["curl_response"] = curl_exec($curl); 
    // $response["data_sent"] = $data;
	
    curl_close($curl);

	/* $data["url"] = $url_api;
	$data["title"] = $title;
	$data["message"] = $message;
	$data["type"] = $type;
	$data["topic2"] = $topic;

    $response["data_sent"] = $data; */
    return $response;
}

function getTextOrderStatus($order_status){
	switch ($order_status) {
		case 'ASIGNADA':
			$titulo = "Tu pedido fue asignado a un motorizado";
			$descripcion = "Se te ha asignado la orden.";       
			break;
		case 'ASIGNADA_MOTORIZADOS':
			$titulo = "Se te ha asignado una orden";
			$descripcion = "Se te ha asignado una orden";       
			break;
		case 'ENVIANDO':
			$titulo = "Tu pedido está en camino";
			$descripcion = "La persona encargada ha salido con tu pedido hacia la dirección que nos indicaste, pronto esta persona te llamará para proceder a realizar la entrega.";       
			break;
		case 'ENTREGADA':
			$titulo = "Tu pedido ha sido entregado";
			$descripcion = "Disfruta de tu pedido!!, si tienes algún comentario, mejora sobre nuestro servicio puedes proceder a calificarnos.";       
			break;
		case 'PREPARANDO':
			$titulo = "Tu pedido se está preparando";
			$descripcion = "Ya estamos preparando tu pedido, no olvides que debes venir a recogerlo.";       
			break;
		case 'CANCELADA';    
		case 'ANULADA':
			$titulo = "Tu orden ha sido ".strtolower($order_status);
			$descripcion = $titulo;       
			break;    
	}
	$return["titulo"] = $titulo;
	$return["descripcion"] = $descripcion;

	return $return;
}

function ordenesSinFinalizar($cod_sucursal){
	global $Clordenes;
	global $ClCouriers;
	
	$ordenes = $Clordenes->listaOrdenesAntiguas($cod_sucursal);
	if($ordenes){
		foreach ($ordenes as $key => $item) {
			$ordenes[$key]['creada']['fecha'] = fechaLatinoShort($item['fecha']);
			$ordenes[$key]['creada']['hora'] = getHourToDateTime($item['fecha']);
			$ordenes[$key]['creada']['agoText'] = 
				hoursAgoDate($item['fecha'],$ordenes[$key]['creada']['agoDias'],$ordenes[$key]['creada']['agoHoras'],$ordenes[$key]['creada']['agoMinutos']);
				
		    //Retiro
			$ordenes[$key]['retiro']['fecha'] = fechaLatinoShort($item['hora_retiro']);
			$ordenes[$key]['retiro']['hora'] = getHourToDateTime($item['hora_retiro']);
			
			$ordenes[$key]['timeline'] = $Clordenes->getOrdenTimeline($item['id']);
			//COURIER
	        $ordenes[$key]['courier'] = $ClCouriers->get($item['cod_courier']);
			//MOTORIZADO
	        $ordenes[$key]['motorizado'] = $Clordenes->getMotorizadoByOrden($item['id']);
	        
	        //COLOR
	        $color = "bg-warning-light";
	        if($item['is_envio'] == "1"){
	            $color = "bg-info-light";
	        }
	        
	        if($item['estado'] == "ENTRANTE" && $item['is_programado'] == "1"){
				if($item['hora_retiro'] < fecha_only()){
					$color = "bg-dark-light";
				}
	        }
	        if($item['estado'] == "ENTREGADA"){
	            $color = "bg-success-light";
	        }
	        $ordenes[$key]['bg-color'] = $color;
		} 
		$return["success"] = 1;
		$return["mensaje"] = "Lista de órdenes antiguas";
		$return["data"] = $ordenes;
	}
	else{
		$return["success"] = 0;
		$return["mensaje"] = "No hay órdenes antiguas";
	}
	return $return;
}

function finalizarAntiguas($cod_sucursal){
	global $Clordenes;

	$ordenes = $Clordenes->listaOrdenesAntiguas($cod_sucursal);
	if($ordenes){
		foreach ($ordenes as $orden) {
			$Clordenes->setEstado($orden["id"], 'ENTREGADA');		
		} 
		$return["success"] = 1;
		$return["mensaje"] = "Lista de órdenes antiguas cambiadas a entregadas";
	}
	else{
		$return["success"] = 0;
		$return["mensaje"] = "No hay órdenes antiguas";
	}
	return $return;
}

//LINK PARA MOTORIZADOS
function generarLink($input){
	global $Clordenes;
	global $ClUsuarios;
	global $ClCouriers;
	global $ClSucursales;

	$errorMsg = "";
	$datosObligatorios = array("cod_orden", "phone");
	if(!validate($datosObligatorios, $input, $errorMsg)){
		$return['success'] = 0;
		$return['mensaje'] = $errorMsg;
		return $return;
	}
	extract($input);

	//VALIDACION DE LA ORDEN
	$orden = $Clordenes->getOrden($cod_orden);
	if(!$orden){
		$return['success'] = 0;
		$return['mensaje'] = "No existe la orden $cod_orden";
		return $return;
	}
	if($orden['estado']!="ENTRANTE" && $orden['estado']!="ACEPTADA"){
		$return['success'] = 0;
		$return['mensaje'] = "Solo se pueden asignar órdenes en estado ENTRANTE o ASIGNADA, esta orden se encuentra en estado ".$orden['estado'];
		return $return;
	}
	if($orden['is_envio']==0){
		$return['success'] = 0;
		$return['mensaje'] = "Las órdenes Pickup no pueden ser asignadas a Couriers, por favor revisar la información";
		return $return;
	}
	$orden['usuario'] = $ClUsuarios->get2($orden['cod_usuario']);
	
	if(strlen($phone) !== 12){
	    $return['success'] = 0;
		$return['mensaje'] = "El teléfono debe tener 12 dígitos, los 3 primeros deben ser el codigo de pais (Ecuador: 593)";
		return $return;
	}

	//CREAR TOKEN
	$token = passRandom();
	$link = $Clordenes->setLinkToken($cod_orden, $token, $phone);
	if($link){
	    
	   // //Mensaje por whatsapp - Start
    // 	require_once "clases/cl_ultramsg.php";
    //     $ClMessages = new cl_ultramsg();
        
    //     $linkOrder = "https://pedidos.demo.mie-commerce.com/pedidos/?id=$token";
    //     $texto = 'Se ta ha asignado una orden desde '.name_site.', abre el enlace para aceptar el pedido 💪, para que puedas acceder más rapido a los enlaces, registra este número 🛵️ ';
    //     $message = $ClMessages->sendMessage($phone, $texto);
    //     $message = $ClMessages->sendMessage($phone, $linkOrder);
    //     $sent = isset($message['sent']) ? $message['sent'] : false;
    //     //Mensaje de whatsapp - Fin
	    
	    $Clordenes->setCourier($cod_orden, "", 100); //Cambia la orden a asignada
		$return['success'] = 1;
		$return['mensaje'] = "Link generado correctamente";
		$return['token'] = $token;
		$return['phone'] = $phone;
	}else{
		$return['success'] = 0;
		$return['mensaje'] = ($msgError !== "") ? $msgError : "No se pudo asignar la orden";
	}
	$return['orden'] = $orden;
	return $return;
}

//Set Recipiente en Orden
function setRecipientes($input){
	global $Clordenes;
	global $ClUsuarios;
	global $ClCouriers;
	global $ClSucursales;

	$errorMsg = "";
	$datosObligatorios = array("cod_orden", "cod_recipiente", "cantidad");
	if(!validate($datosObligatorios, $input, $errorMsg)){
		$return['success'] = 0;
		$return['mensaje'] = $errorMsg;
		return $return;
	}
	extract($input);

	//VALIDACION DE LA ORDEN
	$orden = $Clordenes->getOrden($cod_orden);
	if(!$orden){
		$return['success'] = 0;
		$return['mensaje'] = "No existe la orden $cod_orden";
		return $return;
	}
	
	if($orden['is_envio'] == "1"){
    	if($orden['estado']!="ENTRANTE" && $orden['estado']!="ASIGNADA" && $orden['estado']!="ENVIANDO"){
    		$return['success'] = 0;
    		$return['mensaje'] = "Solo se pueden escoger recipientes cuando la orden se encuentra en estado ASIGNADA o ENVIANDO, esta orden se encuentra en estado ".$orden['estado'];
    		$return['orden'] = $orden;
    		return $return;
    	}
	}else{
	    if($orden['estado']!="ACEPTADA"){
    		$return['success'] = 0;
    		$return['mensaje'] = "Solo se pueden escoger recipientes cuando la orden se encuentra en estado ACEPTADA, esta orden se encuentra en estado ".$orden['estado'];
    		$return['orden'] = $orden;
    		return $return;
    	}
	}
	
	if($Clordenes->setRecipientes($cod_orden, $cod_recipiente, $cantidad)){
		$return['success'] = 1;
		$return['mensaje'] = "Recipiente actualizado correctamente";
	}else{
		$return['success'] = 0;
		$return['mensaje'] = "Error al asignar el recipiente";
	}
	$return['orden'] = $orden;
	return $return;
}

//Get Cierre Diario
function cierreDiario($cod_sucursal){
    global $Clordenes;
    
    $ordenes = $Clordenes->getOrdenesNoFacturadas($cod_sucursal);
    $deliveryReport = $Clordenes->getOrdersByStatus($cod_sucursal, 1);
    $pickupReport = $Clordenes->getOrdersByStatus($cod_sucursal, 0);
    
    $return["success"] = 1;
	$return["mensaje"] = "Cierre diario";
	$return["ordenes"] = $ordenes;
	$return["delivery-report"] = $deliveryReport;
	$return["pickup-report"] = $pickupReport;
	return $return;
}

function ordenesProgramadas($cod_sucursal, $fecha) {
	global $Clordenes;
	//$ordenes = $Clordenes->getOrdenesProgramadas();
	$return['success'] = 1;
	$return['mensaje'] = "Ok";
	return $return;
}
?>
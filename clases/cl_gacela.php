<?php

class cl_gacela
{
	var $URL = "https://gacela.dev/api/";
	var $URLNEW = "https://api.tookanapp.com/v2";
	var $apiKey = "";
	var $tokens_ = "";
	var $tokenSucursal = "";

	public function __construct($tokens_) {
		$tokens = (object)json_decode($tokens_, true);

		$this->apiKey = $tokens->api;
		$this->tokenSucursal = $tokens->token;
		$this->tokens_ = $tokens_;

		if ($tokens->ambiente == "development")
			$this->URL = "https://gacela.dev/api/";
		else
			$this->URL = "https://gacela.co/api/";
	}

	public function cobertura($latitud, $longitud) {
		$data['api_token'] = $this->tokenSucursal;
		$data['destination_latitude'] = $latitud;
		$data['destination_longitude'] = $longitud;
		$json = json_encode($data);

		$ch = curl_init($this->URL . "tracking/coverage");
		$headers = array();
		$headers[] = 'Content-Type: application/json';
		$headers[] = 'Authorization: Bearer ' . $this->apiKey;

		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec($ch);
		curl_close($ch);
		return json_decode($response);
	}

	public function crearOrderDeprecated($orden) {
		//! DEPRECATED 24 nov 2023

		$data['api_token_gacela'] = $this->tokenSucursal;

		$data['external_id'] = $orden['id'];
		$data['name'] = $orden['usuario']['nombre'];
		$data['lastname'] = $orden['usuario']['apellido'];
		$data['phone'] =  $orden['usuario']['telefono'];
		$data['email'] = $orden['usuario']['correo'];
		$data['document'] = $orden['usuario']['num_documento'];
		$data['address'] = $orden['direccion'];
		$data['reference'] = $orden['referencia2'];
		$data['latitude'] = $orden['latitud'];
		$data['longitude'] = $orden['longitud'];
		$total = number_format($orden['total'], 2);


		$data['order_value'] = $total;
		$data['charge_value'] = $total;

		$chargeType = "other";
		$instructions = "Ya se realizo el cobro de este pedido";
		switch ($orden['pago']) {
			case "E":
				$pagoDetalle = "";
				if ($orden['is_suelto'] == 1) {
					$pagoDetalle = ',y enviar suelto de $' . number_format($orden['monto_suelto'], 2);
				}
				$chargeType = "cash";
				$instructions = "Se debe cobrar en efectivo " . $total . " " . $pagoDetalle;
				break;
			case "TC";
				$chargeType = "card";
				$instructions = "El motorizado debe llevar la maquinita para cobrar";
				break;
		}
		$data['charge_type'] = $chargeType;
		$data['charge_instructions'] = $instructions;


		$json = json_encode($data);

		$ch = curl_init($this->URL . "orders/set");
		$headers = array();
		$headers[] = 'Content-Type: application/json';
		$headers[] = 'Authorization: Bearer ' . $this->apiKey;

		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec($ch);
		curl_close($ch);

		file_put_contents("LogsGacela.log", PHP_EOL . $json . PHP_EOL . $response, FILE_APPEND);

		return json_decode($response);
	}

	public function crearOrder($orden) {
		// * AHORA SE CONSULTA EL ESTADO DE DEMANDAS ANTES DE CREAR LA ORDEN
		// * 24 nov 2023

		$data['api_token_gacela'] = $this->tokenSucursal;
		$json = json_encode($data);

		$ch = curl_init($this->URL . "demand_stats/current_alert");
		$headers = array();
		$headers[] = 'Content-Type: application/json';
		$headers[] = 'Authorization: Bearer ' . $this->apiKey;

		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec($ch);
		curl_close($ch);

		file_put_contents("LogsGacela.log", PHP_EOL . $json . PHP_EOL . $response, FILE_APPEND);

		$responseDemand = json_decode($response, true);
		if (isset($responseDemand["results"])) {
			if ($responseDemand["results"]["type"] == "bloqueo") {
				$r = [];
				$r["status"] = $responseDemand["status"];
				return $r;
			}
		}

		return $this->crearOrderConfirmed($orden);
	}

	public function crearOrderConfirmedDeprecated($orden) {
		// * 24 nov 2023
		//! DEPRECATED 17 sep 2025

		$data['api_token_gacela'] = $this->tokenSucursal;

		$data['external_id'] = $orden['id'];
		$data['name'] = $orden['usuario']['nombre'];
		$data['lastname'] = $orden['usuario']['apellido'];
		$data['phone'] =  $orden['usuario']['telefono'];
		$data['email'] = $orden['usuario']['correo'];
		$data['document'] = $orden['usuario']['num_documento'];
		$data['address'] = $orden['direccion'];
		$data['reference'] = $orden['referencia2'];
		$data['latitude'] = $orden['latitud'];
		$data['longitude'] = $orden['longitud'];
		$total = number_format($orden['total'], 2);


		$data['order_value'] = $total;
		$data['charge_value'] = $total;

		$chargeType = "other";
		$instructions = "Ya se realizo el cobro de este pedido";
		switch ($orden['pago']) {
			case "E":
				$pagoDetalle = "";
				if ($orden['is_suelto'] == 1) {
					$pagoDetalle = ',y enviar suelto de $' . number_format($orden['monto_suelto'], 2);
				}
				$chargeType = "cash";
				$instructions = "Se debe cobrar en efectivo " . $total . " " . $pagoDetalle;
				break;
			case "TC";
				$chargeType = "card";
				$instructions = "El motorizado debe llevar la maquinita para cobrar";
				break;
		}
		$data['charge_type'] = $chargeType;
		$data['charge_instructions'] = $instructions;


		$json = json_encode($data);

		$ch = curl_init($this->URL . "orders/set");
		$headers = array();
		$headers[] = 'Content-Type: application/json';
		$headers[] = 'Authorization: Bearer ' . $this->apiKey;

		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec($ch);
		curl_close($ch);

		file_put_contents("LogsGacela.log", PHP_EOL . $json . PHP_EOL . $response, FILE_APPEND);

		return json_decode($response);
	}

	public function crearOrderConfirmed($orden) {
		// * 16 sep 2025
		$tokens = (object)json_decode($this->tokens_, true);
		$order = (object)$orden;
		$user = (object)$order->usuario;
		$office = (object)$order->sucursal;
		$total = $order->total;
		$date = new DateTime("now", new DateTimeZone("America/Guayaquil"));
		$paymentMethod = "Tarjeta";
		$instructions = "Ya se realizo el cobro de este pedido";

		switch ($order->pago) {
			case "E":
				$paymentDetail = "";
				if ($order->is_suelto == 1) {
					$paymentDetail = ',y enviar suelto de $' . number_format($order->monto_suelto, 2);
				}
				$paymentMethod = "Efectivo";
				$instructions = "Se debe cobrar en efectivo {$total} {$paymentDetail}";
				break;
			case "TC":
				$paymentMethod = "Tarjeta";
				$instructions = "El motorizado debe llevar la maquinita para cobrar";
				break;
			default:
				$paymentMethod = "Tarjeta";
				$instructions = "Ya se realizo el cobro de este pedido";
				break;		
		}

		$data = [
			'api_key' => $tokens->api_key,
			'order_id' => $order->id,
			'job_description' => "Orden de {$office->nombre}",
			'job_pickup_phone' => $tokens->store_id,
			'job_pickup_name' => $tokens->name,
			'job_pickup_email' => ($office->nombre && $office->nombre != '') ? $office->nombre : $tokens->email,
			'job_pickup_address' => $tokens->address,
			'job_pickup_latitude' => $tokens->latitude,
			'job_pickup_longitude' => $tokens->longitude,
			'job_pickup_datetime' => $date->modify('+5 minutes')->format('Y-m-d H:i:s'),
			'customer_email' => $user->correo,
			'customer_username' => trim($user->nombre . " " . $user->apellido),
			'customer_address' => $order->direccion,
			'customer_phone' => $user->telefono,
			'latitude' => $order->latitud,
			'longitude' => $order->longitud,
			'job_delivery_datetime' => $date->modify('+30 minutes')->format('Y-m-d H:i:s'),
			'pickup_custom_field_template' => $tokens->pickup_custom_field_template,
			'custom_field_template' => $tokens->custom_field_template,
			'meta_data' => [
				[
					'label' => 'Cédula',
					'data' => ($user->num_documento && $user->num_documento != '') ? $user->num_documento : '0999999999'
				],
				[
					'label' => 'Referencia_de_entrega',
					'data' => $order->referencia2
				],
				[
					'label' => 'Número_de_factura',
					'data' => $order->id
				],
				[
					'label' => 'Peso_de_la_orden_(kg)',
					'data' => '1'
				],
				[
					'label' => 'Valor_factura',
					'data' => $total
				],
				[
					'label' => 'Gestión_de_cobro_contra_entrega',
					'data' => $order->pago == 'E' ? 'SI' : 'NO'
				],
				[
					'label' => 'Tipo_de_Cobro',
					'data' => $paymentMethod
				],
				[
					'label' => 'Valor_a_cobrar',
					'data' => $total
				],
				[
					'label' => 'Instrucciones_adicionales_para_el_cobro',
					'data' => $instructions
				],
				[
					'label' => 'Detalle_factura',
					'data' => []
				]
			],
			'timezone' => 300,
			'has_pickup' => 1,
			'has_delivery' => 1,
			'layout_type' => 0,
			'tracking_link' => 1,
			'notify' => 1,
			'geofence' => 1,
			'auto_assignment' => 1,
			'tags' => 'Moto'
		];

		$json = json_encode($data);

		$ch = curl_init($this->URLNEW . "/create_task");
		$headers = array();
		$headers[] = 'Content-Type: application/json';

		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec($ch);
		curl_close($ch);

		file_put_contents("LogsGacela.log", PHP_EOL . $json . PHP_EOL . $response, FILE_APPEND);

		return json_decode($response);
	}

	public function cancelarOrder($token, &$curlInfo) {
		$data['api_token_gacela'] = $this->tokenSucursal;

		$data['order_token'] = $token;
		$json = json_encode($data);

		$ch = curl_init($this->URL . "cancel_order");
		$headers = array();
		$headers[] = 'Content-Type: application/json';
		$headers[] = 'Authorization: Bearer ' . $this->apiKey;

		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec($ch);
		$curlInfo = curl_getinfo($ch);
		curl_close($ch);
		return json_decode($response);
	}

	public function trackingOrder($token) {
		$ch = curl_init($this->URL . "order_tracking/" . $token);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec($ch);
		curl_close($ch);
		return json_decode($response);
	}

	public function order_status_update($urlw) {
		$data['api_token_gacela'] = $this->tokenSucursal;
		$data['url'] = $urlw;

		$json = json_encode($data);

		$ch = curl_init($this->URL . "webhooks/order_status_updates");
		$headers = array();
		$headers[] = 'Content-Type: application/json';
		$headers[] = 'Authorization: Bearer ' . $this->apiKey;

		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec($ch);
		curl_close($ch);
		return json_decode($response);
	}

	public function webhooks_info() {
		$data['api_token_gacela'] = $this->tokenSucursal;

		$json = json_encode($data);

		$ch = curl_init($this->URL . "webhooks/info");
		$headers = array();
		$headers[] = 'Content-Type: application/json';
		$headers[] = 'Authorization: Bearer ' . $this->apiKey;

		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec($ch);
		curl_close($ch);
		return json_decode($response);
	}

	public function costoCarreraDeprecated($latitud, $longitud){
		$data['api_token'] = $this->tokenSucursal;
		$data['destination_latitude'] = $latitud;
		$data['destination_longitude'] = $longitud;
		$json = json_encode($data);

		$ch = curl_init($this->URL."tracking/fare");
		$headers = array();
		$headers[] = 'Content-Type: application/json';
		$headers[] = 'Authorization: Bearer '.$this->apiKey;
		
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");    
		curl_setopt($ch, CURLOPT_POSTFIELDS, $json);                                                                 
		curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);      
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);   
		$response = curl_exec($ch);
		curl_close($ch);
		return json_decode($response);
	}

	public function costoCarrera($pickupLatitude, $pickupLongitude, $deliveryLatitude, $deliveryLongitude) {
		$data = [
			'template_name' => 'Template Delivery',
			'pickup_latitude' => $pickupLatitude,
			'pickup_longitude' => $pickupLongitude,
			'api_key' => $this->tokenSucursal,
			'delivery_latitude' => $deliveryLatitude,
			'delivery_longitude' => $deliveryLongitude,
		];

		return ['data' => $data];
		
		$json = json_encode($data);

		$ch = curl_init($this->URL . "tracking/get_fare_estimate");
		$headers = array();
		$headers[] = 'Content-Type: application/json';

		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$response = curl_exec($ch);
		curl_close($ch);
		return json_decode($response);
	}
}

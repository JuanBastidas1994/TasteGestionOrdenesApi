<?php

class cl_gacela
{
		var $URL = "https://gacela.dev/api/";
		var $apiKey = "";
		var $tokenSucursal = "";
		public function __construct($papiKey=null, $ptokenSucursal=null, $pAmbiente="development")
		{
			$this->apiKey = $papiKey;
			$this->tokenSucursal = $ptokenSucursal;
			if($pAmbiente == "development")
			    $this->URL = "https://gacela.dev/api/";
			else    
			    $this->URL = "https://gacela.co/api/";
		}

		public function cobertura($latitud, $longitud){
			$data['api_token'] = $this->tokenSucursal;
			$data['destination_latitude'] = $latitud;
			$data['destination_longitude'] = $longitud;
			$json = json_encode($data);

			$ch = curl_init($this->URL."tracking/coverage");
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
		
		public function crearOrder($orden){
            // * AHORA SE CONSULTA EL ESTADO DE DEMANDAS ANTES DE CREAR LA ORDEN
            // * 24 nov 2023

			$data['api_token_gacela'] = $this->tokenSucursal;
			$json = json_encode($data);
			
			$ch = curl_init($this->URL."demand_stats/current_alert");
		    $headers = array();
		    $headers[] = 'Content-Type: application/json';
		    $headers[] = 'Authorization: Bearer '.$this->apiKey;
		  
		    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");    
		    curl_setopt($ch, CURLOPT_POSTFIELDS, $json);                                                                 
		    curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);      
		    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);   
		    $response = curl_exec($ch);
		    curl_close($ch);
		    
		    file_put_contents("LogsGacela.log", PHP_EOL . $json . PHP_EOL . $response, FILE_APPEND);

            $responseDemand = json_decode($response, true);
            if(isset($responseDemand["results"])) {
                if($responseDemand["results"]["type"] == "bloqueo") {
                    return $responseDemand["status"];
                }
            }
		    
		    return $this->crearOrderConfirmed($orden);
		}

        public function crearOrderConfirmed($orden){
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
			$total = number_format($orden['total'],2);
			
			
			$data['order_value'] =$total;
			$data['charge_value'] = $total;
			
			$chargeType = "other";
			$instructions = "Ya se realizo el cobro de este pedido";
            switch($orden['pago'])
            {
                case "E":
					$pagoDetalle = "";
                    if($orden['is_suelto'] == 1){
                    	$pagoDetalle = ',y enviar suelto de $'.number_format($orden['monto_suelto'],2);
                    }
                    $chargeType = "cash";
                    $instructions = "Se debe cobrar en efectivo ".$total. " ".$pagoDetalle;
                    break;
                case "TC"; 
                    $chargeType="card";
                    $instructions = "El motorizado debe llevar la maquinita para cobrar";
                    break;
                    
            }
			$data['charge_type'] = $chargeType;
			$data['charge_instructions'] = $instructions;
			
			
			$json = json_encode($data);
			
			$ch = curl_init($this->URL."orders/set");
		    $headers = array();
		    $headers[] = 'Content-Type: application/json';
		    $headers[] = 'Authorization: Bearer '.$this->apiKey;
		  
		    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");    
		    curl_setopt($ch, CURLOPT_POSTFIELDS, $json);                                                                 
		    curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);      
		    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);   
		    $response = curl_exec($ch);
		    curl_close($ch);
		    
		    file_put_contents("LogsGacela.log", PHP_EOL . $json . PHP_EOL . $response, FILE_APPEND);
		    
		    return json_decode($response);
		}
		
		public function cancelarOrder($token, &$curlInfo){
			$data['api_token_gacela'] = $this->tokenSucursal;
			
			$data['order_token'] = $token;
			$json = json_encode($data);

			$ch = curl_init($this->URL."cancel_order");
		    $headers = array();
		    $headers[] = 'Content-Type: application/json';
		    $headers[] = 'Authorization: Bearer '.$this->apiKey;
		  
		    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");    
		    curl_setopt($ch, CURLOPT_POSTFIELDS, $json);                                                                 
		    curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);      
		    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);   
		    $response = curl_exec($ch);
			$curlInfo = curl_getinfo($ch);
		    curl_close($ch);
		    return json_decode($response);
		}
		
		public function trackingOrder($token){
			$ch = curl_init($this->URL."order_tracking/".$token);
		    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");         
		    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);   
		    $response = curl_exec($ch);
		    curl_close($ch);
		    return json_decode($response);
		}
		
		public function order_status_update($urlw){
			$data['api_token_gacela'] = $this->tokenSucursal;
			$data['url'] = $urlw;
			
			$json = json_encode($data);

			$ch = curl_init($this->URL."webhooks/order_status_updates");
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
		
		public function webhooks_info(){
			$data['api_token_gacela'] = $this->tokenSucursal;
			
			$json = json_encode($data);

			$ch = curl_init($this->URL."webhooks/info");
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

		public function costoCarrera($latitud, $longitud){
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
}
?>
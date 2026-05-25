<?php

class cl_picker
{
		var $URL = "https://dev-api.pickerexpress.com/api/";
		var $apiKey = "";
		var $tokenSucursal = "";
		var $msgError = "";
		public function __construct($papiKey=null, $pAmbiente="development")
		{
			$this->apiKey = $papiKey;
			if($pAmbiente == "development")
				$this->URL = "https://dev-api.pickerexpress.com/api/";
			else    
				$this->URL = "https://api.pickerexpress.com/api/";
		}
		
		public function crearOrder($orden){
		    $nombre_completo = trim($orden['usuario']['nombre']." ".$orden['usuario']['apellido']);
		    $arrayNombre = explode(" ", $nombre_completo, 2);
		    
			$data['customerName'] = $arrayNombre[0];
			
			$lastName = "";
			if(isset($arrayNombre[1])){
			    $lastName = (trim($arrayNombre[1])!=="") ? $arrayNombre[1] : "Desconocido";
			}else{
			    $lastName = "Desconocido";
			}

			$telefono_cliente = "";
			if (strpos($orden['telefono'], '+593') !== false) {
                $telefono_cliente = str_replace('+593', '', $orden['telefono']);
            }
            
            if(strlen($telefono_cliente) > 10){
                $telefono_cliente = substr($telefono_cliente, 1, 9);
            }
			
			$data['customerLastName'] = $lastName;
			$data['customerEmail'] = $orden['usuario']['correo'];
			$data['customerCountryCode'] = "+593";
			$data['customerMobile'] =  $telefono_cliente;
			$data['address'] = $orden['direccion']." ".$orden['referencia2'];
			$data['reference'] = $orden['referencia2'];
			$data['latitude'] = $orden['latitud'];
			$data['longitude'] = $orden['longitud'];
			$data['zipCode'] = "0245";
			$total = number_format($orden['total'],2);
			$data['sendSMR'] = true;
			$data['orderAmount'] =$total;
			$data['businessDeliveryFee'] = number_format($orden['envio'],2);
			$data['cookTime'] = 0;
			$data['externalBookingId'] = $orden['id'];
			if($orden['observacion'] !== "")
			    $data['bookingNotes'] = $orden['observacion'];

			if($orden['pago'] == "E" || $orden['pago'] == "TC"){
				$envio = number_format($orden['envio'],2);
				$totalSinEnvio = $total - $envio;
				$data['orderAmount'] = number_format($totalSinEnvio,2);
				$data['paymentMethod'] = "CASH";
			}else{
				$data['paymentMethod'] = "CARD";
			}
			$json = json_encode($data);
			
			$ch = curl_init($this->URL."createBooking");
		    $headers = array();
		    $headers[] = 'Content-Type: application/json';
		    $headers[] = 'Authorization: Bearer '.$this->apiKey;
		  
		    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");    
		    curl_setopt($ch, CURLOPT_POSTFIELDS, $json);                                                                 
		    curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);      
		    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);   
		    $response = curl_exec($ch);
		    curl_close($ch);
		    
		    file_put_contents("LogsPicker.log", PHP_EOL . $this->apiKey." - ".$json . PHP_EOL . $response, FILE_APPEND);
		    
		    return json_decode($response);
		}
		
		public function cancelOrder($token, $motivo="Cancelada por el comercio"){
			$data['bookingID'] = $token;
			$data['cancelReason'] = $motivo;
			$json = json_encode($data);

			$ch = curl_init($this->URL."cancelBooking");
		    $headers = array();
		    $headers[] = 'Content-Type: application/json';
		    $headers[] = 'Authorization: Bearer '.$this->apiKey;
		  
		    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");    
		    curl_setopt($ch, CURLOPT_POSTFIELDS, $json);                                                                 
		    curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);      
		    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);   
		    $response = curl_exec($ch);
		    curl_close($ch);
		    
		    file_put_contents("LogsPicker.log", PHP_EOL . $this->URL."cancelBooking"." - ".$json . PHP_EOL . $response, FILE_APPEND);
		    return json_decode($response);
		}
		
		public function trackingOrder($tokenOrden){
			$ch = curl_init($this->URL."getBookingDetails?bookingID=".$tokenOrden);
            $headers = array();
		    $headers[] = 'Content-Type: application/json';
            $headers[] = 'content-language: es';
		    $headers[] = 'Authorization: '.$this->apiKey;

		    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");    
		    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);   
            curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);  
		    $response = curl_exec($ch);
		    curl_close($ch);
		    return json_decode($response, true);
		}
		

		/*WEBHOOKS*/
		public function webhooks_info(){
			$ch = curl_init($this->URL."webhooks");
		    $headers = array();
		    $headers[] = 'Content-Type: application/json';
            $headers[] = 'content-language: es';
		    $headers[] = 'Authorization: '.$this->apiKey;
		  
		    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");                                                                
		    curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);      
		    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);   
		    $response = curl_exec($ch);
		    curl_close($ch);
		    return json_decode($response);
		}

		public function order_status_update($api, $URL, $type){
			//"UPDATE_BOOKING_STATUS"
			//"DRIVER_ASSIGNED"
			$data['url'] = $URL;
			$data['type'] = $type;
			
			$json = json_encode($data);

			echo $this->URL;
			$ch = curl_init($this->URL."webhooks");
		    $headers[] = 'Content-Type: application/json';
            $headers[] = 'content-language: es';
		    $headers[] = 'Authorization: '.$api;
		  
		    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");    
		    curl_setopt($ch, CURLOPT_POSTFIELDS, $json);                                                                 
		    curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);      
		    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);   
		    $response = curl_exec($ch);
		    curl_close($ch);
		    return json_decode($response);
		}

		public function order_status_update_ambiente($api, $URL, $type, $ambiente){
			if($ambiente == "development")
				$this->URL = "https://dev-api.pickerexpress.com/api/";
			else    
				$this->URL = "https://api.pickerexpress.com/api/";

			$data['url'] = $URL;
			$data['type'] = $type;
			
			$json = json_encode($data);

			$ch = curl_init($this->URL."webhooks");
		    $headers[] = 'Content-Type: application/json';
            $headers[] = 'content-language: es';
		    $headers[] = 'Authorization: '.$api;
		  
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
<?php

class cl_datafast
{
        var $Isinitialize = true;
        var $URL = "https://test.oppwa.com/v1/";
        var $ambiente = "development";
        var $fase = "FASE1";
        var $api = "";
        var $entityId = "";
        var $customParameters = "";
        var $MID = "";
        var $TID = "";
        var $msgError = "";
        
		public function __construct($pcod_sucursal=null)
		{
		    if($pcod_sucursal !== null){
		        $this->getTokensByOffice($pcod_sucursal);
		    }else{
			    $this->getTokens();
		    }
		}
		
		public function getTokens(){
		    $query =  "SELECT * FROM tb_empresa_datafast WHERE cod_empresa = ".cod_empresa;		
			$resp = Conexion::buscarRegistro($query);
			if($resp){
			    $cp = $resp['mid']."_".$resp['tid'];
			    $this->api = $resp['api'];
			    $this->entityId = $resp['entityId'];
			    $this->MID = $resp['mid'];
			    $this->TID = $resp['tid'];
			    $this->customParameters = $cp;
			    $this->fase = $resp['fase'];
			    $this->ambiente = $resp['ambiente'];
			    if($resp['ambiente'] == "development")
			        $this->URL = "https://test.oppwa.com/v1/";
    			else    
    			    $this->URL = "https://oppwa.com/v1/";
    			$this->Isinitialize = true;    
			}else{
			    $this->Isinitialize = false;
			}
		}
		
		public function getTokensByOffice($office_id){
		    $query =  "SELECT * FROM tb_empresa_sucursal_datafast WHERE cod_sucursal = $office_id";		
			$resp = Conexion::buscarRegistro($query);
			if($resp){
			    $cp = $resp['mid']."_".$resp['tid'];
			    $this->api = 'Bearer '.$resp['api'];
			    $this->entityId = $resp['entityId'];
			    $this->MID = $resp['mid'];
			    $this->TID = $resp['tid'];
			    $this->customParameters = $cp;
			    $this->fase = $resp['fase'];
			    $this->ambiente = $resp['ambiente'];
			    if($resp['ambiente'] == "development")
			        $this->URL = "https://eu-test.oppwa.com/v1/";
    			else    
    			    $this->URL = "https://eu-prod.oppwa.com/v1/";
    			$this->Isinitialize = true;    
			}else{
			    $this->Isinitialize = false;
			}
		}
		
	//REVERSA
	    public function refund($id, $total, &$error, &$refund){
	        $data = [];
    		$data['entityId'] = $this->entityId;
    		$data['amount'] = number_format($total, 2);
    		$data['currency'] = "USD";
    		$data['paymentType'] = "RF";
    		
    		if($this->ambiente == "development")
    		    $data['testMode'] = "EXTERNAL";    
    		
    		$data = http_build_query($data);
    		
    		$ch = curl_init($this->URL."payments/".$id);
            $headers = array();
            $headers[] = 'Authorization: '.$this->api;
          
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);      
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);   
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);   
            $response = curl_exec($ch);
            if(curl_errno($ch)){
            	return curl_errno($ch);
            }
            curl_close($ch);
            $refund = json_decode($response,true);
            if(isset($refund['result']['code'])){
                if($refund['result']['code'] == $this->getDebitCodeSuccess()){
                    return true;
                }else{
                    if(isset($refund['resultDetails']['ExtendedDescription'])){
                        $error = $refund['resultDetails']['ExtendedDescription'];
                    }else if(isset($refund['result']['description'])){
                        $error = $refund['result']['description'];
                    }
                    return false;
                }
            }else{
                $error = "Error desconocido, Verificar en la plataforma de Datafast";
                return false;
            }
    	}
		
	//GET TOKEN EN DESARROLLO FASE 1
		public function getTransactionFase1($total, &$data){
    		$data['entityId'] = $this->entityId;
    		$data['amount'] = number_format($total, 2);
    		$data['currency'] = "USD";
    		$data['paymentType'] = "DB";
    		
    		$data = http_build_query($data);
    		
    		$ch = curl_init($this->URL."checkouts/");
            $headers = array();
            $headers[] = 'Authorization: '.$this->api;
          
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);      
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);   
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);   
            $response = curl_exec($ch);
            if(curl_errno($ch)){
            	return curl_errno($ch);
            }
            curl_close($ch);
            return json_decode($response,true);
    	}
    	
    	
    //GET TOKEN EN PRODUCCION O DESARROLLO FASE 2
    	public function getTransactionProduction($usuario, $productos, $ip, $iva, $base0, $base12, $total){
        	$customParameters = $this->customParameters;
        	
        	if($this->ambiente == "development"){
        	    $data['testMode'] = "EXTERNAL";
        	}
        	
        	$data['entityId'] = $this->entityId;
        	$data['amount'] = number_format($total, 2);
        	$data['currency'] = "USD";
        	$data['paymentType'] = "DB";
        	$data['merchantTransactionId'] = $this->generateTransactionId($usuario['cod_usuario']);

        	/*INFORMACION USUARIO*/
        	$firstName = $usuario['nombre'];
        	$secondName = "";
        	$nombre = explode(" ",$usuario['nombre'],2);
        	if(isset($nombre[1])){
        		$firstName = $nombre[0];
        		$secondName = $nombre[1];
        	}
        
        	$data['customer.givenName'] = $firstName;
        	$data['customer.middleName'] = $secondName;
        	$data['customer.surname'] = $usuario['apellido'];
        	$data['customer.merchantCustomerId'] = $usuario['cod_usuario'];
        	$data['customer.email'] = $usuario['correo'];
        	$data['customer.phone'] = $usuario['telefono'];
        	$data['customer.identificationDocType'] = "IDCARD";
        	$data['customer.identificationDocId'] = $usuario['num_documento'];
        	$data['customer.ip'] = $ip;
        	
        	/*INFORMACION DE LOS PRODUCTOS*/
        	$x=0;
        	foreach($productos as $item){
        	    $data['cart.items['.$x.'].name'] = $item['nombre'];
        	    $data['cart.items['.$x.'].description'] = $item['nombre'];
        	    $data['cart.items['.$x.'].price'] = number_format($item['precio'], 2);
        	    $data['cart.items['.$x.'].quantity'] = $item['cantidad'];
        	    $x++;
        	}
        	
        	/*INFORMACION DE UBICACION*/
        	$data['billing.street1'] = "Alborada 13ava etapa";
        	$data['billing.country'] = "EC";
        	$data['shipping.street1'] = "Alborada 13ava etapa";
        	$data['shipping.country'] = "EC";
        	
        	/*DATOS ADICIONALES*/
        	/*
        	$infoIva = str_pad(str_replace(".","",$iva),12,'0', STR_PAD_LEFT);
        	$infoBase12 = str_pad(str_replace(".","",$base12),12,'0', STR_PAD_LEFT);
        	$infoBase0 = str_pad(str_replace(".","",$base0),12,'0', STR_PAD_LEFT);
        	$data['customParameters['.$customParameters.']'] = "00810030070103910004012".$infoIva."05100817913101052012".$infoBase0."053012".$infoBase12;*/
        	
        	
        	$infoIva = number_format($iva, 2, ".","");
        	$infoBase12 = number_format($base12, 2, ".","");
        	$infoBase0 = number_format($base0, 2, ".","");
        	
        	$data['risk.parameters[USER_DATA2]'] = alias;
        	$data['customParameters[SHOPPER_VAL_BASE0]'] = $infoBase0;
        	$data['customParameters[SHOPPER_VAL_BASEIMP]'] = $infoBase12;
        	$data['customParameters[SHOPPER_VAL_IVA]'] = $infoIva;
        	$data['customParameters[SHOPPER_MID]'] = $this->MID;
        	$data['customParameters[SHOPPER_TID]'] = $this->TID;
        	$data['customParameters[SHOPPER_ECI]'] = '0103910';
        	$data['customParameters[SHOPPER_PSERV]'] = '17913101';
        	$data['customParameters[SHOPPER_VERSIONDF]'] = 2;
        	
        	
        	$json = json_encode($data);
        	$data = http_build_query($data);
        	
        	echo $json;
        	echo $data;
        
        	$ch = curl_init($this->URL."checkouts/");
            $headers = array();
            $headers[] = 'Authorization: '.$this->api;
          
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);      
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);   
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);   
            $response = curl_exec($ch);
            if(curl_errno($ch)){
            	return curl_errno($ch);
            }
            curl_close($ch);
            return json_decode($response,true);
        }
        
        
        public function debitar($id){
        	$url = $this->URL."checkouts/".$id."/payment?entityId=".$this->entityId;
        	$ch = curl_init($url);
            $headers = array();
            $headers[] = 'Authorization: '.$this->api;
          
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");                                                                     
            curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);      
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);   
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);   
            $response = curl_exec($ch);
            if(curl_errno($ch)){
            	return curl_errno($ch);
            }
            curl_close($ch);
            return json_decode($response,true);
        }
        
        public function getDebitCodeSuccess(){
            $codeSuccess = "000.100.112"; //FASE 2 DEVELOPMENT
        	if($this->ambiente == "production")
        	    $codeSuccess = "000.000.000";   //PRODUCCION
        	else if($this->ambiente == "FASE1"){
        	    $codeSuccess = "000.100.110"; //FASE 1 DEVELOPMENT
        	}
        	
        	return $codeSuccess;
        }
        
        
    	
    	//FUNCIONES ADICIONALES
        function generateTransactionId($cod_usuario){
            date_default_timezone_set('America/Guayaquil');
            $transaction = $cod_usuario.date("YmdHis", time());
        	return str_pad($transaction, 10, '0', STR_PAD_LEFT);
        }
}
?>
<?php

class cl_paymentez
{
    var $cod_empresa = 0;
    var $cod_sucursal = 0;
    var $URL = "https://ccapi-stg.paymentez.com/v2/";
    var $ambiente = "development";
    var $SERVER_APP_CODE = "";
    var $SERVER_APP_KEY = "";
     
    public function __construct($pcod_empresa=null, $pcod_sucursal=null)
	{
	    $this->cod_empresa = $pcod_empresa;
	    $this->cod_sucursal = $pcod_sucursal;
		$this->getTokens();
	}
	
	public function getTokens(){
	    $query =  "SELECT * FROM tb_empresa_paymentez WHERE cod_empresa = ".$this->cod_empresa;		
		$resp = Conexion::buscarRegistro($query);
		if($resp){
		    $ambiente = $resp['ambiente'];
		    if($ambiente == "production")
                $this->URL = 'https://ccapi.paymentez.com/v2/';
            else
                $this->URL = 'https://ccapi-stg.paymentez.com/v2/';
            $this->SERVER_APP_CODE = $resp['server_code'];
            $this->SERVER_APP_KEY = $resp['server_key'];   
		}
		
		$query =  "SELECT * FROM tb_empresa_sucursal_paymentez WHERE cod_sucursal = ".$this->cod_sucursal;		
		$resp = Conexion::buscarRegistro($query);
		if($resp){
		    $ambiente = $resp['ambiente'];
		    if($ambiente == "production")
                $this->URL = 'https://ccapi.paymentez.com/v2/';
            else
                $this->URL = 'https://ccapi-stg.paymentez.com/v2/';
            $this->SERVER_APP_CODE = $resp['server_code'];
            $this->SERVER_APP_KEY = $resp['server_key'];   
		}
		
    	$date = new DateTime();
    	$unix_timestamp = $date->getTimestamp();
    	$uniq_token_string = $this->SERVER_APP_KEY.$unix_timestamp;
    	$uniq_token_hash = hash('sha256', $uniq_token_string);
    	$this->auth_token = base64_encode($this->SERVER_APP_CODE.";".$unix_timestamp.";".$uniq_token_hash);
	}
	
	public function generateAuthCode(){
	    $date = new DateTime();
    	$unix_timestamp = $date->getTimestamp();
    	$uniq_token_string = $this->SERVER_APP_KEY.$unix_timestamp;
    	$uniq_token_hash = hash('sha256', $uniq_token_string);
    	return base64_encode($this->SERVER_APP_CODE.";".$unix_timestamp.";".$uniq_token_hash);
	}
	
	public function refund($identifier, &$error, &$refund){
	    $user['id'] = $identifier;
	    $data['transaction'] = $user;
	    $data = json_encode($data);
	    
	    $auth = $this->generateAuthCode();
	    $ch = curl_init($this->URL.'transaction/refund/');
        $headers = array();
        $headers[] = 'Auth-Token: '.$auth;
        $headers[] = 'Content-Type: application/json';
      
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);  
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);    
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);   
        $response = curl_exec($ch);
        if (curl_errno($ch)) {
    	    $error_msg = curl_error($ch);
    	    echo $error_msg;
    	}
        curl_close($ch);
        
        $refund = json_decode($response, true);
        if(isset($refund['status'])){
            if($refund['status'] == "success"){
            	return true;
            }else{
            	$error = $refund['detail'];
            	return false;
            }
        }else{
            if(isset($refund['error'])){
                $error = $refund['error']['type']." - ".$refund['error']['description'];
            	return false;
            }else{
                $error = "Error desconocido, Verificar en la plataforma de Paymentez";
            	return false;
            }
        }
	}
}

?>
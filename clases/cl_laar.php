<?php

class cl_laar
{
		var $URL = "https://api.laarcourier.com:9727/";
		var $cod_empresa = "";
		var $cod_sucursal = "";
		var $msgError = "";
		
		var $username, $password, $API;

        public function __construct($pToken=null, $pUsername=null, $pPassword=null)
		{
			$this->API = $pToken;
            $this->username = $pUsername;
            $this->password = $pPassword;
            $this->getToken();
		}
    	
    	public function getToken(){
    	    $data['username'] = $this->username;
    	    $data['password'] = $this->password;
            $json = json_encode($data);
            
            $link = $this->URL.'/authenticate';
            $ch = curl_init($link);
            
            $headers = array();
            $headers[] = 'Content-Type: application/json';
    
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
            curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);  
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);   
            $response = curl_exec($ch);
            if($response === false){
                $this->msgError = "Curl error: " . curl_error($ch);
                return false;
            }
            
            curl_close($ch);
            $info = json_decode($response,true);
            if(isset($info['token']))
                $this->API = $info['token'];
            else{
                $this->msgError = $info['Message'];
                return false;
            }    
            return true;
    	}

        public function getSucursal($cod_sucursal){
            $query = "SELECT s.cod_sucursal as id, s.nombre, s.direccion, s.telefono, s.correo, c.codigo, c.nombre, c.provincia
                    FROM tb_sucursales s
                    INNER JOIN tb_ciudades c ON c.cod_ciudad = s.cod_ciudad
                    WHERE s.cod_sucursal = $cod_sucursal";
            return Conexion::buscarRegistro($query);        
        }
		
		public function crearGuia($orden){
		    if($this->API == ""){
    	        $this->msgError = "Empresa no tiene configurado Laar o esta fuera de servicio";
    	        return false;
    	    }

            $sucursal = $this->getSucursal($orden['cod_sucursal']);
            if(!$sucursal){
                $this->msgError = "Sucursal no tiene definido una ciudad, por favor revisar";
    	        return false;
            }
            
		    //DATOS ORIGEN
		    $origen['identificacionO']="0950771907";
		    $origen['ciudadO']=$sucursal['codigo'];
		    $origen['nombreO']=$sucursal['nombre'];
		    $origen['direccion']=$sucursal['direccion'];
		    $origen['referencia']="";
		    $origen['numeroCasa']="";
		    $origen['postal']="";
		    $origen['telefono']="";
		    $origen['celular']=$sucursal['telefono'];
		    
		    //DATOS DESTINO
		    $destino['identificacionD']=$orden['usuario']['num_documento']; // opcional
		    $destino['ciudadD']=$orden['destino']['codigo'];
		    $destino['nombreD']=$orden['usuario']['nombre'].' '.$orden['usuario']['apellido'];
		    $destino['direccion']=$orden['direccion'];
		    $destino['referencia']= $orden['referencia2']; // opcional
		    $destino['numeroCasa']=$orden['destino']['num_casa'];
		    $destino['postal']=$orden['destino']['cod_postal'];
		    $destino['telefono']=""; // opcional
		    $destino['celular']=$orden['usuario']['telefono'];
		    
		    $peso = 0;
		    $nPieza=0;
		    foreach ($orden['detalle'] as $d) {
                $peso = $d['peso']+$peso;
                $nPieza++;
            }
		    
		    $nPieza = 1;
		    //DATOS A ENVIAR
			$data['origen'] = $origen;
			$data['destino'] = $destino;
			$data['numeroGuia'] = ""; // opcional
			$data['tipoServicio'] ="201202002002013"; // carga 
			$data['noPiezas'] = $nPieza;
			$data['peso'] = $peso;
			$data['valorDeclarado'] =0; //opcional
			$data['contiene'] ="DECODIFICADOR";
			$data['tamanio'] ="";//opcional
			$data['cod'] = false;//opcional
			$data['costoflete'] = 0;//"si tiene valor de cod true el campo obligatorio"
			$data['costoproducto'] = 0;//"si tiene valor de cod true el campo obligatorio"
			$data['tipocobro'] = 0;//opcional
			$data['comentario'] = "";//opcional
			$data['fechaPedido'] = "";//opcional
			$json = json_encode($data);

			$ch = curl_init($this->URL."guias/contado");
		    $headers = array();
		    $headers[] = 'Content-Type: application/json';
		    $headers[] = 'Authorization: Bearer '.$this->API;
		  
		    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST"); 
		    curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
		    curl_setopt($ch, CURLOPT_HTTPHEADER,$headers); 
		    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		    $response = curl_exec($ch);
		    if($response === false){
                $this->msgError = "Curl error: " . curl_error($ch);
                return false;
            }else{
                //echo 'Operacion correcta';
            }
		    curl_close($ch);
		    
		    //file_put_contents("LogsLaar.log", PHP_EOL . $json . PHP_EOL . $response, FILE_APPEND);
		    
		    return json_decode($response);
		    
		}
		
		public function DataGuia($token){
			$curl = curl_init();
            curl_setopt_array($curl, array(
              CURLOPT_URL => 'https://api.laarcourier.com:9727/guias/v2/'.$token,
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => '',
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 0,
              CURLOPT_FOLLOWLOCATION => true,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => 'GET',
              CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json;charset=utf-8','User-Agent: PostmanRuntime/7.26.8'
              ),
            ));
            $response = curl_exec($curl);
            if($response === false)
            {
                echo 'Curl error: ' . curl_error($curl);
            }
            var_dump($response);
            curl_close($curl);
            
		    return $response;
		}
		
		public function costoEnvio($latitud, $longitud){
		    if($this->API == ""){
    	        $this->msgError = "Empresa no tiene configurado Laar o esta fuera de servicio";
    	        return false;
    	    }
    	    
			$data['codigoServicio'] = 201202002002013;
			$data['codigoCiudadOrigen'] = $latitud;
			$data['codigoCiudadDestino'] = $longitud;
			$data['piezas'] = $longitud;
			$data['peso'] = $longitud;
			$json = json_encode($data);

			$ch = curl_init($this->URL."cotizadores/tarifa/normal");
		    $headers = array();
		    $headers[] = 'Content-Type: application/json';
		    $headers[] = 'Authorization: Bearer '.$this->API;
		  
		    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST"); 
		    curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
		    curl_setopt($ch, CURLOPT_HTTPHEADER,$headers); 
		    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		    $response = curl_exec($ch);
		    if($response === false){
                $this->msgError = "Curl error: " . curl_error($ch);
                return false;
            }else{
                //echo 'Operacion correcta';
            }
		    curl_close($ch);
		    return json_decode($response);
		}
}
?>
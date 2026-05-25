<?php

class cl_inlog
{
		var $URL = "https://www.softwarecristal.com/web/api/";
		var $msgError = "";
		
		var $idCliente, $token;

        public function __construct($pToken=null, $pIdCliente=null)
		{
			//$this->token = $pToken;
			$this->URL .= '?token='.$pToken;
            $this->idCliente = $pIdCliente;
		}
    	

        public function getSucursal($cod_sucursal){
            $query = "SELECT s.cod_sucursal as id, s.nombre, s.direccion, s.telefono, s.correo, c.codigo, c.nombre, c.provincia
                    FROM tb_sucursales s
                    INNER JOIN tb_ciudades c ON c.cod_ciudad = s.cod_ciudad
                    WHERE s.cod_sucursal = $cod_sucursal";
            return Conexion::buscarRegistro($query);        
        }
		
		public function crearGuia($orden){
		    if($this->URL == ""){
    	        $this->msgError = "Empresa no tiene configurado Inlog o esta fuera de servicio";
    	        return false;
    	    }

            $sucursal = $this->getSucursal($orden['cod_sucursal']);
            if(!$sucursal){
                $this->msgError = "Sucursal no tiene definido una ciudad, por favor revisar";
    	        return false;
            }
            
		    //DATOS DESTINO
			$destino['nombre']=$orden['usuario']['nombre'].' '.$orden['usuario']['apellido'];
			$destino['domicilio']=$orden['direccion']." ".$orden['referencia2'];
			$destino['codPostal']=$orden['destino']['codigo'];
		    $destino['numeroFiscal']=$orden['usuario']['num_documento'];
			$destino['telefono']=$orden['usuario']['telefono'];
		    
		    $peso = 0;
			$articulos = null;
		    foreach ($orden['detalle'] as $d) {
				if(trim($d['sku']) == ""){
					$nameProduct = $d['producto'];
					$this->msgError = "El producto $nameProduct no tiene un SKU definido, por favor revisar";
    	        	return false;
				}

				$articulos[] = array(
					"cantidad" => intval($d['cantidad']),
					"sku" => $d['sku']
				);
                $peso = $d['peso']+$peso;
            }
		    
		    //DATOS A ENVIAR
			$data['cuentaCliente'] = $this->idCliente;
			$data['ordenCliente'] = $orden['id'];
			$data['destino'] = $destino;
			$data['articulos'] = $articulos;
			$json = json_encode($data);
            
			$ch = curl_init($this->URL."&o=nuevaNotaPedido");
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
		    //file_put_contents("LogsInlog.log", PHP_EOL . $json . PHP_EOL . $response, FILE_APPEND);
		    
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
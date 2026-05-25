<?php
class cl_contifico
{
	public $API;
	var $URL = "https://api.contifico.com/sistema/api/v1";
	var $session;
	var $cod_empresa;
	var $pos, $ambiente, $categoria;
	var $msgError="";
	var $permiteFacturar = 0;
	var $cod_contifico_empresa;

	public function __construct() {
		$this->cod_empresa = cod_empresa;
		$this->getCredentials();
	}

	public function getInfoBySucursal($cod_sucursal) {
		$query = "SELECT *
					FROM tb_contifico_sucursal cs, tb_contifico_empresa ce, tb_contifico_empresa_postokens cep
					WHERE cs.cod_contifico_empresa = ce.cod_contifico_empresa
					AND cs.cod_contifico_empresa = cep.cod_contifico_empresa
					AND cs.cod_sucursal = $cod_sucursal";
		return Conexion::buscarRegistro($query);
	}

	public function getCredentials(){
		$query = "SELECT * FROM tb_contifico_empresa WHERE estado='A' AND cod_empresa = ".cod_empresa;
		$resp = Conexion::buscarRegistro($query);
		if($resp){
			$this->API = $resp['api'];
			$this->cod_contifico_empresa = $resp['cod_contifico_empresa'];
			$this->categoria = $resp['categoria'];
			$this->ambiente = $resp['ambiente'];
			$this->permiteFacturar = $resp['facturar'];
		}
	}
	
	public function getPoscode($cod_sucursal){
		$query = "SELECT p.pos, p.emisor, p.ptoemision, p.secuencial
				FROM tb_contifico_empresa_postokens p, tb_contifico_sucursal s
				WHERE p.cod_postoken = s.cod_postoken
				AND s.cod_sucursal = $cod_sucursal
				AND p.ambiente = '$this->ambiente'";
		$resp = Conexion::buscarRegistro($query);
		if($resp){
			$resp["num_factura"] = $resp["emisor"] . "-" . $resp["ptoemision"] . "-" . str_pad($resp['secuencial'], 9, "0", STR_PAD_LEFT);
		}
	    return $resp;
	}

	/*ORDENES*/
	public function getDocumentosByCedula($cedula){
		if($this->API == ""){
	        $this->msgError = "Empresa no tiene configurado Contifico";
	        return false;
	    }

		$fecha = date("d/m/Y", strtotime(fecha()));
		$ch = curl_init($this->URL."/registro/documento/?tipo_registro=CLI&persona_identificacion=$cedula&fecha_emision=$fecha&tipo=FAC");
		$json = NULL;
		$headers = array();
		$headers[] = 'Content-Type: application/json';
		$headers[] = 'Authorization: '.$this->API;
		
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");                                                                     
		curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
		curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);      
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);   
		
		$response = curl_exec($ch);
	    if($response === false){
            $this->msgError = "Curl error: " . curl_error($ch);
            return false;
        }else{
            $respCurl = curl_getinfo($ch);
            if($respCurl['http_code'] === 500){
                $this->msgError .= "Curl httpcode error: ".$respCurl['http_code'];
                return false;
            }
        }
        
		curl_close($ch);
	    return json_decode($response,true);
	}

	public function saveOrden($orden, $cod_usuario){
		extract($orden);
		$cod_empresa = cod_empresa;
		$cod_sucursal = getFirstSucursal();
		$fecha = fecha();

		$subtotal = $subtotal_0 + $subtotal_12;
		$query = "INSERT INTO tb_orden_cabecera(cod_empresa, cod_sucursal, cod_usuario, fecha, subtotal, descuento, envio, iva, total, is_envio, pago, estado, medio_compra) ";
		$query.= "VALUES($cod_empresa, $cod_sucursal, $cod_usuario, '$fecha', $subtotal, 0, 0, $iva, $total, 0, 'E', 'CREADA', 'CONTIFICO')";
		if(Conexion::ejecutar($query,NULL)){
			$cod_orden = Conexion::lastId();
			$queryPunto = "INSERT INTO tb_orden_puntos(cod_orden) VALUES($cod_orden)";
			Conexion::ejecutar($queryPunto,NULL);

			$queryPunto = "INSERT INTO tb_orden_runfood(cod_orden,id) VALUES($cod_orden, '$id')";
			Conexion::ejecutar($queryPunto,NULL);

			foreach($cobros as $key => $pago){
				$tipo = $this->getFormaPago($pago['forma_cobro']);
				$monto = $pago['monto'];
				$queryPago = "INSERT INTO tb_orden_pagos(cod_orden, forma_pago, monto)
							VALUES($cod_orden, '$tipo', $monto)";
				Conexion::ejecutar($queryPago,NULL);
			}
			return true;
		}
		else{
			return false;
		}
	}

	function getFormaPago($forma){
		if($forma == "CAJA" || $forma == "EF"){
			return "E";
		}
		if($forma == "TC"){
			return "T";
		}
		if($forma == "PUNTOS"){
			return "P";
		}
	}

    /*PRODUCTOS*/
	public function LstProductos(){
		$ch = curl_init($this->URL."/producto/");
		$json = NULL;
		$headers = array();
		$headers[] = 'Content-Type: application/json';
		$headers[] = 'Authorization: '.$this->API;
		
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");                                                                     
		curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
		curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);      
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);   
		$response = curl_exec($ch);
		curl_close($ch);
		return $response;
		
	}

	public function GetProducto($id){
		$curl = curl_init();

		curl_setopt_array($curl, array(
		CURLOPT_URL => $this->URL."/producto/".$id,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => "",
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 0,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => "GET",
		CURLOPT_HTTPHEADER => array(
			"Authorization:".$this->API
		),
		));

		$response = curl_exec($curl);

		curl_close($curl);
		return json_decode($response,true);
	}

	public function CreateProducto($iva, $pvp, $nombre, $id){
	    $producto['codigo_barra'] = NULL;
	    $producto['porcentaje_iva'] = $iva;
	    $producto['categoria_id'] = $this->categoria;
	    $producto['minimo'] = $pvp;
	    $producto['pvp2'] = $pvp;
	    $producto['pvp3'] = $pvp;
	    $producto['pvp1'] = $pvp;
	    $producto['pvp_manual'] = false;
	    $producto['descripcion'] = $nombre;
	    $producto['nombre'] = $nombre;
	    $producto['codigo'] = $id;
	    $producto['estado'] = "A";
	    $json = json_encode($producto);
	    
	    $ch = curl_init($this->URL."/producto/");
	    $headers = array();
	    $headers[] = 'Content-Type: application/json';
	    $headers[] = 'Authorization: '.$this->API;
		
	    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
	    curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
	    curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);      
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);   
	    $response = curl_exec($ch);
		curl_close($ch);
	    return json_decode($response,true);
	}
	
	/*DOCUMENTOS*/
	public function CreateFactura($esquema){
	    if($this->API == ""){
	        $this->msgError = "Empresa no tiene configurado Contifico";
	        return false;
	    }
	    $json = json_encode($esquema);

		$ch = curl_init($this->URL."/documento/");
	    $headers = array();
	    $headers[] = 'Content-Type: application/json';
	    $headers[] = 'Authorization: '.$this->API; //<-- Key Contifico.
		
	    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
	    curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
	    curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);      
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);   
	    $response = curl_exec($ch);
	    if($response === false){
            $this->msgError = "Curl error: " . curl_error($ch);
            return false;
        }else{
            $respCurl = curl_getinfo($ch);
            if($respCurl['http_code'] === 500){
                $this->msgError .= "Curl httpcode error: ".$respCurl['http_code'];
                return false;
            }
        }
        
		curl_close($ch);
		//$response["esquemaa"] = $esquema;
	    return json_decode($response,true);
	}

	public function incrementSecuencial($cod_sucursal, $tipo) {
		$secuencial = " SET p.secuencial = p.secuencial + 1 ";
		if($tipo == "DNA")
			$secuencial = "SET p.secuencial_dna = p.secuencial_dna + 1";
		$query = "UPDATE tb_contifico_empresa_postokens p, tb_contifico_sucursal s
				$secuencial
				WHERE p.cod_postoken = s.cod_postoken
				AND s.cod_sucursal = $cod_sucursal";
		return Conexion::ejecutar($query,NULL);		
	}

	public function EditFactura($esquema){
	    $json = json_encode($esquema);

		$ch = curl_init($this->URL."/documento/");
	    $headers = array();
	    $headers[] = 'Content-Type: application/json';
	    $headers[] = 'Authorization: '.$this->API; //<-- Key Contifico.
		
	    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");                                                                     
	    curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
	    curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);      
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);   
	    $response = curl_exec($ch);
	    if($response === false){
            $this->msgError = "Curl error: " . curl_error($ch);
            return false;
        }else{
            $respCurl = curl_getinfo($ch);
            if($respCurl['http_code'] === 500){
                $this->msgError .= "Curl httpcode error: ".$respCurl['http_code'];
                return false;
            }
        }
        
		curl_close($ch);
	    return json_decode($response,true);
	}

	function getSucursalContifico($cod_sucursal) {
		$query = "SELECT *
					FROM tb_contifico_sucursal
					WHERE cod_sucursal = $cod_sucursal";
		return Conexion::buscarRegistro($query);
	}

	function setInventario($jsonString) {
		if($this->API == ""){
	        $this->msgError = "Empresa no tiene configurado Contifico";
	        return false;
	    }
	    
		$json = str_replace("\\", "", json_encode($jsonString));
		
		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL => $this->URL . "/movimiento-inventario/",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => 'POST',
			CURLOPT_POSTFIELDS => $json,
			CURLOPT_HTTPHEADER => array(
				'Authorization: ' . $this->API,
				'Content-Type: application/json'
			),
		));

		$response = curl_exec($curl);

		curl_close($curl);
		return json_decode($response,true);
	}

	public function saveErrorFactura($cod_orden, $motivo){
		$fecha = fecha();
		$query = "INSERT INTO tb_orden_errores
					SET
						cod_orden = $cod_orden,
						tipo = 'FACTURA',
						proveedor = 'CONTIFICO',
						motivo = '$motivo',
						fecha = '$fecha'";
		return Conexion::ejecutar($query, null);
	}

	public function sendToSRI($id) {
		if($this->API == ""){
	        $this->msgError = "Empresa no tiene configurado Contifico";
	        return false;
	    }

		$ch = curl_init($this->URL."/documento/$id/sri/");
	    $headers = array();
	    $headers[] = 'Content-Type: application/json';
	    $headers[] = 'Authorization: '.$this->API; //<-- Key Contifico.
		
	    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");                                                                     
	    curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);      
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);   
	    $response = curl_exec($ch);

		curl_close($ch);
	    return json_decode($response,true);
	}
}
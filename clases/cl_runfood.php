<?php
class cl_runfood
{
    public $URL = "";
    public $userId = "";
    public $apiKey = "";
    public $msgError = "";
	public $cod_empresa;

    public function __construct(){
        $this->cod_empresa = cod_empresa;
    }

    public function getCredentials(){

    }

    public function getSucursal($cod_sucursal){
        $cod_empresa = $this->cod_empresa;
        $query = "SELECT s.cod_sucursal, s.nombre, s.direccion, rs.cod_runfood_sucursal, rs.dominio, rs.usuario_id, rs.api_key, rs.facturar, rs.tipo_documento
                FROM tb_sucursales s
                INNER JOIN tb_runfood_sucursal rs ON s.cod_sucursal = rs.cod_sucursal
                WHERE s.cod_sucursal = $cod_sucursal
                AND s.estado IN ('A', 'I')";
        $sucursal = Conexion::buscarRegistro($query);
        if($sucursal){
            $this->URL = $sucursal['dominio'];
            $this->userId = $sucursal['usuario_id'];
            $this->apiKey = $sucursal['api_key'];
        }
        return $sucursal;
    }

    public function getAllProductsByOffices($cod_sucursal){
        global $session;
        $cod_empresa = $this->cod_empresa;
        $dir = url_sistema.'assets/empresas/'.$session['alias'].'/';
        $query = "SELECT p.cod_producto, p.nombre, p.precio, p.image_min, p.cod_producto_padre, pf.id, pf.name_in_contifico, pf.cod_sistema_facturacion 
                FROM tb_productos p
                LEFT JOIN tb_productos_facturacion pf ON p.cod_producto = pf.cod_producto AND pf.cod_contifico_empresa = $cod_sucursal
                WHERE p.cod_empresa = $cod_empresa
                AND p.estado IN ('A', 'I')";
        $resp = Conexion::buscarVariosRegistro($query);
        foreach($resp as $key => $item){
            $resp[$key]['image_min'] = $dir.$item['image_min'];
        }
        return $resp;
    }

    public function sendInvoice($data){
		$ch = curl_init($this->URL."/PEDIDO/INSERT");
		$json = NULL;
		$headers = array();
		$headers[] = 'Content-Type: application/json';
		
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
		curl_setopt($ch, CURLOPT_POSTFIELDS, $this->armarTrama($data));
		curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);      
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);   
		$response = curl_exec($ch);

        
        $msg = "";
        if($this->curlErrors($ch, $response, $msg)){
            curl_close($ch);
		    return json_decode($response,true);
        }else{
            $this->msgError = $msg;
            return false;
        }
	}
	
    public function revertInvoice($id, $motivo){
        $data = [
            "id" => $id,
            "idUsuario" => $this->userId,
            "motivo" => $motivo
        ];
		$ch = curl_init($this->URL."/PEDIDO/DELETE");
		$json = NULL;
		$headers = array();
		$headers[] = 'Content-Type: application/json';
		
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data, JSON_NUMERIC_CHECK));
		curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);      
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);   
		$response = curl_exec($ch);

        
        $msg = "";
        if($this->curlErrors($ch, $response, $msg)){
            curl_close($ch);
		    return json_decode($response,true);
        }else{
            $this->msgError = $msg;
            return false;
        }
	}

    /** Nueva API de Runfood: POST /orders, auth por header X-Api-Key (no más wrapping de tablet/usuario). */
    public function createOrder($data){
        $ch = curl_init($this->URL . "/orders");
        $headers = [
            'Content-Type: application/json',
            'X-Api-Key: ' . $this->apiKey,
        ];

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data, JSON_NUMERIC_CHECK));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);

        $info = curl_getinfo($ch);
        if ($info['http_code'] == 409) {
            // external_id ya existe: Runfood devuelve el id del pedido existente en vez de duplicar
            curl_close($ch);
            return json_decode($response, true);
        }

        $msg = "";
        if ($this->curlErrors($ch, $response, $msg)) {
            curl_close($ch);
            return json_decode($response, true);
        } else {
            $this->msgError = $msg;
            curl_close($ch);
            return false;
        }
    }

    /** Nueva API de Runfood: DELETE /orders/{id}. Solo funciona mientras la orden esté en estado 'open'. */
    public function cancelOrder($id, $motivo = null){
        $ch = curl_init($this->URL . "/orders/$id");
        $headers = [
            'Content-Type: application/json',
            'X-Api-Key: ' . $this->apiKey,
        ];

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        if ($motivo) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(["reason" => $motivo]));
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);

        $msg = "";
        if ($this->curlErrors($ch, $response, $msg)) {
            curl_close($ch);
            return $response === "" ? ['success' => true] : json_decode($response, true);
        } else {
            $this->msgError = $msg;
            curl_close($ch);
            return false;
        }
    }

    /** @deprecated API previa de Runfood (PEDIDO/INSERT con wrapping tablet/usuario). */
    public function armarTrama($data = null){
        $trama = null;
        if($data !== null){
            $trama['data'] = $data;
        }
        $trama['tablet']['usuario'] = $this->userId;
        return json_encode($trama, JSON_NUMERIC_CHECK);
    }

    public function curlErrors($ch, $response, &$msgError){
        if($response === false){
            $msgError = "Curl error: " . curl_error($ch);
            return false;
        }else{
            $info = curl_getinfo($ch);
            $httpcode = $info['http_code'];
            $codeInt = intval($httpcode / 100);
            if($codeInt === 2)
                return true;
            else{
                // Si el código es 500, intentar obtener el mensaje de error del cuerpo de la respuesta
                if ($httpcode == 500) {
                    // Decodificar la respuesta JSON para obtener el mensaje de error
                    $responseData = json_decode($response, true);
                    if (isset($responseData['message'])) {
                        // Si hay un mensaje en el JSON, lo asignamos
                        $msgError = "Error 500: " . $responseData['message'];
                    } else {
                        // Si no hay un mensaje en el JSON, simplemente devolvemos el código de error
                        $msgError = "Error 500: " . $httpcode;
                    }
                } else {
                    // En otros casos, devolver el código HTTP
                    $msgError = "Error " . $httpcode;
                }
                return false;
            }    
        }
    }
    
    
}
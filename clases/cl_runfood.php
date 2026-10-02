<?php
class cl_runfood
{
    public $URL = "";
    public $userId = "";
    public $apiKey = "";
    public $msgError = "";
	public $cod_empresa;
    /** HTTP code de la última llamada a la API nueva (0 si falló la conexión). */
    public $lastHttpCode = 0;
    /** Cuerpo decodificado de la última respuesta, también cuando es un error (400/409/422). */
    public $lastResponse = null;

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

    /**
     * Llamada genérica a la API nueva de Runfood (auth por header X-Api-Key).
     * Retorna el body decodificado si el HTTP es 2xx; false en otro caso (ver msgError,
     * lastHttpCode y lastResponse). Ojo: NO usar JSON_NUMERIC_CHECK — convierte SKUs como
     * "012312312" en números y pierden el cero inicial.
     */
    private function request($method, $path, $data = null){
        $ch = curl_init($this->URL . $path);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'X-Api-Key: ' . $this->apiKey,
        ]);
        if ($data !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION));
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        $response = curl_exec($ch);

        $this->lastHttpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $this->lastResponse = ($response !== false && $response !== "") ? json_decode($response, true) : null;

        if ($response === false) {
            $this->msgError = "Curl error: " . curl_error($ch);
            curl_close($ch);
            return false;
        }
        curl_close($ch);

        if (intval($this->lastHttpCode / 100) === 2) {
            return $this->lastResponse ?? ['success' => true];
        }

        // Runfood responde {"error": "codigo", "message": "detalle"}
        $this->msgError = "Error " . $this->lastHttpCode;
        if (is_array($this->lastResponse)) {
            $error = $this->lastResponse['error'] ?? '';
            $message = $this->lastResponse['message'] ?? '';
            $detalle = trim("$error: $message", " :");
            if ($detalle !== '') $this->msgError .= " - $detalle";
        }
        return false;
    }

    /**
     * POST /orders. En 409 (external_id repetido) retorna false, pero lastResponse trae
     * el id y status del pedido que ya existía: {"error":"duplicate_order","id":..,"status":..}
     */
    public function createOrder($data){
        return $this->request("POST", "/orders", $data);
    }

    public function getOrder($id){
        return $this->request("GET", "/orders/$id");
    }

    /** POST /orders/{id}/tabs/{tabId}/invoice — factura una cuenta completa de un pedido abierto. */
    public function invoiceTab($orderId, $tabId, $data){
        return $this->request("POST", "/orders/$orderId/tabs/$tabId/invoice", $data);
    }

    public function getOrderInvoices($orderId){
        return $this->request("GET", "/orders/$orderId/invoices");
    }

    /** DELETE /orders/{id}. Solo funciona mientras el pedido esté abierto (sin facturar). */
    public function cancelOrder($id, $motivo = null){
        return $this->request("DELETE", "/orders/$id", $motivo ? ["reason" => $motivo] : null);
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
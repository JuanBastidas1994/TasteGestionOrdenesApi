<?php
class cl_pedidosya {
    var $URL = "https://courier-api.pedidosya.com/v3";
    var $cod_empresa, $cod_sucursal, $ambiente, $token, $estado;
    var $isTest; 
    var $collectMoney;
    var $street, $city, $latitude, $longitude;

    public function __construct($pTokenSucursal=null, $pAmbiente="development") {
        $this->token = $pTokenSucursal;
        $this->ambiente = $pAmbiente;
        $this->isTest = false;
        $this->collectMoney = false;
        if($pAmbiente == "development")
            $this->isTest = true;
    }

    public function boolToString($value) {
        if($value)
            return "true";
        return "false";
    }

    public function getCoverage() {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->URL . 'estimate/coverage',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>'{
                "waypoints": [
                    {
                        "addressStreet": "'.$this->street.'",
                        "city": "'.$this->city.'",
                        "latitude": '.$this->latitude.',
                        "longitude": '.$this->longitude.',
                        "type": "PICK_UP"
                    }
                ]
            }',
            CURLOPT_HTTPHEADER => array(
                'Authorization: ' . $this->token,
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
                
        return json_decode($response);
    }

    public function createOrder($sucursal, $orden) {
        $fecha = fecha();

        $total = number_format($orden["total"], 2);
        $collectMoney = "";
        if($this->collectMoney) {
            $collectMoney = ", \"collectMoney\": " . $orden["collectMoney"];
        }
        $json = '{ 
                    "referenceId": "'.$orden['id'].'",
                    "isTest": '.$this->boolToString($this->isTest).',
                    "notificationMail": "'.$orden['usuario']['correo'].'",
                    "items": [
                        {
                            "type": "STANDARD",
                            "value": '.$total.',
                            "description": "Comida preparada",
                            "quantity": 1,
                            "volume": '.$orden["volumen"].',
                            "weight": '.$orden["peso"].'
                        }
                    ],
                    "waypoints": [
                        {
                            "type": "PICK_UP",
                            "addressStreet": "'.$sucursal['direccion'].'",
                            "addressAdditional": "'.$sucursal['direccion'].'",
                            "city": "'.$sucursal['ciudad'].'",
                            "latitude": '.$sucursal['latitud'].',
                            "longitude": '.$sucursal['longitud'].',
                            "phone": "'.$sucursal['telefono'].'",
                            "name": "'.$sucursal['nombre'].'",
                            "instructions": "Retirar en el local"
                        },
                        {
                            "type": "DROP_OFF",
                            "latitude": '.$orden['latitud'].',
                            "longitude": '.$orden['longitud'].',
                            "addressStreet": "'.$orden['direccion'].'",
                            "addressAdditional": "'.$orden['referencia2'].'",
                            "city": "'.$sucursal['ciudad'].'",
                            "phone": "'.$orden['usuario']['telefono'].'",
                            "name": "'.$orden['usuario']['nombre'].'",
                            "instructions": "'.$orden["instructionDelivery"].'"'.$collectMoney.'
                        }
                    ]
                }';

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->URL . '/shippings',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $json,
            CURLOPT_HTTPHEADER => array(
                'Authorization: ' . $this->token,
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);
        
        curl_close($curl);

        $textLog = PHP_EOL . PHP_EOL . "[$fecha] - " . str_replace([PHP_EOL, "    "], "", $json) . PHP_EOL . $response;

        file_put_contents("LogsPedidosYa.log", $textLog, FILE_APPEND);
        return json_decode($response, true);
    }

    public function cancelOrder($shippingId, $reason="Cancelada por el comercio") {
        $fecha = fecha();

        $json = '{
            "reasonText": "'.$reason.'"
        }';

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->URL . '/shippings/'.$shippingId.'/cancel',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $json,
            CURLOPT_HTTPHEADER => array(
                'Authorization: ' . $this->token,
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        $textLog = PHP_EOL . PHP_EOL . "[$fecha] - " . "endpoint " .$this->URL . '/shippings/'.$shippingId.'/cancel' . PHP_EOL . str_replace([PHP_EOL, "    "], "", $json) .  PHP_EOL . $response;
        
        file_put_contents("LogsPedidosYa.log", $textLog, FILE_APPEND);
        return json_decode($response, true);
    }

    public function tranckingOrder($shippingId) {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->URL . '/shippings/'.$shippingId.'/tracking',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Authorization: ' . $this->token
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        
        return json_decode($response, true);
    }
    
    public function setOfficeToken(&$cod_pedidosya_sucursal) {
        $query = "INSERT INTO tb_pedidosya_sucursales
                    SET cod_empresa = $this->cod_empresa,
                    cod_sucursal = $this->cod_sucursal,
                    ambiente = '$this->ambiente',
                    token = '$this->token',
                    estado = 'A'";
        $resp = Conexion::ejecutar($query, null);
        if($resp)
            $cod_pedidosya_sucursal = Conexion::lastId();
        return $resp;
    }

    public function updateOfficeToken($cod_pedidosya_sucursal) {
        $query = "UPDATE tb_pedidosya_sucursales
                    SET token = '$this->token'
                    WHERE cod_pedidosya_sucursal = $cod_pedidosya_sucursal";
        return Conexion::ejecutar($query, null);
    }

    public function getOfficeEnvironment($cod_sucursal, $ambiente) {
        $query = "SELECT * 
                    FROM tb_pedidosya_sucursales
                    WHERE ambiente = '$ambiente'
                    AND cod_sucursal = $cod_sucursal";
        return Conexion::buscarRegistro($query);
    }

    function shipping_status($urlWebhook) {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->URL . '/webhooks-configuration',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'PUT',
            CURLOPT_POSTFIELDS =>'{
                "webhooksConfiguration": [
                    {
                        "isTest": '.$this->boolToString($this->isTest).',
                        "topic": "SHIPPING_STATUS",
                        "notificationType": "WEBHOOK",
                        "urls": [
                            {
                                "url": "'.$urlWebhook.'",
                                "authorizationKey": ""
                            }
                        ]
                    }
                ]
            }',
            CURLOPT_HTTPHEADER => array(
                'Authorization: ' . $this->token,
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        return json_decode($response, true);
    }
}
?>
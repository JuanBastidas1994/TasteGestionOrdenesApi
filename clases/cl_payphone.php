<?php

class cl_payphone
{
    var $URL = "";
    var $isInitialized = false;
    var $identificador = "";
    var $token = "";
    var $cod_empresa = 0;
    
    public function __construct($pcod_empresa=null)
    {
        $this->cod_empresa = $pcod_empresa;
        $this->getTokens();
    }
    
    public function getTokens(){
        $query =  "SELECT * 
                    FROM tb_empresa_payphone 
                    WHERE cod_empresa = " . $this->cod_empresa;		
        $resp = Conexion::buscarRegistro($query);
        if($resp){
            $this->identificador = $resp["identificador"];
            $this->token = $resp["token"];
            $this->isInitialized = true;
            return;
        }
        $this->isInitialized = false;
    }

    public function approvedPayment($payPhoneId, $payPhoneClientTransactionId){
        //Preparar JSON de llamada
        $data_array =   array(
                            "id"=> (int)$payPhoneId,
                            "clientTxId"=>$payPhoneClientTransactionId 
                        );
        
        $data = json_encode($data_array);
        
        //Iniciar Llamada
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, "https://pay.payphonetodoesposible.com/api/button/V2/Confirm");
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt_array($curl, array(
        CURLOPT_HTTPHEADER => array(
        "Authorization: Bearer ". $this->token, "Content-Type:application/json"),
        ));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($curl);
        curl_close($curl);
        
        //En la variable result obtienes todos los parámetros de respuesta
        $return["respuesta_payphone"] = json_decode($result, true);
        return $return;
    }

    function existPayment($idPayphone, $idClientPayphone){
        $query = "SELECT *
                    FROM tb_orden_pagos
                    WHERE observacion = '$idPayphone'
                    AND observacion2 = '$idClientPayphone'";
        return Conexion::buscarRegistro($query);
    }

    function refund($idPayphone){
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://pay.payphonetodoesposible.com/api/reverse',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS =>'{
                "id": '.$idPayphone.'
            }',
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer ' . $this->token,
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        $return["respuesta_payphone"] = json_decode($response, true);
        return $return;
    }
}

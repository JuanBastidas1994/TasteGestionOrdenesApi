<?php
    class cl_facturas{
        var $cod_empresa;
        var $API, $pos, $ambiente, $categoria;

        public function __construct(){
            $this->cod_empresa = cod_empresa;
		    $this->getCredentials();
        }

        public function getCredentials(){
            $query = "SELECT * FROM tb_contifico_empresa WHERE estado='A' AND cod_empresa = ".cod_empresa;
            $resp = Conexion::buscarRegistro($query);
            if($resp){
                $this->API = $resp['api'];
                $this->categoria = $resp['categoria'];
                $this->ambiente = $resp['ambiente'];
            }
        }

        public function lista(){
            $query = "SELECT * 
                        FROM tb_sucursales 
                        WHERE cod_empresa = ".cod_empresa."
                        AND estado = 'A'";
            return Conexion::buscarVariosRegistro($query);
        }
        
        public function get($cod_orden){
            $query = "SELECT * 
                        FROM tb_orden_cabecera 
                        WHERE cod_orden = $cod_orden";
            $factura = Conexion::buscarRegistro($query);
            if($factura){
                $factura['detalle'] = $this->details($cod_orden);
                $factura['pagos'] = $this->payments($cod_orden);
            }
            return $factura;
        }
        
        public function details($cod_orden){
            $query = "SELECT fd.*, p.nombre, p.cobra_iva, p.precio_no_tax 
                        FROM tb_orden_detalle fd
                        INNER JOIN tb_productos p ON fd.cod_producto = p.cod_producto
                        WHERE cod_orden = $cod_orden";
            return Conexion::buscarVariosRegistro($query);
        }
        
        public function payments($cod_orden){
            $query = "SELECT * 
                        FROM tb_orden_pagos 
                        WHERE cod_orden = $cod_orden";
            return Conexion::buscarVariosRegistro($query);
        }

        public function lastPayment($cod_orden){
            $query = "SELECT * FROM tb_orden_pagos 
                    WHERE cod_orden = $cod_orden 
                    ORDER BY fecha_create DESC LIMIT 0,1";
            return Conexion::buscarRegistro($query);
        }
    }
?>
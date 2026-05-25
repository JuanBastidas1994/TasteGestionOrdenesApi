<?php

class cl_couriers
{
	public function __construct()
	{
	}

	public function lista(){
		$query = "SELECT * FROM tb_courier WHERE estado = 'A'";
		return Conexion::buscarVariosRegistro($query);
	}


	public function get($cod_courier){
		$query = "SELECT cod_courier as id, nombre, imagen FROM tb_courier WHERE cod_courier = ".$cod_courier;
		return Conexion::buscarRegistro($query);
	}
	
	public function getFlota($cod_orden){
		$query = "SELECT of.cod_flota as id, e.nombre, CONCAT(e.alias,'/',e.logo) as imagen 
                    FROM tb_ordenes_flota of
                    INNER JOIN tb_empresas e ON e.cod_empresa = of.cod_flota AND e.estado = 'A'
                    WHERE of.cod_orden = $cod_orden";
		$flota = Conexion::buscarRegistro($query);
        if($flota){
            $flota['imagen'] = url_business_assets.$flota['imagen'];
        }
        return $flota;
	}


    //-----------------TOKENS------------------------
    public function getTokensGacela($cod_sucursal){
        $query = "SELECT * 
                    FROM tb_gacela_sucursal gs 
                    WHERE gs.cod_sucursal = $cod_sucursal 
                    AND gs.estado = 'A' LIMIT 0,1";
        return Conexion::buscarRegistro($query);            
    }

    public function getTokensPicker($cod_sucursal){
        $query = "SELECT * FROM tb_picker_sucursal
                    WHERE cod_sucursal = $cod_sucursal 
                    AND estado = 'A' LIMIT 0,1";
        return Conexion::buscarRegistro($query);
    }

    public function getTokensLaar($cod_sucursal){
        $query = "SELECT * FROM tb_laar_sucursal 
                WHERE cod_sucursal = $cod_sucursal
                AND estado = 'A' limit 0,1";
        return Conexion::buscarRegistro($query);
    }

    public function getTokensInlog($cod_sucursal){
        $query = "SELECT * FROM tb_inlog_sucursal 
                WHERE cod_sucursal = $cod_sucursal
                AND estado = 'A' limit 0,1";
        return Conexion::buscarRegistro($query);
    }
    
    public function getTokensPedidosYa($cod_sucursal){
        $query = "SELECT * 
                FROM tb_pedidosya_sucursales 
                WHERE cod_sucursal = $cod_sucursal
                AND estado = 'A' 
                LIMIT 0,1";
        return Conexion::buscarRegistro($query);
    }
}
?>
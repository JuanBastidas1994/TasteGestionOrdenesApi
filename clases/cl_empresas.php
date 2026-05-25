<?php

class cl_empresas
{
		public $cod_usuario, $cod_empresa, $cod_rol, $nombre, $apellido, $imagen, $correo, $usuario, $password, $fecha_nacimiento, $estado;
		
		public function __construct($pcod_usuario=null)
		{
			if($pcod_usuario != null)
				$this->cod_usuario = $pcod_usuario;
		}

		public function get(){
			$query = "SELECT * FROM tb_empresas WHERE cod_empresa = ".cod_empresa;
			$resp = Conexion::buscarRegistro($query);
			return $resp;
		}

		public function getByCode($code){
			$query = "SELECT * FROM tb_empresas WHERE cod_empresa = $code";
			$resp = Conexion::buscarRegistro($query);
			return $resp;
		}

		public function getByAlias($alias){
			$query = "SELECT * FROM tb_empresas WHERE alias = '$alias'";
			$resp = Conexion::buscarRegistro($query);
			return $resp;
		}

		public function getByApiKey($api){
			$query = "SELECT * FROM tb_empresas WHERE api_key = '$api' AND estado = 'A'";
			$resp = Conexion::buscarRegistro($query);
			return $resp;
		}
		
		public function getProgramar(){
		    $query = "SELECT programar_pedido, cant_dias_programar_pedido as dias FROM tb_empresas WHERE cod_empresa = ".cod_empresa; 
		    $resp = Conexion::buscarRegistro($query);
			return $resp;
		}

		public function getFidelizacion(){
			$query = "SELECT * FROM  tb_empresa_fidelizacion_puntos WHERE cod_empresa = ".cod_empresa;
			$resp = Conexion::buscarRegistro($query);
			return $resp;
		}

		public function getFidelizacionById($id){
			$query = "SELECT * FROM  tb_empresa_fidelizacion_puntos WHERE cod_empresa = $id";
			$resp = Conexion::buscarRegistro($query);
			return $resp;
		}

		public function getNiveles(){
			$cod_empresa = cod_empresa;
			$query = "SELECT nombre, punto_inicial, punto_final, dinero_x_punto, posicion FROM tb_niveles WHERE cod_empresa = $cod_empresa ORDER BY posicion";
			$resp = Conexion::buscarVariosRegistro($query);
			return $resp;
		}

		public function getRedesSociales(){
			$cod_empresa = cod_empresa;
			$query = "SELECT r.codigo, r.nombre, r.icono, er.descripcion
                        FROM tb_empresa_red_social er, tb_red_social r
                        WHERE er.cod_red_social = r.cod_red
                        AND er.cod_empresa = $cod_empresa";
			$resp = Conexion::buscarVariosRegistro($query);
			foreach($resp as $key=>$item)
				$resp[$key]['codigo'] = strtolower($item['codigo']);
			return $resp;
		}
		
		public function getIsEnvioGrabaIva(){
		    $query = "SELECT envio_grava_iva FROM tb_empresas WHERE cod_empresa = ".cod_empresa;
		    $resp = Conexion::buscarRegistro($query);
		    if($resp){
		        return $resp['envio_grava_iva'];
		    }else
		        return 1;
		}
		
		public function getFormasPagoEmpresa(){
            $query = "SELECT efp.cod_forma_pago, efp.descripcion, fp.descripcion as nombre
                        FROM tb_empresa_forma_pago efp, tb_formas_pago fp
                        WHERE efp.cod_forma_pago = fp.cod_forma_pago
                        AND efp.estado = 'A'
                        AND efp.cod_empresa = ".cod_empresa." order by efp.posicion ASC";
            $resp = Conexion::buscarVariosRegistro($query);
            return $resp;
        }
        
        public function getProveedorBotonPagos(){
            $query = "SELECT p.*
                    FROM tb_empresa_botonpagos eb, tb_proveedor_botonpagos p 
                    WHERE eb.cod_proveedor_botonpagos = p.cod_proveedor_botonpagos
                    AND eb.estado = 'A' AND eb.cod_empresa = ".cod_empresa;
            return Conexion::buscarRegistro($query);
        }
        
        public function getInfoDatafast(){
            $query = "SELECT * FROM tb_empresa_datafast WHERE cod_empresa = ".cod_empresa;
            $resp = Conexion::buscarRegistro($query);
            return $resp;
        }
        
        public function getTokensDatafast(){
            $query = "SELECT * FROM tb_empresa_datafast WHERE cod_empresa = ".cod_empresa;
            $resp = Conexion::buscarRegistro($query);
            return $resp;
        }
        
        public function getTokensPaymentez($cod_sucursal){
            $query = "SELECT * FROM tb_empresa_sucursal_paymentez WHERE cod_sucursal = $cod_sucursal";
            $resp = Conexion::buscarRegistro($query);
            if(!$resp){
                $query = "SELECT * FROM tb_empresa_paymentez WHERE cod_empresa = ".cod_empresa;
                $resp = Conexion::buscarRegistro($query);
            }
            return $resp;
        }

		public function getAppVersion($aplicacion){
			$query = "SELECT name, code, texto, obligatorio, aplicacion FROM tb_empresas_versiones_app 
					WHERE cod_empresa = ".cod_empresa."
					AND aplicacion = '$aplicacion'
					ORDER BY fecha_modificacion DESC LIMIT 0,1;";
			return Conexion::buscarRegistro($query);
		}
		
		public function getMotorizadosInternos(){
		    $query = "SELECT cod_usuario as id, CONCAT(nombre, ' ', apellido) as nombres, imagen, correo, telefono
		            FROM tb_usuarios 
		            WHERE cod_rol = 17 
		            AND estado = 'A' 
		            AND cod_empresa = ".cod_empresa;
		    return Conexion::buscarVariosRegistro($query);
		}
		
		//Permisos - Get Permisos por empresa
		public function getPermissions(){
		    $identificadores = [];
		    $x=0;
		    $query = "SELECT identificador FROM tb_permisos_empresas WHERE cod_empresa = ".cod_empresa;
		    $permisos = Conexion::buscarVariosRegistro($query);
		    foreach ($permisos as $key => $permiso) {
		        $identificadores[$x]=$permiso['identificador'];
		        $x++;
		    }
		    return $identificadores;
		}
		
		public function getPermisos($cod_empresa){
		    $query = "SELECT identificador FROM tb_permisos_empresas WHERE habilitado = 1 AND estado = 'A' AND cod_empresa = $cod_empresa";
		    return Conexion::buscarVariosRegistro($query);
		}
		
		public function isPermissionDesktopSystem($cod_empresa){
		    $query = "SELECT identificador FROM tb_permisos_empresas WHERE habilitado = 1 AND estado = 'A' AND identificador = 'SISTEMA_DESKTOP' AND cod_empresa = $cod_empresa";
		    return Conexion::buscarRegistro($query);
		}
		
		public function getPermiso($identifier){
			$query = "SELECT * FROM tb_permisos_empresas WHERE identificador = '$identifier' AND cod_empresa = ".cod_empresa;
			return Conexion::buscarRegistro($query);
		}
		
		public function getPermisoByBusiness($identifier, $business_id){
			$query = "SELECT * FROM tb_permisos_empresas WHERE identificador = '$identifier' AND cod_empresa = ".$business_id;
			return Conexion::buscarRegistro($query);
		}
}
?>
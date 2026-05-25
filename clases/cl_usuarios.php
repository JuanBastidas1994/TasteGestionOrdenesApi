<?php

class cl_usuarios
{
		public $cod_usuario, $cod_empresa, $cod_rol, $nombre, $apellido, $telefono, $imagen, $correo, $usuario, $password, $fecha_nacimiento, $estado, $num_documento;
		
		public function __construct($pcod_usuario=null)
		{
			if($pcod_usuario != null)
				$this->cod_usuario = $pcod_usuario;
		}

		public function direcciones($cod_usuario){
			$query = "SELECT cod_usuario_direccion as id, nombre, direccion, referencia, latitud, longitud 
			FROM tb_usuario_direcciones WHERE cod_usuario =".$cod_usuario;
            $resp = Conexion::buscarVariosRegistro($query);
            return $resp;
		}

		public function save_direcciones($cod_usuario, $nombre, $direccion, $lat, $lon, $referencia = ""){
			$query = "INSERT INTO tb_usuario_direcciones(cod_usuario, nombre, direccion, referencia, latitud, longitud)";
			$query.= "VALUES($cod_usuario, '$nombre', '$direccion', '$referencia', '$lat', '$lon')";
			return Conexion::ejecutar($query,NULL);
		}

		public function get_direccion($id){
			$query = "SELECT * FROM tb_usuario_direcciones WHERE cod_usuario_direccion = $id";
            $resp = Conexion::buscarVariosRegistro($query);
            return $resp;
		}

		public function delete_direccion($id){
			$query = "DELETE FROM tb_usuario_direcciones WHERE cod_usuario_direccion = $id";
			return Conexion::ejecutar($query,NULL);
		}

		public function Login($usuario, $password)
		{
			$query = "SELECT cod_usuario as id, nombre, apellido, num_documento, correo, direccion, telefono, fecha_nacimiento
					FROM tb_usuarios u
					WHERE u.usuario = '$usuario' 
					AND u.password = MD5('$password') 
					AND u.estado = 'A'
					AND u.cod_rol = 4
					AND u.cod_empresa = ".cod_empresa;
			$return = Conexion::buscarRegistro($query);
			return $return;
		}
		
		public function LoginWithBusiness($usuario, $password)
		{
			$query = "SELECT u.cod_usuario as id, concat(u.nombre,' ',u.apellido) as nombres, u.correo, u.usuario, u.estado, u.imagen, u.cod_rol, u.cod_sucursal, 
					    e.cod_empresa, e.nombre as empresa, e.fecha_caducidad, e.logo, e.api_key as apikey, e.alias, e.color
    					FROM tb_usuarios u, tb_empresas e 
    					WHERE u.cod_empresa = e.cod_empresa 
    					AND u.usuario = '$usuario' 
    					and u.password = MD5('$password') 
    					AND u.estado = 'A' 
    					AND u.cod_rol IN(2,3)";
			$resp = Conexion::buscarRegistro($query);
			if($resp){
			    $files = url_sistema.'assets/empresas/'.$resp['alias'].'/';
			    $resp['logo'] = $files.$resp['logo'];
			    $resp['imagen'] = $files.$resp['imagen'];
			}
			return $resp;
		}

		public function usuarioDisponible($usuario){
			$query = "SELECT * FROM tb_usuarios WHERE usuario = '$usuario' AND estado IN('A','I') AND cod_empresa = ".cod_empresa;
			$row = Conexion::buscarRegistro($query, NULL);
			if($row)
				return false;
			else
				return true;
		}

		public function registro(&$id){
			$query = "INSERT INTO tb_usuarios(cod_empresa, cod_rol, nombre, apellido, telefono, correo, usuario, password, fecha_nacimiento, estado, num_documento)";
			$query.= "VALUES($this->cod_empresa, $this->cod_rol, '$this->nombre', '$this->apellido', '$this->telefono', '$this->correo', '$this->correo', MD5('$this->password'), '$this->fecha_nacimiento','A','$this->num_documento')";
			$resp = Conexion::ejecutar($query,NULL);
			if($resp)
			    $id = Conexion::lastId();
			
			return $resp;
		}

		public function get($cod_usuario){  //SE USA EN MUCHAS SERVICIOS QUE REQUIEREN CAMPOS NO VISIBLES PARA EL USUARIO
			$query = "SELECT * FROM tb_usuarios WHERE cod_usuario = $cod_usuario";
			$resp = Conexion::buscarRegistro($query);
			return $resp;
		}
		
		public function get2($cod_usuario){ //SE USA EN EL SERVICIO DE REGISTRO
			$query = "SELECT cod_usuario as id, nombre, apellido, num_documento, correo, direccion, telefono, fecha_nacimiento FROM tb_usuarios WHERE estado = 'A' AND cod_usuario = $cod_usuario";
			$resp = Conexion::buscarRegistro($query);
			return $resp;
		}
		
		public function getUserRegistrado($cod_usuario){
		    $query = "SELECT * FROM tb_usuarios WHERE cod_rol = 4 AND estado = 'A' AND cod_usuario = $cod_usuario";
			$resp = Conexion::buscarRegistro($query);
			return $resp;
		}
		
		public function getbyNumDocumento($num_documento){
			$query = "SELECT * FROM tb_usuarios 
					WHERE num_documento = '$num_documento' 
					AND cod_rol = 4 
					AND cod_empresa = ".cod_empresa;
			$resp = Conexion::buscarRegistro($query);
			return $resp;
		}

		public function getbyEmail($correo){
			$query = "SELECT * FROM tb_usuarios WHERE cod_rol = 4 AND correo = '$correo' AND estado IN('A','I') AND cod_empresa = ".cod_empresa;
			$resp = Conexion::buscarRegistro($query);
			return $resp;
		}
		
		public function getNumOrdersByStatus($cod_usuario){
		    $query = "SELECT ca.estado, count(ca.estado) as numero
                                  FROM tb_orden_cabecera ca
                                  WHERE ca.cod_usuario = $cod_usuario
                                  AND ca.cod_empresa = ".cod_empresa." 
                                  GROUP BY ca.estado
                                  ORDER BY numero DESC";
            return Conexion::buscarVariosRegistro($query);
		}
		
		public function getOrdenes($cod_usuario){
		    $query = "SELECT ca.cod_orden as id, ca.fecha, ca.total, ca.is_envio, ca.pago, ca.estado, s.nombre as sucursal
						FROM tb_orden_cabecera ca, tb_sucursales s
						WHERE ca.cod_sucursal = s.cod_sucursal
						AND ca.cod_usuario = $cod_usuario
						AND ca.cod_empresa = ".cod_empresa." ORDER BY ca.cod_orden DESC";
            $resp = Conexion::buscarVariosRegistro($query);
            foreach ($resp as $key => $item) {
    			$resp[$key]['fecha'] = fechaLatinoShort($item['fecha']);
    			$resp[$key]['hora'] = getHourToDateTime($item['fecha']);
    			$resp[$key]['tipo_envio'] = ($item['is_envio'] == 1) ? 'Delivery' : 'Pickup';
    		} 
    		return $resp;
		}

		public function set_password($cod_usuario, $password){
			$query = "UPDATE tb_usuarios SET password = MD5('$password') WHERE cod_usuario = $cod_usuario";
			return Conexion::ejecutar($query,NULL);
		}

		function set_numdocumento($cod_usuario, $cedula){
			$query = "UPDATE tb_usuarios SET num_documento = '$cedula' WHERE cod_usuario = $cod_usuario";
			return Conexion::ejecutar($query,NULL);
		}

		function set_telefono($cod_usuario, $telefono){
			$query = "UPDATE tb_usuarios SET telefono = '$telefono' WHERE cod_usuario = $cod_usuario";
			return Conexion::ejecutar($query,NULL);
		}

		public function getMotorizadosCercanos($lat, $lng, $cod_sucursal, $km){
			$cod_empresa = cod_empresa;
			$query = "SELECT
					cod_usuario as id, CONCAT(nombre, ' ', apellido) as nombres, imagen, correo, usuario, telefono, 
					latitud, longitud, is_active, fecha_ubicacion,
					(
						6378 * acos (
							cos ( radians($lat) )
							* cos( radians( latitud ) )
							* cos( radians( longitud ) - radians($lng) )
							+ sin ( radians($lat) )
							* sin( radians( latitud ) ) )
					) AS distance
				FROM tb_usuarios
				WHERE cod_empresa = $cod_empresa
				AND cod_sucursal IN(0, $cod_sucursal)
				AND estado = 'A'
				AND cod_rol = 17
				HAVING distance <= $km
				ORDER BY distance ASC";
				/* AND is_active = 1 */
			$resp = Conexion::buscarVariosRegistro($query);
            foreach ($resp as $key => $item) {
				$distance = $resp[$key]['distance'];
				$resp[$key]['color_badge'] = "success";
				$resp[$key]['distance_unit'] = "Km";
				if($resp[$key]['distance'] < 1) {
					$distance = $resp[$key]['distance'] * 1000;
					$resp[$key]['distance_unit'] = "m";
				}
				else if($resp[$key]['distance'] >= 1 && $resp[$key]['distance'] <= 5){
					$resp[$key]['color_badge'] = "warning";
					
				}
				else {
					$resp[$key]['color_badge'] = "danger";
				}
				$resp[$key]['distance'] = $distance;
    			$resp[$key]['imagen'] = url.$item['imagen'];
				$resp[$key]['pedidos'] = $this->getCantOrdenesMotorizado($item['id']);
    		} 
    		return $resp;
		}

		public function getCantOrdenesMotorizado($cod_usuario){
			$fecha = fecha_only();
			$query = "SELECT ca.estado, COUNT(*) as cantidad
			FROM tb_orden_cabecera ca
			INNER JOIN tb_motorizado_asignacion mo ON ca.cod_orden = mo.cod_orden 
			AND mo.cod_motorizado = $cod_usuario AND DATE(mo.fecha_asignacion) = '$fecha'
			WHERE ca.estado IN ('ASIGNADA','ENVIANDO')
			GROUP BY ca.estado";
			//return $query;
			$resp = Conexion::buscarVariosRegistro($query);
			foreach ($resp as $key => $item) {
				$icon = "truck";
				if($item['estado'] == "ASIGNADA")
					$icon = "archive";
				$resp[$key]['icon'] = $icon;
    		} 
    		return $resp;
		}
		
		function getAllMotorizados(){
		    $cod_empresa = cod_empresa;
			$query = "SELECT
					cod_usuario as id, CONCAT(nombre, ' ', apellido) as nombres, imagen, correo, usuario, telefono, 
					latitud, longitud, is_active, fecha_ubicacion
				FROM tb_usuarios
				WHERE cod_empresa = $cod_empresa
				AND estado = 'A'
				AND cod_rol = 17
				ORDER BY distance ASC";
			$resp = Conexion::buscarVariosRegistro($query);
            foreach ($resp as $key => $item) {
				$resp[$key]['pedidos'] = $this->getCantOrdenesMotorizado($item['id']);
    		} 
    		return $resp;
		}
		
		public function getPurchaseCode($code){
		    $fecha = fecha();
		    $query = "SELECT pc.*, u.cod_usuario, u.num_documento, u.telefono 
                        FROM tb_usuario_purchase_code pc
                        INNER JOIN tb_usuarios u ON pc.cod_usuario = u.cod_usuario AND u.estado = 'A'
                        WHERE pc.codigo  = '$code' 
                        AND pc.estado = 'CREADO'
                        AND pc.fecha_expiracion > '$fecha'";
			return Conexion::buscarRegistro($query);
		}
		
		public function getPurchaseCodeActive($code){
		    $fecha = fecha();
		    $query = "SELECT pc.*, u.cod_usuario, u.num_documento, u.telefono 
                        FROM tb_usuario_purchase_code pc
                        INNER JOIN tb_usuarios u ON pc.cod_usuario = u.cod_usuario AND u.estado = 'A' AND u.cod_empresa = ".cod_empresa."
                        WHERE pc.codigo  = '$code' 
                        AND pc.estado = 'CREADO'
                        AND pc.fecha_expiracion > '$fecha'";
			return Conexion::buscarRegistro($query);
		}
		
		public function unsubscribeCode($code, $cod_orden){
		    $query = "UPDATE tb_usuario_purchase_code SET estado='USADO', cod_orden=$cod_orden WHERE codigo='$code'";
		    return Conexion::ejecutar($query,NULL);
		}
		
		/*CUPONES*/
		public function getCuponesUser($cod_usuario){
		    $fecha = fecha();
		    $query = "SELECT c.titulo, c.imagen, c.descripcion, c.tipo, cu.fecha_caducidad 
                        FROM tb_cupones_usuarios cu
                        INNER JOIN tb_cupones c ON c.cod_cupon = cu.cod_cupon AND c.estado = 'A' AND c.cod_empresa = ".cod_empresa."
                        WHERE cu.cod_usuario = $cod_usuario
                        AND cu.fecha_caducidad >= '$fecha'
                        AND cu.estado = 'ACTIVO'
                        ORDER BY cu.fecha_caducidad";
			$resp = Conexion::buscarVariosRegistro($query);
			if($resp){
			    foreach ($resp as $key => $cupon) {
			        $resp[$key]['imagen'] = url.$cupon['imagen'];
                	$dias_restantes = diasRestantes($cupon['fecha_caducidad']);
                	$resp[$key]['dias_restantes'] = $dias_restantes;
                	
                	$texto_caducidad = "";
                	if($dias_restantes > 0 && $dias_restantes <= 10){
                	    $texto_caducidad = "Caduca en $dias_restantes día";
                	    if($dias_restantes > 1)
                	        $texto_caducidad .= "s";
                	}
                	if($dias_restantes == 0){
                	    $texto_caducidad = "Caduca hoy";    
                	}
                	$resp[$key]['texto_caducidad'] = $texto_caducidad;
			    }
			}
			return $resp;
		}
		
}
?>
<?php

class cl_sucursales
{
		var $cod_sucursal, $cod_empresa, $estado;
		
		public function __construct($pcod_sucursal=null)
		{
			if($pcod_sucursal != null)
				$this->cod_sucursal = $pcod_sucursal;
		}

		public function lista($tipo=""){
			$filter = "";
			if($tipo=="pickup")
				$filter = " AND s.pickup = 1";
				if($tipo=="delivery")
				$filter = " AND s.delivery = 1";
			$query = "SELECT s.*
				FROM tb_sucursales as s
				WHERE s.estado IN('A') 
				AND s.cod_empresa = ".cod_empresa.$filter;
            $resp = Conexion::buscarVariosRegistro($query);
            foreach ($resp as $key => $sucursales) {
                /*DISPONIBILIDAD*/
                $abierto = $this->disponibilidad($sucursales['cod_sucursal']);
                if($abierto){
                    $resp[$key]['hora_ini'] = $abierto['hora_ini'];
                    $resp[$key]['hora_fin'] = $abierto['hora_fin'];
                    $resp[$key]['abierto'] = true;
                }else    
					$resp[$key]['abierto'] = false;	
                
				$resp[$key]['image'] = url.$sucursales['image'];
            }
            return $resp;
		}
		
		public function get($cod_sucursal){
		    $query = "SELECT cod_sucursal as id, nombre, direccion, latitud, longitud, distancia_km, hora_ini, hora_fin, telefono, correo, image, delivery, pickup, programar_pedido, estado, cod_ciudad, grava_iva
		            FROM tb_sucursales 
					WHERE estado = 'A' 
					AND cod_sucursal = $cod_sucursal 
					AND cod_empresa = ".cod_empresa;
		    $resp = Conexion::buscarRegistro($query);
		    if($resp){
				$resp['image'] = url.$resp['image'];
		    }
		    return $resp;
		}

		public function getCouriers($cod_sucursal){
			$query = "SELECT sc.cod_courier as id, sc.prioridad, c.nombre as courier, c.tipo, c.imagen, sc.detalle, '0' as cod_flota
			FROM tb_sucursal_courier sc
			INNER JOIN tb_courier c ON sc.cod_courier = c.cod_courier AND c.estado = 'A'
			WHERE sc.cod_sucursal = $cod_sucursal
			AND sc.estado = 'A'
			ORDER BY sc.prioridad ASC";
			return Conexion::buscarVariosRegistro($query);
		}
		
		public function getFlotas($cod_sucursal){
			$query = "SELECT '101' as id, '10' as prioridad, e.nombre as courier, 'MOTO' as tipo, sf.cod_flota,
                CONCAT(e.alias,'/',e.logo) as imagen , '' as detalle
                FROM tb_sucursal_flota sf
                INNER JOIN tb_empresas e ON e.cod_empresa = sf.cod_flota AND e.estado = 'A'
                WHERE sf.cod_sucursal = $cod_sucursal";
			$flotas = Conexion::buscarVariosRegistro($query);
            foreach($flotas as $key => $flota){
                $flotas[$key]['imagen'] = url_business_assets.$flota['imagen'];
            }
            return $flotas;
		}
		
		public function getWithoutValidateBusiness($cod_sucursal){
		    $query = "SELECT cod_sucursal as id, nombre, direccion, latitud, longitud, distancia_km, hora_ini, hora_fin, telefono, correo, image, delivery, pickup, programar_pedido, estado, cod_ciudad, grava_iva, cod_empresa
		            FROM tb_sucursales 
					WHERE estado = 'A' 
					AND cod_sucursal = $cod_sucursal";
		    $resp = Conexion::buscarRegistro($query);
		    if($resp){
				$resp['image'] = url.$resp['image'];
		    }
		    return $resp;
		}

		public function listaCobertura($latitud, $longitud){
			$query = "SELECT
						  *, (
						    6378 * acos (
						      cos ( radians($latitud) )
						      * cos( radians( latitud ) )
						      * cos( radians( longitud ) - radians($longitud) )
						      + sin ( radians($latitud) )
						      * sin( radians( latitud ) )
						    )
						  ) AS distance
						FROM tb_sucursales
						WHERE cod_empresa = ".cod_empresa."
						AND estado = 'A'
						AND delivery = 1
						HAVING distance <= distancia_km
						ORDER BY distance";
			$data = Conexion::buscarVariosRegistro($query);
			foreach ($data as $key => $sucursal) {
				$data[$key]['image'] = url.$sucursal['image'];
				$data[$key]['distance'] = number_format($sucursal['distance'],3);
				$data[$key]['precio'] = number_format($this->getPrecio($sucursal['distance']),2);
				$data[$key]['metodo_cobertura'] = "LINEA_RECTA";

				/*DISPONIBILIDAD*/
                $abierto = $this->disponibilidad($sucursal['cod_sucursal']);
                if($abierto){
                    $data[$key]['hora_ini'] = $abierto['hora_ini'];
                    $data[$key]['hora_fin'] = $abierto['hora_fin'];
                    $data[$key]['abierto'] = true;
                }else    
                    $data[$key]['abierto'] = false;
			}
			return $data;
		}

		public function getPrecio($distancia){
			$query = "SELECT * FROM tb_empresa_costo_envio WHERE cod_empresa = ".cod_empresa;
			$data = Conexion::buscarRegistro($query);
			if($data){
				$base_km = floatval($data['base_km']);
				$base_dinero = floatval($data['base_dinero']);
				$adicional_km = floatval($data['adicional_km']);
				$distancia = floatval($distancia);

				if($distancia <= $base_km){
					return $base_dinero;
				}else{
					$kmExtras = $distancia - $base_km;
					return $base_dinero + ($kmExtras * $adicional_km);
				}
			}else{
				return 0;
			}
		}

		public function getConPrecio($cod_sucursal, $latitud, $longitud){
			$query = "SELECT
						  *, (
						    6378 * acos (
						      cos ( radians($latitud) )
						      * cos( radians( latitud ) )
						      * cos( radians( longitud ) - radians($longitud) )
						      + sin ( radians($latitud) )
						      * sin( radians( latitud ) )
						    )
						  ) AS distance
						FROM tb_sucursales
						WHERE cod_empresa = ".cod_empresa."
						AND estado = 'A'
						AND delivery = 1
						AND cod_sucursal = $cod_sucursal";
			$data = Conexion::buscarRegistro($query);
			if($data){
				$data['distance'] = number_format($data['distance'],3);
				$data['precio'] = number_format($this->getPrecio($data['distance']),2);
				
				/*DISPONIBILIDAD*/
                $abierto = $this->disponibilidad($data['cod_sucursal']);
                if($abierto){
                    $data['hora_ini'] = $abierto['hora_ini'];
                    $data['hora_fin'] = $abierto['hora_fin'];
                    $data['abierto'] = true;
                }else    
                    $data['abierto'] = false;
			}
			return $data;
		}

		public function getHorarios($cod_sucursal){
			$query = "SELECT dia, hora_ini, hora_fin 
			FROM tb_sucursal_disponibilidad 
			WHERE cod_sucursal = $cod_sucursal";
			$data = Conexion::buscarVariosRegistro($query);
			foreach ($data as $key => $sucursal) {
				$nombreWeek = str_replace(array("0","1","2","3","4","5","6"),array("Lunes","Martes","Miércoles","Jueves","Viernes","Sábado","Domingo"), $sucursal['dia']);
				$data[$key]['dia'] = $nombreWeek;
				$data[$key]['hora_ini'] = fechaToFormat($sucursal['hora_ini'], "H:i");
				$data[$key]['hora_fin'] = fechaToFormat($sucursal['hora_fin'], "H:i");
			}
			return $data;
		}
		
		public function disponibilidad($cod_sucursal, $fecha=""){
		    if($fecha == ""){
			    $fecha = fecha();
			    $hora = hora();
		    }else{
		        list($dia, $hora) = explode(' ', $fecha);
		    }    
		    
			$query = "SELECT * FROM tb_sucursal_festivos
					WHERE cod_sucursal = $cod_sucursal
					AND fecha_inicio <= '$fecha' 
					AND fecha_fin >= '$fecha'";
			$row = Conexion::buscarRegistro($query);
			if($row)
				return false;

			
			$dia = dayOfWeek($fecha) - 1;
			$query = "SELECT * FROM tb_sucursal_disponibilidad
					WHERE cod_sucursal = $cod_sucursal
					AND dia = $dia
					AND hora_ini <= '$hora'
					AND hora_fin >= '$hora'";
			$row = Conexion::buscarRegistro($query);
			if(!$row)
			    return false;
			return $row;
		}
		
		public function getHorarioFecha($cod_sucursal, $fecha=""){
		    if($fecha == "")
			    $fecha = fecha();
			
			 $dia = dayOfWeek($fecha) - 1;
			 $query = "SELECT dia, hora_ini, hora_fin FROM tb_sucursal_disponibilidad
					WHERE cod_sucursal = $cod_sucursal
					AND dia = $dia";
			 return Conexion::buscarRegistro($query);
		}
		
		public function datetimeDisponibilidad($cod_sucursal, $fecha, $hora){
		    $fecha = $fecha." ".$hora;
		    $query = "SELECT * FROM tb_sucursal_festivos
					WHERE cod_sucursal = $cod_sucursal
					AND fecha_inicio <= '$fecha' 
					AND fecha_fin >= '$fecha'";
			$row = Conexion::buscarRegistro($query);
			if(!$row)
			    return true;
			return false;
		}
		
		public function restriccionDisponibilidadBySucursal($cod_sucursal, $pfecha = ""){
		    $fecha = ($pfecha === "") ? fecha() : $pfecha;
		    $query = "SELECT * FROM tb_sucursal_festivos
					WHERE cod_sucursal = $cod_sucursal
					AND fecha_inicio <= '$fecha' 
					AND fecha_fin >= '$fecha'";
			return Conexion::buscarRegistro($query);
		}
		
		public function getAltaDemanda($cod_sucursal, $pfecha = ""){
		    $fecha = ($pfecha === "") ? fecha() : $pfecha;
		    $query = "SELECT * FROM tb_sucursal_alta_demanda
					WHERE cod_sucursal = $cod_sucursal
					AND fecha_inicio <= '$fecha' 
					AND fecha_fin >= '$fecha'";
			return Conexion::buscarRegistro($query);
		}


		/*FUNCIONES GOOGLE MAPS*/ 
		 public function getDistanciaRutaGoogle($suc_lat, $suc_lon, $des_lat, $des_lon){
			 $origen = "&origin=".$suc_lat.",".$suc_lon;
			 $destino = "&destination=".$des_lat.",".$des_lon; 
			 
			 $url = "https://maps.googleapis.com/maps/api/directions/json?mode=driving&key=AIzaSyAWo6DXlAmrqEiKiaEe9UyOGl3NJ208lI8".$origen.$destino;
			 //echo $url;
			 $ch = curl_init($url);
			 $headers = array();
			 $headers[] = 'Content-Type: application/json';
		   
			 curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
			 curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);
			 curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);   
			 $response = curl_exec($ch);
			 curl_close($ch);
			 $data = json_decode($response, true);
			 if(isset($data['routes'][0]['legs'])){
				 $legs = $data['routes'][0]['legs'];
				 if(count($legs)>0){
					 $resp['distancia'] = $legs[0]['distance']['value'];
					 $resp['tiempo'] = $legs[0]['duration']['text'];
					 return $resp;
				 }
				 else
					 return false;
			 }else{
				 return false;
			 }
		 }
		 
		 
		 public function crearRestriccion($cod_sucursal, $fecha_inicio, $fecha_fin){
		     $fecha = fecha_only();
		     $hora_ini = explode(" ", $fecha_inicio)[1];
		     $hora_fin = explode(" ", $fecha_fin)[1];
		     $query = "INSERT INTO tb_sucursal_festivos(cod_sucursal, fecha, hora_inicio, hora_fin, fecha_inicio, fecha_fin)
						VALUES($cod_sucursal, '$fecha', '$hora_ini', '$hora_fin', '$fecha_inicio', '$fecha_fin')";
			return Conexion::ejecutar($query, null);
		 }
		 
		 public function eliminarRestriccion($cod_sucursal_festivos){
			$query = "DELETE 
						FROM tb_sucursal_festivos 
						WHERE cod_sucursal_festivos = $cod_sucursal_festivos";
			return Conexion::ejecutar($query, null);
		}
		
		 public function crearAltaDemanda($cod_sucursal, $fecha_inicio, $fecha_fin){
		     $query = "INSERT INTO tb_sucursal_alta_demanda(cod_sucursal, fecha_inicio, fecha_fin)
						VALUES($cod_sucursal, '$fecha_inicio', '$fecha_fin')";
			return Conexion::ejecutar($query, null);
		 }
		 
		 public function eliminarAltaDemanda($id){
			$query = "DELETE  FROM tb_sucursal_alta_demanda  WHERE id = $id";
			return Conexion::ejecutar($query, null);
		}

		public function getBusiness($cod_sucursal){
			$query = "SELECT s.nombre as nom_sucursal, e.nombre as nom_empresa 
						FROM tb_sucursales s, tb_empresas e
						WHERE s.cod_empresa = e.cod_empresa
						AND s.cod_sucursal = $cod_sucursal";
			return Conexion::buscarRegistro($query);
		}

		public function getInfoByCiudad($cod_ciudad) {
			$query = "SELECT * 
						FROM tb_ciudades 
						WHERE cod_ciudad = $cod_ciudad";
			return Conexion::buscarRegistro($query);
		}
}
?>
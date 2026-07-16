<?php

class cl_ordenes
{
	public $percent_iva;
	public $sucursal_grava_iva = 1;

	public function __construct()
	{
	}

	public function listaGestionOrdenes($estado, $envio, $cod_sucursal){
	    $fecha = fecha_only();
		$cod_empresa = cod_empresa;
		$query = "SELECT ca.cod_orden as id, ca.cod_usuario, ca.cod_sucursal, ca.cod_courier, ca.fecha, ca.total, ca.is_envio, ca.is_programado, ca.hora_retiro, ca.pago, ca.referencia as direccion, ca.estado, ca.mesa_referencia
					, ca.order_token as token, u.nombre, u.correo, u.telefono, u.imagen
					FROM tb_orden_cabecera ca, tb_usuarios u
					WHERE ca.cod_usuario = u.cod_usuario
					AND ca.estado = '$estado'
					AND ca.cod_sucursal = $cod_sucursal
					AND ca.cod_empresa = $cod_empresa
					AND ((DATE(ca.fecha) = '$fecha' AND is_programado = 0) OR (DATE(ca.hora_retiro) = '$fecha' AND is_programado = 1))";

		if($estado !== "ENTRANTE"){	// SI NO ES ENTRANTE MOSTRAR ORDENES DEL DÍA DE HOY
			$fecha = fecha_only();
			//$query .= " AND DATE(ca.fecha) = '$fecha'";
		}

		if($envio == "delivery"){
			$query .= " AND ca.is_envio = 1
					ORDER BY ca.cod_orden DESC LIMIT 0, 150";
		}else if($envio == "pickup"){
			$query .= " AND ca.is_envio = 0
					AND (DATE_FORMAT(ca.hora_retiro,'%Y %m %d')=DATE_FORMAT(now(),'%Y %m %d')) ORDER BY TIME_FORMAT(ca.hora_retiro, '%H:%i') ASC";
		}else if($envio == "onsite"){
			$query .= " AND ca.is_envio = 2
					AND (DATE_FORMAT(ca.hora_retiro,'%Y %m %d')=DATE_FORMAT(now(),'%Y %m %d')) ORDER BY TIME_FORMAT(ca.hora_retiro, '%H:%i') ASC";
		}else{
			$query .= " ORDER BY ca.cod_orden DESC LIMIT 0, 150";
		}
		return Conexion::buscarVariosRegistro($query); 
	}
	
	public function listaForMap($cod_sucursal, $range){
	    $fecha = fecha_only();
	    $ahora = hora_only();
		$cod_empresa = cod_empresa;
		$query = "SELECT ca.cod_orden as id, ca.cod_usuario, ca.cod_sucursal, ca.cod_courier, ca.fecha, ca.total, ca.is_envio, ca.is_programado, ca.hora_retiro, ca.pago, ca.referencia as direccion, ca.estado
					, ca.order_token as token, u.nombre, u.correo, u.telefono, u.imagen, ca.latitud, ca.longitud, ca.is_express
					FROM tb_orden_cabecera ca, tb_usuarios u
					WHERE ca.cod_usuario = u.cod_usuario 
					AND ca.cod_empresa = $cod_empresa
					AND ca.is_envio = 1 ";
        
        if($range){
            if($range == 9){
                $datetime_ini = $fecha.' 09:00:00';
                $datetime_fin = $fecha.' 12:00:00';
            }else if($range == 12){
                $datetime_ini = $fecha.' 12:00:00';
                $datetime_fin = $fecha.' 15:00:00';
            }else if($range == 3){
                // $hora_ini = '00:00:00';
                if ($ahora >= '15:00:00') {
                    $datetime_ini = $fecha.' 15:00:00';
                    $datetime_fin = $fecha.' 23:59:59';
                }else if ($ahora < '09:00:00') {
                    $fecha_ini = date('Y-m-d', strtotime($fecha . ' -1 day'));
                    $datetime_ini = $fecha_ini.' 15:00:00';
                    $datetime_fin = $fecha.' 09:00:00';
                }else{
                    return [];
                }
            }
            $query .= "AND ca.fecha >= '$datetime_ini' 
                    AND ca.fecha < '$datetime_fin'";
        }
        
    
		return Conexion::buscarVariosRegistro($query); 
	}

	public function getOrden($cod_orden){
		$cod_empresa = cod_empresa;
		$query = "SELECT cod_orden as id, cod_usuario, cod_sucursal, cod_courier, fecha, estado, cod_descuento as cupon_descuento,
					subtotal, subtotal0, subtotal12, descuento, envio, envio_iva, iva, iva_porcentaje, service, total, is_envio, is_programado, hora_retiro, latitud, longitud, telefono, referencia as direccion, referencia2, pago,
					is_suelto, monto_suelto, medio_compra, observacion, order_token, is_altademanda, mesa_referencia
				FROM tb_orden_cabecera 
				WHERE cod_empresa = $cod_empresa 
				AND cod_orden = $cod_orden";
		$resp = Conexion::buscarRegistro($query);
		if($resp){
		    $resp['descuento'] = ($resp['descuento'] > 0) ? number_format($resp['descuento'] / 1.12, 2) : 0;
		    $resp['subtotal'] = number_format($resp['subtotal'], 2);
		    $resp['subtotal0'] = number_format($resp['subtotal0'], 2);
		    $resp['subtotal12'] = number_format($resp['subtotal12'], 2);
		    $resp['envio'] = number_format($resp['envio'], 2);
		    $resp['total'] = number_format($resp['total'], 2);
		    $resp['iva'] = number_format($resp['iva'], 2);
		}
		return $resp;
	}
	
	public function getOrdenByFlota($cod_orden){
		$cod_empresa = cod_empresa;
		$query = "SELECT cod_orden as id, cod_usuario, cod_sucursal, cod_courier, fecha, estado, cod_descuento as cupon_descuento, 
					subtotal, subtotal0, subtotal12, descuento, envio, envio_iva, iva, iva_porcentaje, total, is_envio, is_programado, hora_retiro, latitud, longitud, telefono, referencia as direccion, referencia2, pago, 
					is_suelto, monto_suelto, medio_compra, observacion, order_token
				FROM tb_orden_cabecera 
				WHERE cod_orden = $cod_orden";
		$resp = Conexion::buscarRegistro($query);
		if($resp){
		    $resp['descuento'] = ($resp['descuento'] > 0) ? number_format($resp['descuento'] / 1.12, 2) : 0;
		    $resp['subtotal'] = number_format($resp['subtotal'], 2);
		    $resp['subtotal0'] = number_format($resp['subtotal0'], 2);
		    $resp['subtotal12'] = number_format($resp['subtotal12'], 2);
		    $resp['envio'] = number_format($resp['envio'], 2);
		    $resp['total'] = number_format($resp['total'], 2);
		    $resp['iva'] = number_format($resp['iva'], 2);
		}
		return $resp;
	}
	
	public function getMotorizadoByOrden($cod_orden){
	    $query = "SELECT * FROM tb_orden_motorizado WHERE cod_orden = $cod_orden";
	    return Conexion::buscarRegistro($query);
	}

	public function getOrdenDetalle($cod_orden){
		$ivaDivider = 1 + ($this->percent_iva / 100);

		$query = "SELECT o.cod_orden_detalle as id, o.cod_producto as product_id, p.nombre as producto, p.sku, o.descripcion, o.comentarios, o.precio, o.descuento, o.descuento_porcentaje, o.cantidad, o.precio_final as total, o.adicional_total, p.peso, p.cobra_iva,
		        p.image_min, o.es_regalo, e.dia as evento, e.estado as estado_evento
		FROM tb_orden_detalle o
        INNER JOIN tb_productos p ON o.cod_producto = p.cod_producto
        LEFT JOIN tb_orden_evento e ON o.cod_orden_detalle = e.cod_orden_detalle
		WHERE o.cod_orden = $cod_orden;";
		$detalles = Conexion::buscarVariosRegistro($query);
		foreach($detalles as $key => $detalle){
		    if($detalle['cobra_iva'] == 1 && $this->sucursal_grava_iva == 1){
		        $detalle['precio'] = $detalle['precio'] / $ivaDivider;
		        // $detalle['total'] = $detalle['total'] / $ivaDivider;
		        $detalle['adicional_total'] = $detalle['adicional_total'] / $ivaDivider;
		        $detalle['descuento'] = ($detalle['descuento'] > 0) ? $detalle['descuento'] / $ivaDivider : 0;
		    }
		    
		    $detalles[$key]['precio'] = number_format($detalle['precio'], 2);
		    $detalles[$key]['total'] = number_format($detalle['total'], 2);
		    $detalles[$key]['adicional_total'] = number_format($detalle['adicional_total'], 2);
		    $detalles[$key]['descuento'] = number_format($detalle['descuento'], 2);
			$detalles[$key]['opciones'] = $this->decodificarOpcionesByDetalle($detalle['descripcion'], $detalle['cantidad']);
			unset($detalles[$key]['descripcion']);
		}
		return $detalles;
	}

	public function decodificarOpcionesByDetalle($string, $cantidadPadre=1){
		$ivaDivider = 1 + ($this->percent_iva / 100);

		$opciones = json_decode($string, true);
		if (JSON_ERROR_NONE !== json_last_error()){
			return [];
		}else{
		    foreach($opciones as $key => $item){
		        $text = isset($item['text']) ? $item['text'] : $item['nombre'];
		        $opciones[$key]['text'] = $this->replaceUnicode($text);
		        //DETALLES
		        $detalles = $item['detalles'];
		        foreach($detalles as $key2 => $detalle){
		            $text = isset($detalle['text']) ? $detalle['text'] : $detalle['nombre'];
		            $detalles[$key2]['text'] = $this->replaceUnicode($text);
					$detalles[$key2]['cantidad'] = $detalles[$key2]['cantidad'] * $cantidadPadre;
					if($this->sucursal_grava_iva == 1) {
						$precio_adicional_no_tax = number_format(($detalles[$key2]['precio_adicional'] / $ivaDivider), 2);
		            	$detalles[$key2]['precio_adicional_no_tax'] = $precio_adicional_no_tax;
				// 		$detalles[$key2]['precio_adicional'] = number_format($precio_adicional_no_tax * $detalles[$key2]['cantidad'], 2);						
					}
					else {
						$detalles[$key2]['precio_adicional_no_tax'] = number_format($detalles[$key2]['precio_adicional'], 2);
						$detalles[$key2]['precio_adicional'] = number_format($detalles[$key2]['precio_adicional'] * $detalles[$key2]['cantidad'], 2);
					}
		        }
		        $opciones[$key]['detalles'] = $detalles;
		    }
			return $opciones;
		}
	}

	public function getOrdenPagos($cod_orden){
		$query = "SELECT p.forma_pago, f.descripcion, p.monto, p.observacion
		FROM tb_orden_pagos p, tb_formas_pago f
		WHERE p.forma_pago = f.cod_forma_pago
		AND p.cod_orden = $cod_orden";
		$pagos = Conexion::buscarVariosRegistro($query);
		foreach($pagos as $key => $pago){
		    $pagos[$key]['monto'] = number_format($pago['monto'], 2);
			$pagos[$key]['descripcion'] = html_entity_decode($pago['descripcion']);
		}
		return $pagos;
	}
	
	

	public function getOrdenTimeline($cod_orden){
		$query = "SELECT h.estado, fecha
		FROM tb_orden_historial h
		WHERE h.cod_orden = $cod_orden
		ORDER BY h.fecha ASC";
		$resp = Conexion::buscarVariosRegistro($query);
		foreach ($resp as $key => $item) {
			$resp[$key]['fecha'] = fechaLatinoShort($item['fecha'])." ".getHourToDateTime($item['fecha']);
			$desc = "";
			if($item['estado'] == "ASIGNADA")
			    $desc = "Orden enviada al courier";
			else if($item['estado'] == "ORDEN_ACEPTADA")
			    $desc = "Motorizado acepto la carrera";
			else if($item['estado'] == "PUNTO_RECOGIDA")
			    $desc = "Motorizado llegó al local a recoger el pedido";
			else if($item['estado'] == "ENVIANDO")
			    $desc = "Motorizado se dirige al destino";
			else if($item['estado'] == "PUNTO_ENTREGA")
			    $desc = "Motorizado llegó al lugar de entrega";
			else if($item['estado'] == "ENTREGADA")
			    $desc = "Motorizado o Caja entregaron el pedido";
			else if($item['estado'] == "NO_ENTREGADA")
			    $desc = "Motorizado no pudo entregar el pedido";
			else if($item['estado'] == "ANULADA")
			    $desc = "La orden fue anulada";
			else if($item['estado'] == "PREPARANDO")
			    $desc = "La orden se está preparando";
			$resp[$key]['descripcion'] = $desc;
		}  
		return $resp;
	}

	public function getOrdenDestino($cod_orden){
		$query = "SELECT od.num_casa, od.cod_postal, c.codigo, c.nombre, c.provincia 
				FROM tb_orden_destino od
				INNER JOIN tb_ciudades c ON od.cod_ciudad = c.cod_ciudad
				AND od.cod_orden = $cod_orden";
		return Conexion::buscarRegistro($query);
	}

	public function getMotivoAnulacion($cod_orden){
		$query = "SELECT *
					  FROM tb_orden_cancelacion
					  WHERE cod_orden = ".$cod_orden;
		 $cancelacion = Conexion::buscarRegistro($query);
		 if($cancelacion){
			 return $cancelacion['motivo'];
		 }else   
			return "";
	}
	
	public function getOrdenAnulada($cod_orden){
		$query = "SELECT oc.*, fe.num_factura
				FROM tb_orden_cabecera oc
				INNER JOIN tb_orden_factura_electronica fe ON fe.cod_orden = oc.cod_orden
				WHERE oc.cod_orden = ".$cod_orden;
		 return Conexion::buscarRegistro($query);
	}

	public function getCourierAnulacion($cod_orden){
		$query = "SELECT *
					  FROM tb_orden_courier_canceled
					  WHERE cod_orden = ".$cod_orden;
		 $cancelacion = Conexion::buscarRegistro($query);
		 if($cancelacion){
			 return $cancelacion['motivo'];
		 }else   
			return "";
	}

	public function setEstado($cod_orden, $estado){
		$query = "UPDATE tb_orden_cabecera SET estado='$estado' WHERE cod_orden = $cod_orden";
		if(Conexion::ejecutar($query,NULL)){
			if($estado <> "ACEPTADA" && $estado <> "ANULADA") {
				if(!$this->getOrderAccepted($cod_orden)) {
					$this->SetHistorial($cod_orden, 'ACEPTADA',fecha()); 
				}
			}
			$this->SetHistorial($cod_orden, $estado,fecha());
			return true;
		}else{
			return false;
		}
	}
	
	public function getCourier($cod_orden){
	    $query = "SELECT oc.cod_courier, oc.order_token as token, c.nombre as courier 
                FROM tb_orden_cabecera oc
                INNER JOIN tb_courier c ON c.cod_courier = oc.cod_courier
                WHERE cod_orden = $cod_orden";
        return Conexion::buscarRegistro($query);
	}

	public function setCourier($cod_orden, $token, $cod_courier){
		$query = "UPDATE tb_orden_cabecera 
				SET cod_courier=$cod_courier, order_token='$token', estado='ASIGNADA'
				WHERE cod_orden = $cod_orden";
		if(Conexion::ejecutar($query,NULL)){
				if(!$this->getOrderAccepted($cod_orden)) {
					$this->SetHistorial($cod_orden, 'ACEPTADA',fecha()); 
				}
				$this->SetHistorial($cod_orden, 'ASIGNADA',fecha()); 
			return true;
		}else{
			return false;
		}
	}
	
	public function setFlota($cod_orden, $cod_flota){
	    $fecha = fecha();
	    $query = "INSERT INTO tb_ordenes_flota (cod_flota, cod_orden, fecha_creacion) 
	            VALUES($cod_flota, $cod_orden, '$fecha')";
	   return Conexion::ejecutar($query,NULL);
	}

	public function cancelarAsignacion($cod_orden){
		$query = "UPDATE tb_orden_cabecera 
				SET cod_courier=0, order_token='', estado='ENTRANTE'
				WHERE cod_orden = $cod_orden";
		if(Conexion::ejecutar($query,NULL)){
				//$this->SetHistorial($cod_orden, 'ASIGNADA',fecha()); 
			return true;
		}else{
			return false;
		}
	}
	
	public function deleteMotorizadoAsignacion($cod_orden){
	       $query = "DELETE FROM tb_motorizado_asignacion WHERE cod_orden = $cod_orden";
		   $resp = Conexion::ejecutar($query,NULL);
	       $query2 = "DELETE FROM tb_orden_motorizado WHERE cod_orden = $cod_orden";
		   Conexion::ejecutar($query2,NULL);
	       return $resp;
	}

	public function SetHistorial($cod_orden, $estado,$fecha){
		$query = "INSERT INTO tb_orden_historial (cod_orden,estado,fecha)
			values ('$cod_orden','$estado','$fecha')";
		return Conexion::ejecutar($query,NULL);
	}

	public function AnularOrden($cod_orden, $comentario, $userAction){
			$query = "UPDATE tb_cliente_dinero SET estado = 'D' WHERE cod_orden = $cod_orden";
			Conexion::ejecutar($query,NULL);

			$query = "UPDATE tb_clientes_puntos SET estado = 'D' WHERE cod_orden = $cod_orden";
			Conexion::ejecutar($query,NULL);
			
			$query = "UPDATE tb_clientes_saldos SET dinero = saldo_anterior WHERE cod_orden = $cod_orden";
			Conexion::ejecutar($query,NULL);

			$this->setEstado($cod_orden, 'ANULADA');
			
			$query = "INSERT INTO tb_orden_cancelacion(cod_orden,motivo,fecha_create,user_create) VALUES($cod_orden,'$comentario',NOW(),$userAction)";
			Conexion::ejecutar($query,NULL);
			
			$query = "SELECT c.cod_cliente, p.monto
                    FROM tb_orden_cabecera o, tb_usuarios u, tb_clientes c, tb_orden_pagos p
                    WHERE o.cod_usuario = u.cod_usuario
                    AND c.cod_usuario = u.cod_usuario
                    AND o.cod_orden = p.cod_orden
                    AND p.forma_pago = 'P'
                    AND o.cod_orden = $cod_orden";
		    $dineroUsado = Conexion::buscarRegistro($query, NULL);
		    if($dineroUsado){
		        $cod_cliente = $dineroUsado['cod_cliente'];
		        $monto = $dineroUsado['monto'];
		        $query = "INSERT INTO tb_cliente_dinero(cod_cliente, cod_tipo_pago, dinero, saldo, fecha, fecha_caducidad, estado) 
					VALUES($cod_cliente, 3, $monto, $monto, NOW(), DATE_ADD(NOW(), INTERVAL 3 MONTH), 'A')";
			    Conexion::ejecutar($query,NULL);	
		    }
			return true;
	}

	public function isPagoTarjeta($cod_orden){
		$query = "SELECT * FROM tb_orden_pagos 
				WHERE forma_pago='T' AND cod_orden = $cod_orden";
		return Conexion::buscarRegistro($query);
	}

	public function isFactElectronica($cod_orden){
		$query = "SELECT * FROM tb_orden_factura_electronica 
				WHERE estado IN ('CREADA', 'EMITIDA_SRI') AND cod_orden = $cod_orden";
		return Conexion::buscarRegistro($query);
	}

	public function getHistoryOrders($cod_sucursal){
		$cod_empresa = cod_empresa;
		$query = "SELECT oh.*, u.nombre, om.foto as motorizado_foto, CONCAT(om.nombre, ' ', om.apellido) as motorizado, om.telefono as motorizado_telefono
					FROM tb_orden_cabecera oc, tb_orden_historial oh, tb_orden_motorizado om, tb_usuarios u
					WHERE oc.cod_orden = oh.cod_orden
					AND oc.cod_orden = om.cod_orden
					AND oc.cod_usuario = u.cod_usuario
					AND oc.cod_empresa = $cod_empresa
					AND oc.cod_sucursal = $cod_sucursal
					AND oc.is_envio = 1
					AND oh.estado IN('PUNTO_RECOGIDA', 'ENVIANDO', 'PUNTO_ENTREGA', 'ENTREGADA')
					ORDER BY oc.cod_orden DESC
					LIMIT 0,20";
		return Conexion::buscarVariosRegistro($query);
	}

	public function asignarMotorizado($cod_orden, $cod_motorizado, $fecha){
		$query = "INSERT INTO tb_motorizado_asignacion (cod_orden, cod_motorizado, fecha_asignacion) 
					VALUES($cod_orden, $cod_motorizado, '$fecha')";
		$resp = Conexion::ejecutar($query, NULL);
		if($resp){
			$query = "UPDATE tb_orden_cabecera 
						SET estado = 'ASIGNADA' 
						WHERE cod_orden = $cod_orden";
			return Conexion::ejecutar($query, NULL); 
		}
		return false;
	}

	public function orderHistorial($cod_orden, $estado, $fecha){
		$query = "INSERT INTO tb_orden_historial (cod_orden, estado, fecha)
					VALUES ('$cod_orden', '$estado', '$fecha')";
		return Conexion::ejecutar($query, NULL);
	}

	public function setOrdenMotorizado($cod_orden, $cod_motorizado){
		$url = url_sistema."assets/empresas/".alias."/";
		$motorizado = $this->getMotorizado($cod_motorizado);
		$nombre = $motorizado["nombre"];
		$apellido = $motorizado["apellido"];
		$num_documento = $motorizado["num_documento"];
		$placa = "";
		$foto =  $url.$motorizado["imagen"];
		$telefono = $motorizado["telefono"];

		return $this->setMotorizadoToOrder($cod_orden, $nombre, $apellido, $num_documento, $placa, $foto, $telefono);
	}

	public function setMotorizadoToOrder($cod_orden, $nombre, $apellido, $num_documento, $placa, $foto, $telefono){
		$query = "INSERT INTO tb_orden_motorizado (cod_orden, nombre, apellido, num_documento, placa, foto, telefono, proceso) 
		VALUES('$cod_orden', '$nombre', '$apellido', '$num_documento', '$placa', '$foto', '$telefono','En camino al local')";
		return Conexion::ejecutar($query,NULL);
	}

	function getMotorizado($cod_motorizado){
		$query = "SELECT * 
					FROM tb_usuarios 
					WHERE cod_usuario = $cod_motorizado";
		$moto = Conexion::buscarRegistro($query);
		if($moto){
		    $url = url_sistema."assets/empresas/".alias."/";
		    $moto['foto'] = $url.$moto["imagen"];
		}
		return $moto;
	}

	function getOrdenesRezagadas($cod_sucursal){
		$start = fecha_only()." 00:00:00";
		$end = sumarTiempo("+15", "minute");
		$query = "SELECT COUNT(*) as cantidad, SUM(total) as total
			FROM tb_orden_cabecera 
			WHERE cod_sucursal = $cod_sucursal
			AND estado = 'ENTRANTE'
			AND hora_retiro > '$start'
			AND hora_retiro < '$end'";
		return Conexion::buscarRegistro($query);
	}
	
	function getOrdenesRezagadasArr($cod_sucursal){
		$start = fecha_only()." 00:00:00";
		$end = sumarTiempo("+15", "minute");
		$query = "SELECT *
			FROM tb_orden_cabecera 
			WHERE cod_sucursal = $cod_sucursal
			AND estado = 'ENTRANTE'
			AND hora_retiro > '$start'
			AND hora_retiro < '$end'";
		return Conexion::buscarVariosRegistro($query);
	}


	public function getOrdenPrint($cod_orden){
		$query = "SELECT oc.*, u.nombre, u.apellido, u.correo, u.telefono as telefono_user, u.num_documento
					FROM tb_orden_cabecera oc, tb_usuarios u
					WHERE oc.cod_usuario = u.cod_usuario
					AND oc.cod_orden = $cod_orden";
		$resp = Conexion::buscarRegistro($query);
		if($resp){
			$entrega = "Pickup";
			if($resp['is_envio'] == 1){
				$entrega = "Delivery";
			}
			$resp['entrega'] = $entrega;
			$resp['hora_retiro_latino'] = fechaLatinoShort($resp['hora_retiro'])." a las ".getHourToDateTime($resp['hora_retiro']);
			
			/*$query = "SELECT o.*, p.nombre, p.peso, p.cobra_iva
                    FROM tb_orden_detalle o
                    INNER JOIN tb_productos p ON o.cod_producto = p.cod_producto
                    AND o.cod_orden = $cod_orden";
			$detalles = Conexion::buscarVariosRegistro($query, NULL);*/
			
			$query = "SELECT o.*, p.nombre, p.peso, p.cobra_iva, (SELECT c.categoria 
                            FROM tb_productos_categorias pc 
                            INNER JOIN tb_categorias c ON pc.cod_categoria = c.cod_categoria
                            WHERE pc.cod_producto = p.cod_producto
                            LIMIT 1) AS categoria
                    FROM tb_orden_detalle o
                    INNER JOIN tb_productos p ON o.cod_producto = p.cod_producto
                    AND o.cod_orden = $cod_orden";
			$detalles = Conexion::buscarVariosRegistro($query, NULL);

			foreach($detalles as $key => $detalle){
				$detalles[$key]['opciones'] = $this->decodificarOpcionesByDetalle($detalle['descripcion'], $detalle['cantidad']);
			}
			$resp['detalle'] = $detalles;

			$query = "SELECT p.forma_pago, p.monto, f.descripcion, p.observacion, p.lote
						FROM tb_orden_pagos p, tb_formas_pago f
						WHERE p.forma_pago = f.cod_forma_pago
						AND p.cod_orden = $cod_orden";
			$resp['pagos'] = Conexion::buscarVariosRegistro($query, NULL);		
			
			$query = "SELECT * FROM tb_orden_datos_facturacion WHERE cod_orden = $cod_orden";
			$resp['datos_facturacion'] = Conexion::buscarRegistro($query, NULL);		
			return $resp;
		}else
			return false;
		return $resp;
	}

	public function getRunfood($id){
		$query = "SELECT c.*, r.id, u.num_documento  
				FROM tb_orden_cabecera c, tb_orden_runfood r, tb_usuarios u
				WHERE c.cod_orden = r.cod_orden 
				AND u.cod_usuario = c.cod_usuario
				AND c.cod_empresa = ".cod_empresa." AND r.id = '$id'";
		return Conexion::buscarRegistro($query);
	}

	public function get_orden_array($cod_orden){
		$query = "SELECT oc.*, u.nombre, u.apellido, u.correo, u.telefono as telefono_user, u.num_documento
					FROM tb_orden_cabecera oc, tb_usuarios u
					WHERE oc.cod_usuario = u.cod_usuario
					AND oc.cod_orden = $cod_orden";
		$resp = Conexion::buscarRegistro($query);
		if($resp){
			$entrega = "Pickup";
			if($resp['is_envio'] == 1){
				$entrega = "Delivery";
			}
			$resp['entrega'] = $entrega;
			
			$query = "SELECT o.*, p.nombre, p.peso, p.cobra_iva
						FROM tb_orden_detalle o, tb_productos p
						WHERE o.cod_producto = p.cod_producto
						AND o.cod_orden = $cod_orden";
			$detalles = Conexion::buscarVariosRegistro($query, NULL);
			if($detalles){
				foreach($detalles as $key => $detalle){
					$detalles[$key]['opciones'] = $this->decodificarOpcionesByDetalle($detalle['descripcion'], $detalle['cantidad']);
					unset($detalles[$key]['descripcion']);
				}
			}
			$resp['detalle'] = $detalles;

			$query = "SELECT p.forma_pago, p.monto, f.descripcion, p.observacion
						FROM tb_orden_pagos p, tb_formas_pago f
						WHERE p.forma_pago = f.cod_forma_pago
						AND p.cod_orden = $cod_orden";
			$resp['pagos'] = Conexion::buscarVariosRegistro($query, NULL);
			
			$query = "SELECT *
					FROM tb_orden_datos_facturacion 
					WHERE cod_orden = $cod_orden";
		    $resp['datos_facturacion'] = Conexion::buscarRegistro($query);
			return $resp;
		}else
			return false;
		return $resp;
	}

	public function listaOrdenesAntiguas($cod_sucursal){
	    $fecha = fecha_only();
		$cod_empresa = cod_empresa;
		$query = "SELECT ca.cod_orden as id, ca.cod_usuario, ca.cod_sucursal, ca.cod_courier, ca.fecha, ca.total, ca.is_envio, ca.is_programado, ca.hora_retiro, ca.pago, ca.referencia as direccion, ca.estado
					, ca.order_token as token, u.nombre, u.correo, u.telefono, u.imagen
					FROM tb_orden_cabecera ca, tb_usuarios u
					WHERE ca.cod_usuario = u.cod_usuario 
					AND ca.estado NOT IN('ENTREGADA', 'ANULADA')
					AND ca.cod_sucursal = $cod_sucursal
					AND ca.cod_empresa = $cod_empresa
					AND ca.cod_courier IN (0, 99)
					AND DATE(ca.fecha) < '$fecha'
					ORDER BY ca.cod_orden DESC";
		return Conexion::buscarVariosRegistro($query);
	}

	public function getOrderAccepted($cod_orden) {
		$query = "SELECT *
					FROM tb_orden_historial 
					WHERE cod_orden = $cod_orden
					AND estado = 'ACEPTADA'";
		return Conexion::buscarRegistro($query);
	}
	
	public function getLinkMotorizado($cod_orden) {
		$query = "SELECT * FROM tb_motorizado_link WHERE cod_orden = $cod_orden";
		$link = Conexion::buscarRegistro($query);
		if($link){
		    $link['url'] = 'https://pedidos.demo.mie-commerce.com/pedidos/?id='.$link['token'];
		}
		return $link;
	}
	
	public function setLinkToken($cod_orden, $token, $phone){
	    $query = "INSERT INTO tb_motorizado_link(cod_orden, token, fecha_creacion, fecha_expiracion, phone)
	            VALUES($cod_orden, '$token', NOW(), DATE_ADD(NOW(), INTERVAL 6 HOUR), '$phone')";
	   return Conexion::ejecutar($query,NULL);
	}
	
	public function destroyLinkToken($cod_orden){
	    $query = "DELETE FROM tb_motorizado_link WHERE cod_orden = $cod_orden";
	    return Conexion::ejecutar($query,NULL);
	}
	
	public function removeFlotaOrden($cod_orden){
	    $query = "DELETE FROM tb_ordenes_flota WHERE cod_orden = $cod_orden";
	    return Conexion::ejecutar($query,NULL);
	}

	public function saveOrdenInventario($cod_orden, $cod_contifico_empresa, $tipo, $codigo, $id) {
		$fecha = fecha();
		$query = "INSERT INTO tb_orden_inventario
					SET 
					cod_contifico_empresa = $cod_contifico_empresa,
					cod_orden = $cod_orden,
					tipo = '$tipo',
					codigo = '$codigo',
					id = '$id',
					fecha = '$fecha'";
		return Conexion::ejecutar($query, null);
	}
	
	//Recipientes
	public function getRecipientes($cod_orden, $cod_empresa){
	    $query = "SELECT r.cod_recipiente, r.nombre, ore.cantidad 
                    FROM tb_recipientes r
                    LEFT JOIN tb_orden_recipientes ore ON r.cod_recipiente = ore.cod_recipiente AND ore.cod_orden = $cod_orden
                    WHERE r.estado = 'A' AND r.cod_empresa = $cod_empresa";
        $resp = Conexion::buscarVariosRegistro($query);
        foreach($resp as $key => $value){
            if($value['cantidad'] == NULL){
                $resp[$key]['cantidad'] = 0;
            }
        }
        return $resp;
	}
	
	public function getRecipientesByRuc($cod_orden, $cod_empresa, $ruc_id){
	    $query = "SELECT r.cod_recipiente, r.nombre, r.precio, ore.cantidad, rf.id 
                    FROM tb_recipientes r
                    INNER JOIN tb_orden_recipientes ore ON r.cod_recipiente = ore.cod_recipiente AND ore.cod_orden = $cod_orden
                    INNER JOIN tb_recipientes_facturacion rf ON r.cod_recipiente = rf.cod_recipiente AND rf.cod_contifico_empresa = $ruc_id
                    WHERE r.estado = 'A' AND r.cod_empresa = $cod_empresa";
        return Conexion::buscarVariosRegistro($query);
	}
	
	public function setRecipientes($cod_orden, $cod_recipiente, $cantidad){
	    $query = "DELETE FROM tb_orden_recipientes WHERE cod_orden = $cod_orden AND cod_recipiente = $cod_recipiente";
	    Conexion::ejecutar($query, null);
	    
	    $query = "INSERT INTO tb_orden_recipientes (cod_orden, cod_recipiente, cantidad)
	            VALUES($cod_orden, $cod_recipiente, $cantidad)";
	   return Conexion::ejecutar($query, null);
	}
	
	public function getOrdenesNoFacturadas(){
	    $fecha = fecha_only();
		$cod_empresa = cod_empresa;
	    $query = "SELECT oc.cod_orden, oc.fecha, oc.estado, u.nombre as cliente
                    FROM tb_orden_cabecera oc, tb_usuarios u
                    WHERE oc.cod_usuario = u.cod_usuario
                    AND oc.cod_orden NOT IN(SELECT cod_orden FROM tb_orden_factura_electronica)
                    AND oc.fecha BETWEEN '$fecha 00:00:00' AND '$fecha 23:59:59'
                    AND oc.cod_empresa = $cod_empresa
                    AND oc.estado = 'ENTREGADA'
                    ORDER BY oc.cod_orden DESC";
        return Conexion::buscarVariosRegistro($query);
	}
	
	public function getOrdersByStatus($cod_sucursal, $isEnvio){
	    $fecha = fecha_only();
		$cod_empresa = cod_empresa;
	    $query = "SELECT e.cod_estado, e.nombre, COUNT(oc.estado) as num_ordenes
                FROM tb_estado_ordenes e
                LEFT JOIN tb_orden_cabecera oc ON oc.estado = e.cod_estado 
                                AND oc.cod_sucursal = $cod_sucursal 
                                AND oc.fecha BETWEEN '$fecha 00:00:00' AND '$fecha 23:59:59' 
                                AND oc.is_envio = $isEnvio AND e.estado = 'A'
                WHERE e.is_envio IN ($isEnvio,2)
                GROUP BY (e.cod_estado)
                ORDER BY e.posicion ASC";
        return Conexion::buscarVariosRegistro($query);
	}
	
	public function getDatosFacturacion($cod_orden){
	    $query = "SELECT *
					FROM tb_orden_datos_facturacion 
					WHERE cod_orden = $cod_orden";
		return Conexion::buscarRegistro($query);
	}

	public function saveOrdenError($cod_orden, $tipo, $proveedor, $motivo){
		$fecha = fecha();
		$query = "INSERT INTO tb_orden_errores(cod_orden, tipo, proveedor, motivo, fecha)
				VALUES($cod_orden, '$tipo', '$proveedor', '$motivo', '$fecha')";
		return Conexion::ejecutar($query, null);
	}

	public function getOrdenesProgramadas() {
		
	}
	
	public function getCiudadDestino($cod_orden){
            $query = "SELECT od.*, s.nombre as nom_sucursal, s.nombre as nom_ciudad_origen, c.nombre as nom_ciudad_destino, c.provincia 
                        FROM tb_orden_destino od, tb_orden_cabecera oc, tb_sucursales s, tb_ciudades c
                        WHERE od.cod_orden = oc.cod_orden
                        AND oc.cod_sucursal = s.cod_sucursal
                        AND od.cod_ciudad = c.cod_ciudad
                        AND od.cod_orden = $cod_orden";
            return Conexion::buscarRegistro($query);
    }

	public function saveLastOrderToken($cod_orden, $order_token) {
		$fecha = fecha();
		$query = "INSERT INTO tb_orden_historial
					SET 
						cod_orden = $cod_orden,
						estado = 'ASIGNACION_CANCELADA',
						order_token = '$order_token',
						fecha = '$fecha',
						observacion = 'Cancelada por el admin'";
		return Conexion::ejecutar($query, null);
	}
	
	function replaceUnicode($string){
        $search  = array('u00c1', 'u00e1', 'u00c9', 'u00e9', 'u00cd', 'u00ed', 'u00d3', 'u00f3', 'u00da', 'u00fa', 'u00d1', 'u00f1', 'u00bf');
        $replace = array('A', 'a', 'E', 'e', 'I', 'i', 'O', 'o', 'U', 'u', 'N', 'n', '');
        return str_replace($search, $replace, $string);
    }
    
    public function getOrderForNotify($cod_orden){
		$query = "SELECT oc.cod_orden, oc.fecha, oc.subtotal, oc.descuento, oc.envio, oc.iva, oc.total, oc.estado, oc.is_envio, oc.is_programado, oc.hora_retiro, oc.referencia, oc.referencia2, u.nombre, u.apellido, u.correo, u.telefono, s.nombre as sucursal, s.direccion as sucursal_direccion, oc.cod_sucursal, e.nombre as empresa
		FROM tb_orden_cabecera oc, tb_usuarios u, tb_sucursales s, tb_empresas e
		WHERE oc.cod_usuario = u.cod_usuario
		AND oc.cod_sucursal = s.cod_sucursal
		AND s.cod_empresa = e.cod_empresa
		AND oc.cod_orden = $cod_orden";
		$orden = Conexion::buscarRegistro($query);
		if($orden){
		    $queryPago = "SELECT p.forma_pago as id, p.monto, f.descripcion as nombre
						FROM tb_orden_pagos p, tb_formas_pago f
						WHERE p.forma_pago = f.cod_forma_pago
						AND p.cod_orden = $cod_orden";
			$orden['pagos'] = Conexion::buscarVariosRegistro($queryPago, NULL);	
		}
		return $orden;
	}
	
	//FLOTAS
	public function getListOrdersFlota($business_id = 0, $status = ''){
		$filtroComercios='';   
		$filtroEstados ='';
	    $cod_empresa = cod_empresa;  
	    if($business_id > 0){
	        $filtroComercios = "AND e.cod_empresa = $business_id";
	    }
	    
	    if($status !== ''){
	        $filtroEstados = "AND oc.estado = '$status'";
	    }


        $query = "SELECT 
                  oc.cod_orden, 
                  oc.total, 
                  oc.fecha, 
                  oc.estado, 
                  CONCAT(u.nombre, ' ', u.apellido) as nom_cliente,
                  s.nombre as sucursal,
                  e.nombre as empresa,
                  CONCAT(e.alias, '/', e.logo) as imagen
                FROM 
                  tb_orden_cabecera oc 
                  INNER JOIN tb_usuarios u ON oc.cod_usuario = u.cod_usuario 
                  AND oc.cod_courier = 101 
                  INNER JOIN tb_ordenes_flota of on of.cod_orden = oc.cod_orden 
                  INNER JOIN tb_sucursales s on s.cod_sucursal = oc.cod_sucursal
                  INNER JOIN tb_empresas e on e.cod_empresa = oc.cod_empresa $filtroComercios
                WHERE 
                  of.cod_flota = $cod_empresa 
                  $filtroEstados" ;
		$ordenes = Conexion::buscarVariosRegistro($query);	
		foreach($ordenes as $key => $orden){
		    $ordenes[$key]['imagen'] = url_business_assets.$orden['imagen'];
		}
		return $ordenes;
	}
	
	//MASIVAS
	public function getOrdenesMasivas($ids) {
        $cod_empresa = cod_empresa;
    
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
    
        $query = "SELECT cod_orden FROM tb_orden_cabecera WHERE cod_orden IN ($placeholders)  AND estado = 'ENTRANTE'  AND cod_empresa = ?  AND is_envio = 1";
        $params = array_merge($ids, [$cod_empresa]);
        return Conexion::buscarVariosRegistro($query, $params);
    }
    
    public function actualizarEstadoOrdenes($ids, $cod_courier = 99) {
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $query = "UPDATE tb_orden_cabecera 
                  SET cod_courier = ?, estado = 'ASIGNADA' 
                  WHERE cod_orden IN ($placeholders)";
        $params = array_merge([$cod_courier], $ids);
    
        return Conexion::ejecutar($query, $params);
    }
    
    public function asignarMotorizadoYActualizarOrdenes($ordenes, $motorizado) {
        $conn = Conexion::obtenerConexion();
        $conn->beginTransaction();
    
        $fecha = date('Y-m-d H:i:s');
    
        foreach ($ordenes as $orden) {
            $cod_orden = $orden['cod_orden'];
    
            // Paso 3: asignar motorizado
            $stmt1 = $conn->prepare("INSERT INTO tb_motorizado_asignacion (cod_orden, cod_motorizado, fecha_asignacion) 
                                     VALUES (?, ?, ?)");
            $stmt1->execute([$cod_orden, $motorizado['cod_usuario'], $fecha]);
    
            // Paso 4: agregar datos del motorizado a la orden
            $stmt2 = $conn->prepare("INSERT INTO tb_orden_motorizado 
                (cod_orden, nombre, apellido, num_documento, placa, foto, telefono, proceso)
                VALUES (?, ?, ?, ?, ?, ?, ?, 'En camino al local')");
            $stmt2->execute([
                $cod_orden,
                $motorizado['nombre'],
                $motorizado['apellido'],
                $motorizado['num_documento'],
                $motorizado['placa'],
                $motorizado['foto'],
                $motorizado['telefono']
            ]);
        }
    
        $conn->commit();
    }
}
?>
<?php
/*	Variables Heredadas del Index
		$method - POST, GET, PUT, DELETE, etc.
		$request - Url y variables GET
		$input - Solo metodo POST, PUT */

require_once "clases/cl_empresas.php";
$ClEmpresas = new cl_empresas();

	if($method == "GET"){
		$num_variables = count($request);
		if($num_variables == 1){
		    $return = getConfiguracion();
			showResponse($return);
		}else if($num_variables == 2){
			if($request[1] == "provincias"){
				$return['success'] = 1;
				$return['mensaje'] = "Lista Provincias";
				$query = "SELECT provincia, estado FROM tb_ciudades GROUP by provincia order by provincia ASC";
				$return['data'] = Conexion::buscarVariosRegistro($query);
				showResponse($return);
			}
			$return = getConfiguracion($request[1]);
			showResponse($return);
		}
		else if($num_variables == 3){
			if($request[1] == "ciudades"){
				$provincia = $request[2];
				$return['success'] = 1;
				$return['mensaje'] = "Lista Ciudades por provincia";
				$query = "SELECT cod_ciudad, nombre, estado FROM tb_ciudades where provincia = '$provincia' and estado = 'A' order by nombre ASC";
				$return['data'] = Conexion::buscarVariosRegistro($query);
				showResponse($return);
			}
		    if($request[1]=="tokens"){
		        $cod_sucursal = $request[2];
		        $proveedor = $ClEmpresas->getProveedorBotonPagos();
	            if($proveedor){
	                $proveedor = array_map('html_entity_decode', $proveedor);
	                $return['success'] = 1;
	                $return['mensaje'] = "Tokens";
	                $return['proveedor'] = $proveedor;
	                if($proveedor['cod_proveedor_botonpagos'] == 2){ //PAYMENTEZ
	                    $return['tokens'] = $ClEmpresas->getTokensPaymentez($cod_sucursal);    
	                }
	                 if($proveedor['cod_proveedor_botonpagos'] == 1){ //DATAFAST
	                    $return['tokens'] = $ClEmpresas->getTokensDatafast($cod_sucursal);    
	                }
	            }else{
	                $return['success'] = 0;
	                $return['mensaje'] = "No tiene configurado este proveedor";
	            }
	            showResponse($return);
		    }
		}
		else{
			$return['success']= 0;
			$return['mensaje']= "Url no valida para configuracion, por favor revisar los parametros";
			showResponse($return);
		}
	}
	else{
		$return['success']= 0;
		$return['mensaje']= "El metodo ".$method." para configuracion aun no esta disponible.";
		showResponse($return);
	}


function getConfiguracion($aplicacion = ""){
	global $ClEmpresas;
	$return['success'] = 1;
	$return['mensaje'] = "Correcto";

	$return['fidelizacion_active'] = fidelizacion;

	$info = [];
	$empresa = $ClEmpresas->get();
	if($empresa){
		$info['nombre'] = $empresa['nombre'];
		$info['alias'] = $empresa['alias'];
		$info['direccion'] = $empresa['direccion'];
		$info['telefono'] = $empresa['telefono'];
		$info['correo'] = $empresa['correo'];
		$info['color'] = $empresa['color'];
		$info['pixel'] = $empresa['facebook_pixel'];
		$info['url_android'] = $empresa['url_android'];
		$info['url_ios'] = $empresa['url_ios'];
		$info['redes_sociales'] = $ClEmpresas->getRedesSociales();
	}

	$return['informacion'] = $info;

	/*VERSION APP*/
	if($aplicacion !== ""){
		$versionApp = $ClEmpresas->getAppVersion($aplicacion);
		if($versionApp){
			$versionApp['code'] = intval($versionApp['code']);
			$versionApp['texto'] = html_entity_decode($versionApp['texto']);
			$return['app_version'] = $versionApp;

			if($aplicacion == "ANDROID")
				$return['app_version']['url'] = $empresa['url_android'];
			
			if($aplicacion == "IOS")
				$return['app_version']['url'] = $empresa['url_ios'];
		}

		/*VALIDAR CAMPOS EN EL REGISTRO*/
		$return['registro_test'] = false;
		if(isset($_GET['version'])){
			$version = $_GET['version'];
			$query = "SELECT * FROM tb_app_registro_reglas WHERE origen='$aplicacion' AND version_code='$version'";
			if(Conexion::buscarRegistro($query)){
				$return['registro_test'] = true;
			}
		}
	}

	/*PROGRAMAR PEDIDO*/
	$programar = $ClEmpresas->getProgramar();
	if($programar){
		$return['programar'] = $programar;
	}
	
	/*FORMAS DE PAGO*/
	$formaPago = $ClEmpresas->getFormasPagoEmpresa();
	if($formaPago){
		$fpago = [];
		foreach($formaPago as $fp){
			$fp['descripcion'] = strip_tags(editor_decode($fp['descripcion']));
			
			if($fp['cod_forma_pago'] == "T"){
				$proveedor = $ClEmpresas->getProveedorBotonPagos();
				if($proveedor){
					$fp['cod_proveedor'] = $proveedor['cod_proveedor_botonpagos'];
					$fp['proveedor'] = $proveedor['identificador'];
					$fpago[] = array_map('html_entity_decode', $fp);
				}
			}else{
				$fpago[] = array_map('html_entity_decode', $fp);
			}
		}
		$return['forma_pago'] = $fpago;
	}
	
	/*FIDELIZACION*/
	$fidelizacion = $ClEmpresas->getFidelizacion();
	if($fidelizacion){
		$fidelizacion['activo'] = true;
		$return['fidelizacion'] = $fidelizacion;
		$return['niveles'] = $ClEmpresas->getNiveles();
	}else{
		$fidelizacion['activo'] = false;
		$return['fidelizacion'] = $fidelizacion;
	}
	
	return $return;
}	
?>
<?php
require_once "clases/cl_empresas.php";
require_once "clases/cl_sucursales.php";
$ClSucursales = new cl_sucursales();
$ClEmpresas = new cl_empresas();

if($method == "GET"){
	$num_variables = count($request);
	if($num_variables == 1){
	}else if($num_variables == 2){
		$cod_orden = $request[1];
		showResponse(getConfiguracion($cod_orden));
	}else if($num_variables == 3){
		
	}

	$return['success']= 0;
	$return['mensaje']= "Evento no existente para órdenes";
	showResponse($return);
}else{
	$return['success']= 0;
	$return['mensaje']= "El metodo ".$method." para Órdenes aun no esta disponible.";
	showResponse($return);
}


function getConfiguracion($cod_sucursal){
	global $ClSucursales;
    global $ClEmpresas;
    $respConfig = null;
	$sucursal = $ClSucursales->get($cod_sucursal);
	if($sucursal){
        //ESTADOS
        $respConfig['estados'] = getStatus();

        //COURIERS
        $couriers = $ClSucursales->getCouriers($sucursal['id']);
        $flotas = $ClSucursales->getFlotas($sucursal['id']);
        $respConfig['couriers'] = array_merge($couriers, $flotas);

        //FACT ELECTRONICA
        $respConfig['facturacion'] = getFacturacionElectronica(cod_empresa);
        
        //PERMISOS
        $respConfig['permisos'] = $ClEmpresas->getPermissions();

        //FIDELIZACION
        $fidelizacion = $ClEmpresas->getFidelizacion();
        if($fidelizacion){
            $fidelizacion['activa'] = true;
            $fidelizacion['niveles'] = $ClEmpresas->getNiveles();

            $respConfig['fidelizacion']= $fidelizacion;
        }else{
            $respConfig['fidelizacion']['activa'] = false;
        }
        
        //MOTORIZADOS
        $respConfig['motorizados'] = $ClEmpresas->getMotorizadosInternos();
        

        /*DISPONIBILIDAD*/
        $sucursal['horarios'] = $ClSucursales->getHorarios($cod_sucursal);
        $abierto = $ClSucursales->disponibilidad($cod_sucursal);
        if($abierto){
            $sucursal['hora_ini'] = $abierto['hora_ini'];
            $sucursal['hora_fin'] = $abierto['hora_fin'];
            $sucursal['abierto'] = true;
        }else
            $sucursal['abierto'] = false;
        
        /*CERRADO POR RESTRICCION??*/
        $sucursal['restriccion'] = $ClSucursales->restriccionDisponibilidadBySucursal($cod_sucursal);
        
        //Alta Demanda
        $sucursal['alta_demanda'] = $ClSucursales->getAltaDemanda($cod_sucursal);
        
        $respConfig['sucursal'] = $sucursal;

        /*CASHER*/
        $Allheaders = getallheaders();
        if (array_key_exists("Casher-Id", $Allheaders)) {
            $respConfig['casher'] = getConfigCasher($Allheaders['Casher-Id'], $respConfig['permisos']);
        }else{
            $respConfig['casher'] = false;
        }

		$return['success'] = 1;
		$return['mensaje'] = "Correcto";
		$return['data'] = $respConfig;
	}else{
		$return['success'] = 0;
		$return['mensaje'] = "Sucursal $cod_sucursal no encontrada";
	}
	return $return;
}

function getFacturacionElectronica($cod_empresa){
    $query = "SELECT ef.*, f.nombre 
    FROM tb_empresa_facturacion ef
    INNER JOIN tb_sistema_facturacion f ON ef.cod_sistema_facturacion = f.cod_sistema_facturacion
    WHERE ef.cod_empresa = $cod_empresa AND ef.estado = 'A'
    ORDER BY ef.prioridad";
    return Conexion::buscarVariosRegistro($query);
}

function getStatus(){
    $query = "SELECT cod_estado as id, nombre, icono, is_envio 
            FROM tb_estado_ordenes WHERE estado='A' ORDER BY posicion ASC";
    return Conexion::buscarVariosRegistro($query);
}

function getConfigCasher($cod_usuario, $permisos){
    $query = "SELECT cod_usuario as CasherId, recordatorio, printer FROM tb_cajero_gestionordenes_config WHERE cod_usuario = $cod_usuario";
    $config = Conexion::buscarRegistro($query);
    if(!$config){
        $config['CasherId'] = $cod_usuario;

        //Configuración por defecto de Recordatorios
        $recordatorios = [
            'permiso' => 1,
            'tiempo' => 5,
            'asignacion' => 0,
            'tiempo_asignacion' => 15,
        ];
        if(in_array("GO_AUTOASIGNAR_ORDENES", $permisos)){  //Tiene permisos para autoasignar
            $query = "SELECT * FROM tb_usuarios WHERE cod_usuario = $cod_usuario AND estado = 'A'";
            $user = Conexion::buscarRegistro($query);
            if($user){
                if($user['cod_rol'] == "5"){
                    $recordatorios['asignacion'] = 1;
                }
            }
        }
        $config['recordatorio'] = $recordatorios;
    }
    return $config;
}
?>
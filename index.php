<?php
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Headers: Api-Key, Content-type, *");
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header("Allow: GET, POST, OPTIONS, PUT, DELETE");
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Max-Age: 86400');    // cache for 1 day
header("Content-type:application/json; charset=utf-8");

require_once "funciones.php";
require_once "helpers/walletHelper.php";
//METODOS
$funciones = array(
    "usuarios"   => "controllers/Usuarios.php",
	"productos"   => "controllers/Productos.php",
	"contifico"   => "controllers/Contifico.php",
	"runfood"   => "controllers/Runfood.php",
	"facturas"   => "controllers/Facturas.php",
	"printer"   => 	"controllers/Printer.php",
    "ordenes"   => 	"controllers/Ordenes.php",
	"sucursales" => "controllers/Sucursales.php",
    "puntos" => "controllers/Fidelizacion.php",
    "configuracion" => "controllers/Configuracion.php",
    "formas-pago" => "controllers/Formas_pago.php",
    "app" => "controllers/app_config.php",
    "correos"=> "controllers/Correos.php",
    "notificar"=> "controllers/Notificaciones.php",
	//CONFIGURACION
	"gestion-ordenes" => "controllers/GestionOrdenes.php",
	"gestion-flotas" => "controllers/GestionFlotas.php",
);

$empresa = NULL;
$method = $_SERVER['REQUEST_METHOD'];
if($method == "OPTIONS"){
    $return['success']= 1;
	$return['mensaje']= "Validacion completa";
    showResponse($return);
}

if(verificateWs($empresa))
{
	$cod_empresa = $empresa['cod_empresa'];
	$alias = $empresa['alias'];
	$files = url_sistema.'assets/empresas/'.$alias.'/';
	$filesUpload = url_sistema.'assets/empresas/'.$alias.'/';
	define('cod_empresa',$cod_empresa);
	define('alias',$alias);
	define('url',$files);
	define('urlUpload',$filesUpload);
	define('name_site',$empresa['nombre']);
	define('url_web',$empresa['url_web']);
	define('api_key',$empresa['api_key']);
	if($empresa['fidelizacion'] == 1)
		define('fidelizacion',true);
	else	
		define('fidelizacion',false);

	if($empresa['cod_tipo_empresa'] == 1)
		define('tipo','RESTAURANTE');
	else
		define('tipo','RETAIL');

    logGeneral($_SERVER, "INFO CUALQUIERA");
	
	$request = explode("/", trim($_SERVER['PATH_INFO'],'/'));
	if(count($request)>0){
		if (array_key_exists($request[0], $funciones)) {
			if($method == "POST"){
			    $json = file_get_contents('php://input');
				$input = json_decode($json,true);
				mylog($json, 'INPUT JSON ENTRADA');
				if (JSON_ERROR_NONE !== json_last_error()){
					$return['success']= -1;
					$return['mensaje']= "El Json de entrada no tiene un formato correcto.";
					showResponse($return);
				}
				if(count($input)==0){
					$return['success']= -1;
					$return['mensaje']= "No hay valor de entrada";
					showResponse($return);
				}
			}

		    require_once $funciones[$request[0]];
		}else{
			$return['success']= -1;
			$return['mensaje']= "Evento ".$request[0]." no existente, por favor verificar la URL.";
		}
	}
}	
else
{
	$return['success']= -1;
	$return['mensaje']= "No autorizado";
	showResponse($return);
}

$return['success']= 0;
$return['mensaje']= "No hay respuesta, metodo no encontrado";
echo json_encode($return);

function showResponse($return){
	/* MANEJO DE ERRORES */
	/*
	switch ($return['success']) {
		case -1:	//NO AUTORIZADO
			http_response_code(401);
			break;
		case 1:
			http_response_code(200);
			break;
		case 0:
			http_response_code(200);
			break;
		default:
			http_response_code(401);
			break;
	}*/
	http_response_code(200);
	echo json_encode($return);
	exit();
}

function mylog($texto, $title=""){
    global $request;
    $folder = "logs/".alias;
    
    if (!file_exists($folder)) {
        mkdir($folder, 0777);
    }
    $file = $folder."/".$request[0].".log";
    $log = "[".fecha()."] ".$title." ".$texto;
	file_put_contents($file, PHP_EOL . $log, FILE_APPEND);
}

function logGeneral($server, $texto){
    global $request;
    $folder = "logs/".alias;
    
    if (!file_exists($folder)) {
        mkdir($folder, 0777);
    }
    $file = $folder."/todas-las-entradas.log";
    $endpoint = $server['REQUEST_URI'];
    $metodo = $server['REQUEST_METHOD'];
    
    $log = "[".fecha()."] ENDPOINT: ".$endpoint." - METODO: ".$metodo." - INFO: ".$texto;
	file_put_contents($file, PHP_EOL . $log, FILE_APPEND);
}

?>
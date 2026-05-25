<?php
require_once "config.php";
// ob_start();
// session_start();

if (ENVIRONMENT == "production") {
    ini_set('display_errors', 0);
    error_reporting(0);
} else {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
}

require_once "conexion.php";

function verificateWs(&$codigo)
{
    $Allheaders = getallheaders();
    if (array_key_exists("Api-Key", $Allheaders)) {
        $query = "SELECT * FROM tb_empresas WHERE api_key = '" . $Allheaders['Api-Key'] . "'";
        $codigo = Conexion::buscarRegistro($query);
        if ($codigo) {
            return true;
        } else
            return false;
    } else
        return false;
}

function validate($datosObligatorios, $input, &$error)
{
    foreach ($datosObligatorios as $key => $value) {
        if (!array_key_exists($value, $input)) {
            $error = "Falta informacion, Error: Campo $value es obligatorio";
            return false;
        }
    }
    return true;
}

function encrypt_decrypt($action, $string)
{
    $output = false;
    $encrypt_method = "AES-128-CBC";
    $secret_key = '1234567890123456';
    $secret_iv = '1234567890123456';

    if (strlen($secret_key) == 16) {
        $encrypt_method = "AES-128-CBC";
    } else {
        $encrypt_method = "AES-256-CBC";
    }

    if ($action == 'encrypt') {
        $output = openssl_encrypt($string, $encrypt_method, $secret_key, 0, $secret_iv);
        //$output is base64 encoded automatically!
    } else if ($action == 'decrypt') {
        $output = openssl_decrypt($string, $encrypt_method, $secret_key, 0, $secret_iv);
        //$string must be base64 encoded!
    }
    return $output;
}

function getFirstSucursal()
{
    $data = Conexion::buscarRegistro("SELECT * FROM tb_sucursales WHERE cod_empresa = " . cod_empresa . " ORDER BY cod_sucursal ASC LIMIT 0,1");
    if ($data) {
        return $data['cod_sucursal'];
    } else {
        return 0;
    }
}

function build_sorter($key)
{
    return function ($a, $b) use ($key) {
        return strnatcmp($a[$key], $b[$key]);
    };
}

function fecha()
{
    date_default_timezone_set('America/Guayaquil');
    $time = time();
    $fecha = date("Y-m-d H:i:s", $time);    //FECHA Y HORA ACTUAL
    return $fecha;
}

function fecha_only()
{
    date_default_timezone_set('America/Guayaquil');
    $time = time();
    $fecha = date("Y-m-d", $time);    //FECHA Y HORA ACTUAL
    return $fecha;
}

function fechaFormat($format)
{
    date_default_timezone_set('America/Guayaquil');
    $time = time();
    $fecha = date($format, $time);    //FECHA Y HORA ACTUAL
    return $fecha;
}

function fechaToFormat($fecha, $format)
{
    date_default_timezone_set('America/Guayaquil');
    $fecha = date($format, strtotime($fecha));    //FECHA Y HORA ACTUAL
    return $fecha;
}

function fechasDiasDiferencia($dia1, $dia2)
{
    $firstDate  = new DateTime($dia1);
    $secondDate = new DateTime($dia2);
    $intvl = $firstDate->diff($secondDate);
    return $intvl->days;
}

function dayOfWeek($fecha)
{
    date_default_timezone_set('America/Guayaquil');
    //$time = time();
    $dia = date("N", strtotime($fecha));  //FECHA Y HORA ACTUAL
    return $dia;
}

function getHourToDateTime($datetime)
{
    $date = strtotime($datetime);
    return date('H:i', $date);
}

function hora()
{
    date_default_timezone_set('America/Guayaquil');
    $time = time();
    $hora = date("H:i:s", $time);  //FECHA Y HORA ACTUAL
    return $hora;
}

function hora_only()
{
    date_default_timezone_set('America/Guayaquil');
    $time = time();
    $hora = date("H:i", $time);  //FECHA Y HORA ACTUAL
    return $hora;
}

function horaFormat($hora)
{
    $split = explode(":", $hora);
    return $split[0] . ":" . $split[1];
}

function AddIntervalo($datetime, $intervalo)
{
    $intervalo_minutos = ($intervalo * 60);

    list($dia, $hora) = explode(' ', $datetime);
    $nuevaHora = strtotime($intervalo_minutos . ' minute', strtotime($hora));
    $nuevaHora = date('H:i:s', $nuevaHora);

    return $dia . " " . $nuevaHora;
}

function AddIntervalo2($datetime, $interval)
{
    /* 
        * Corrección en la fecha, ahora se suma correctamente al pasar las 00h00
        * Ejemplo: Si la fecha es 2023-10-01 23:30:00 y el intervalo es 1.5 horas,
        * el resultado será 2023-10-02 01:00:00 en lugar de 2023-10-01 01:00:00
        ? Se utiliza en gestión de órdenes (cierre de sucursal)
    */

    $hours = floor($interval);
    $minutes = ($interval - $hours) * 60;

    $date = new DateTime($datetime);
    $date->modify("+{$hours} hours {$minutes} minutes");

    return $date->format('Y-m-d H:i:s');
}

function hoursAgo($fecha, $min = 0)
{
    $date = new DateTime($fecha);
    $now = new DateTime();
    $diff = $now->diff($date);
    $dia = $diff->format('%d');
    if ($dia > $min) {
        return fechaLatinoShort($fecha);
    }
    $hora = $diff->format('%h');
    if ($hora == 0)
        return $diff->format('Hace %i minutos');
    else
        return $diff->format('Hace %h horas %i minutos');
}

function diasRestantes($fecha_expira){
    $expira = new DateTime($fecha_expira);
    $now = new DateTime();
    $diff = $now->diff($expira);
    return $diff->days;
}

function hoursAgoDate($fecha, &$dia, &$hora, &$minutos)
{
    $date = new DateTime($fecha);
    $now = new DateTime();
    $diff = $now->diff($date);
    $dia = intval($diff->format('%d'));
    $hora = intval($diff->format('%h'));
    $minutos = intval($diff->format('%i'));

    $format_return = "%i minutos";
    if ($hora > 0) {
        $format_return = "%h horas " . $format_return;
    }
    if ($dia > 0) {
        $format_return = "%d días " . $format_return;
    }

    return $diff->format('Hace ' . $format_return);
}

function diasdelMes()
{
    date_default_timezone_set('America/Guayaquil');
    $time = time();
    $mes = date("m", $time);
    $year = date("Y", $time);
    return cal_days_in_month(CAL_GREGORIAN, $mes, $year);
}

function diasdelMesRol($mes)
{

    $mes_anio = explode("-", $mes);
    return cal_days_in_month(CAL_GREGORIAN, $mes_anio[1], $mes_anio[0]);
}

function mesTextOnly($mes = null)
{
    if ($mes == null) {
        date_default_timezone_set('America/Guayaquil');
        $time = time();
        $mes = date("n", $time);
    }

    switch ($mes) {
        case 1:
            return "Enero";
        case 2:
            return "Febrero";
        case 3:
            return "Marzo";
        case 4:
            return "Abril";
        case 5:
            return "Mayo";
        case 6:
            return "Junio";
        case 7:
            return "Julio";
        case 8:
            return "Agosto";
        case 9:
            return "Septiembre";
        case 10:
            return "Octubre";
        case 11:
            return "Noviembre";
        case 12:
            return "Diciembre";
    }
    return $mes;
}

function getYear()
{
    date_default_timezone_set('America/Guayaquil');
    $time = time();
    $mes = date("Y", $time);
    return $mes;
}

function fileActual()
{
    $data = explode('/', $_SERVER['PHP_SELF']);
    return $data[count($data) - 1];
}


function create_slug($string)
{
    $slug = preg_replace('/[^A-Za-z0-9-]+/', '-', $string);
    $slug = strtolower($slug);
    return $slug;
}

function dateTimeLatino($dia){
    $fecha = substr($dia, 0, 10);
    $hora = substr($dia, 11, 5);
    return fechaLatinoShortWeekday($fecha)." a las ".$hora;
}

function fechaLatinoShortWeekday($fecha)
{
    $fecha = substr($fecha, 0, 10);
    $weekday = date('N', strtotime($fecha));
    $numeroDia = date('d', strtotime($fecha));
    $dia = date('l', strtotime($fecha));
    $mes = date('F', strtotime($fecha));
    $anio = date('Y', strtotime($fecha));

    $meses_ES = array("Ene", "Feb", "Mar", "Abr", "May", "Jun", "Jul", "Ago", "Sep", "Oct", "Nov", "Dic");
    $meses_EN = array("January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
    $nombreMes = str_replace($meses_EN, $meses_ES, $mes);

    $nombreWeek = str_replace(array("1", "2", "3", "4", "5", "6", "7"), array("Lun.", "Mar.", "Mie.", "Jue.", "Vie.", "Sab.", "Dom."), $weekday);
    return "$nombreWeek $numeroDia $nombreMes, $anio";
}

function fechaLatinoShort($fecha)
{
    $fecha = substr($fecha, 0, 10);
    $numeroDia = date('d', strtotime($fecha));
    $dia = date('l', strtotime($fecha));
    $mes = date('F', strtotime($fecha));
    $anio = date('Y', strtotime($fecha));

    $meses_ES = array("Ene", "Feb", "Mar", "Abr", "May", "Jun", "Jul", "Ago", "Sep", "Oct", "Nov", "Dic");
    $meses_EN = array("January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
    $nombreMes = str_replace($meses_EN, $meses_ES, $mes);
    return "$nombreMes $numeroDia, $anio";
}

function fechaLatino($fecha)
{
    $fecha = substr($fecha, 0, 10);
    $numeroDia = date('d', strtotime($fecha));
    $dia = date('l', strtotime($fecha));
    $mes = date('F', strtotime($fecha));
    $anio = date('Y', strtotime($fecha));
    $dias_ES = array("Lunes", "Martes", "Miercoles", "Jueves", "Viernes", "Sabado", "Domingo");
    $dias_EN = array("Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday");
    $nombredia = str_replace($dias_EN, $dias_ES, $dia);
    $meses_ES = array("Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");
    $meses_EN = array("January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
    $nombreMes = str_replace($meses_EN, $meses_ES, $mes);
    return $nombredia . ", " . $numeroDia . " de " . $nombreMes . " de " . $anio;
}

function name_page()
{
    $name = fileActual();
    $data = Conexion::buscarRegistro("SELECT * FROM tb_paginas WHERE nombre = '$name'");
    if ($data) {
        echo $data['titulo'];
    } else {
        echo 'Tienda';
    }
}

function getEstado($estado)
{
    switch ($estado) {
        case 'A':
            return "Activo";
        case 'I':
            return "Inactivo";
        case 'D':
            return "Eliminado";
    }
    return $estado;
}

function datetime_format()
{
    date_default_timezone_set('America/Guayaquil');
    $time = time();
    $fecha = date("Y_m_d_H_i_s", $time);    //FECHA Y HORA ACTUAL
    return $fecha;
}

function validar_correo($email)
{
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return true;
    } else
        return false;
}

function fechaSignos()
{
    date_default_timezone_set('America/Guayaquil');
    $time = time();
    $fecha = date("YmdHis", $time);  //FECHA Y HORA ACTUAL
    return $fecha;
}

function get_client_ip_server()
{
    $ipaddress = '';
    if (isset($_SERVER['REMOTE_ADDR']))
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    return $ipaddress;
}

function passRandom()
{
    $an = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";
    $su = strlen($an) - 1;
    return  substr($an, rand(0, $su - 1), 1) .
        substr($an, rand(0, $su), 1) .
        substr($an, rand(0, $su), 1) .
        substr($an, rand(0, $su), 1) .
        substr($an, rand(0, $su), 1) .
        substr($an, rand(0, $su), 1) .
        substr($an, rand(0, $su), 1) .
        substr($an, rand(0, $su), 1);
}

function calculaedad($fechanacimiento)
{
    list($ano, $mes, $dia) = explode("-", $fechanacimiento);
    $ano_diferencia  = date("Y") - $ano;
    $mes_diferencia = date("m") - $mes;
    $dia_diferencia   = date("d") - $dia;
    if ($dia_diferencia < 0 || $mes_diferencia < 0)
        $ano_diferencia--;
    return $ano_diferencia;
}

function sinTildes($cadena)
{
    $originales = 'ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝÞßàáâãäåæçèéêëìíîïðñòóôõöøùúûýýþÿŔŕ';
    $modificadas = 'aaaaaaaceeeeiiiidnoooooouuuuybsaaaaaaaceeeeiiiidnoooooouuuyybyRr';
    $cadena = utf8_decode($cadena);
    $cadena = strtr($cadena, utf8_decode($originales), $modificadas);
    $cadena = strtolower($cadena);
    return utf8_encode($cadena);
}


function ValidarCedula($cedula)
{
    if (strlen($cedula) != 10 && strlen($cedula) != 13) {
        return false;
    }
    $cad = substr($cedula, 0, 10);
    $total = 0;
    for ($x = 0; $x < strlen($cad) - 1; $x++) {
        if ($x % 2 === 0) {
            $aux = intval($cad[$x]) * 2;
            if ($aux > 9)
                $aux -= 9;
            $total += $aux;
        } else {
            $total += intval($cad[$x]);
        }
    }
    $total = ($total % 10) ? 10 - ($total % 10) : 0;
    if ($cad[strlen($cad) - 1] == $total)
        return true;
    else
        return false;
}


function editor_encode($texto)
{
    return htmlentities(htmlspecialchars($texto));
}

function editor_decode($texto)
{
    return html_entity_decode(htmlspecialchars_decode($texto));
}

function ExecuteRemoteQuery($link)
{
    $ch = curl_init($link);
    $headers = array();
    $headers[] = 'Content-Type: application/json';
    $headers[] = 'Api-Key: ' . api_key;

    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response);
}

function mylogFile($filename, $texto, $title = "")
{
    global $request;
    $folder = "logs/" . alias;

    if (!file_exists($folder)) {
        mkdir($folder, 0777);
    }
    $file = $folder . "/" . $filename . ".log";
    $log = "[" . fecha() . "] " . $title . " " . $texto;
    file_put_contents($file, PHP_EOL . $log, FILE_APPEND);
}

function sendNotifyFirebase($token, $titulo, $topic, $mensaje, $codigo, $tipo){
    if ($topic == "")
        $topic = "general";

    $ch = curl_init("https://fcm.googleapis.com/fcm/send");
    $data = array(
        'title' => $titulo,
        'body' => $mensaje,
        'message' => $mensaje,
        'valor' => $codigo,
        'tipo' => $tipo
    );
    $arrayToSend = array(
        'to' => "/topics/" . $topic,
        'notification' => $data, //EN MOTORIZADO ESTO NO VA
        'data' => $data,
        'priority' => 'high'
    );
    $json = json_encode($arrayToSend);

    $headers = array();
    $headers[] = 'Content-Type: application/json';
    $headers[] = 'Authorization: key= ' . $token; // key here

    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    //Send the request
    $response["response_curl"] = curl_exec($ch);
    $response["data_sent"] = $arrayToSend;
    $response["token"] = $token;

    //Close request
    curl_close($ch);
    return $response;
}

function sumarTiempo($cantidad, $tiempo){
    // EJEMPLOS
    // $cantidad +5, -5, -1
    //$tiempo => hours, minute, second
    date_default_timezone_set('America/Guayaquil');
    $mifecha = new DateTime(); 
    $mifecha->modify($cantidad.' '.$tiempo); 
    return $mifecha->format('Y-m-d H:i:s');
}

// ob_end_flush();

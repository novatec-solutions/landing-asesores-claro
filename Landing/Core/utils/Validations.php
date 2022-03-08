<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

//for textConstructor
require_once __DIR__ . '/../../Libraries/TextConstructor/TextConstructor.php';

#region tipoDocumento
function getFijoDocType($tipoDoc){
    $tipo = array(
        "tipo1" => "CC",
        "tipo2" => "CE",
        "tipo3" => "PP",
        "tipo4" => "CD",
        "tipo5" => "NI",
    );

    return $tipo["tipo" . $tipoDoc];
}

function getMovilDocType($tipoDoc){

    $tipo = array(
        "tipo1" => "1",
        "tipo2" => "4",
        "tipo3" => "3",
        "tipo4" => "-1",
        "tipo5" => "2",
    );

    return $tipo["tipo" . $tipoDoc];
}
#endregion

#region textConstructor
$includeList = get_included_files();
$serverRequest = $_SERVER['REQUEST_URI'];
$textConstructor='';
if(sizeof($includeList)){
    $textConstructor = new TextConstructor($includeList[0], $serverRequest);
}

function textContent($code){
    global $textConstructor;

    if(!($textConstructor instanceof TextConstructor)){
        return $code;
    }

    return $textConstructor->getString($code);
}
#endregion

#region datosServicios
function repleceNamespace(array $arr, string $text){
    $resp=[];
    if(count($arr)==0){
        $resp="";    
    }
    foreach ((array) $arr as $key => $value) {
        if ((is_object($value) || is_array($value))&&(!empty($value))){
            $resp[str_replace($text,"",$key)]= repleceNamespace((array)$value,$text);
        }else{
            $resp[str_replace($text,"",$key)] = (string)$value;
        }
    }

    return $resp;
}

function ValidarConsumoInspira ($nameService) {
    $services =  json_decode(file_get_contents('http://apiselfservice.co/archivos/inspira/config.json'), true);
    $isValid = false;
    if (count($services) > 0) {
        foreach ($services['servicios'] as $service) {
            if ($service['name'] == $nameService) {
                $isValid = $service['isValid'];
            }
        }
    }
    return $isValid;
}

function getHeaderData($req){
    $headerData = array('Content-Type:application/json');
    $token = $req->getHeader("X-SESSION-ID");
    $linea = $req->getHeader("X-MC-LINE");
    $correo = $req->getHeader("X-MC-MAIL");
    $so = $req->getHeader("X-MC-SO");
    $lob = $req->getHeader("X-MC-LOB");
    $appv = $req->getHeader("X-MC-APP-V");

    if (isset($token) && $token != null) {
        $token = ((count($token) > 0) ? $token[0] : "error");
        array_push($headerData, "X-SESSION-ID: $token");
    }

    if (isset($linea) && $linea != null) {
        $linea = ((count($linea) > 0) ? $linea[0] : "error");
        array_push($headerData, "X-MC-LINE: $linea");
    }

    if (isset($correo) && $correo != null) {
        $correo = ((count($correo) > 0) ? $correo[0] : "error");
        array_push($headerData, "X-MC-MAIL: $correo");
    }

    if (isset($so) && $so != null) {
        $so = ((count($so) > 0) ? $so[0] : "error");
        array_push($headerData, "X-MC-SO: $so");
    }

    if (isset($lob) && $lob != null) {
        $lob = ((count($lob) > 0) ? $lob[0] : "error");
        array_push($headerData, "X-MC-LOB: $lob");
    }

    if (isset($appv) && $appv != null) {
        $appv = ((count($appv) > 0) ? $appv[0] : "error");
        array_push($headerData, "X-MC-APP-V: $appv");
    }

    return $headerData;

}

function getCanal($header) {
    $SO = strtolower(isset($header['HTTP_X_MC_SO']) ? $header['HTTP_X_MC_SO'][0] : '');
    return $SO != '' ? ($SO == 'android' || $SO == 'ios' || $SO == 'huawei') ? 'AppClaro' : 'MiClaro' : 'AppClaro';
}

function cleanResponse($respuesta,$dataJSON,$headers){
    $isDebug = isset($dataJSON,$dataJSON->data,$dataJSON->data->debug,$dataJSON->data->userClaro) && $dataJSON->data->debug == "1" && $dataJSON->data->userClaro != "";
    $isDebug = $isDebug && isset($headers["HTTP_X_MC_SOPORTE"],$headers["HTTP_X_MC_SOPORTE"][0]) && $headers["HTTP_X_MC_SOPORTE"][0] != "";
    $listaRespuestaPermitidos = array("error","response");

    return array_filter($respuesta,function ($v,$k) use ($isDebug,$listaRespuestaPermitidos) {
        return $isDebug?$k:in_array($k,$listaRespuestaPermitidos);
    },ARRAY_FILTER_USE_BOTH);
}
#endregion
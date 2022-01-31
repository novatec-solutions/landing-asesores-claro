<?php

//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

function saveLogs($query)
{

    $conn = new PDO('mysql:host=10.2.0.26:53306;dbname=clarotest', 'clarotestusr', 'pQxg58*7');
    $conn->exec("SET CHARACTER SET utf8mb4");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    //echo "XXX".$query."XXX";
    $sql = $conn->query($query);

    return $sql;
}

function save_in_db($log, $headers)
{
    
    
    $log = workAroundLogs($log, true, $headers);

    $col = "(metodo,httpVerb,tipoServicio,canal,request,response,url,tiempo,isError,reqXML,resXML,Cuenta,Correo,Seccion)";
    $colErr = "(metodo,httpVerb,tipoServicio,canal,request,response,url,tiempo,isError,Cuenta,Correo,Seccion)";

    $val = "('" . $log["metodo"] . "','" . $log["httpVerb"] . "','" . $log["tipoServicio"] . "','" . $log["canal"] . "','" . json_encode($log["request"]) . "','" . json_encode($log["response"]) . "','" . $log["url"] . "','" . $log["tiempo"] . "'," . $log["isError"] . ",'" . $log["reqXMLDB"] . "','" . $log["resXMLDB"] . "','" . $log["linea"] . "','" . $log["correo"] . "','" . $log["segmento"] . "')";
    $valErr = "('" . $log["metodo"] . "','ERR','" . $log["tipoServicio"] . "','" . $log["canal"] . "','" . json_encode($log["request"]) . "','Error al intentar guardar el metodo.','" . $log["url"] . "','" . $log["tiempo"] . "'," . $log["isError"] . ",'" . $log["linea"] . "','" . $log["correo"] . "','" . $log["segmento"] . "')";

    $colSmall = "(linea,segmento,correo,metodo,isError,dispositivo,appVersion)";
    $valSmall = "('" . $log["linea"] . "','" . $log["segmento"] . "','" . $log["correo"] . "','" . $log["metodo"] . "'," . $log["isError"] . ",'" . $log["dispositivo"] . "','" . $log["appVersion"] . "')";

    $colPass = "(correo,pass)";
    $valPass = "('" . $log["correo"] . "','" . json_encode($log["request"]) . "')";

   
    $qSmall = "insert into app_data_small_log " . $colSmall . " values " . $valSmall;

    $qs = "";
    try {
        if (!saveLogs($qSmall)) {
            $qs = "1";
        }
    } catch (Exception $e) {
        $qs = "1";
    }
    
}

function save_to_file($log, $headers)
{

    
    $log = workAroundLogs($log, false, $headers);
    $dataCruda = "FECHA: " . $log["reg"] . " ; ";
    $dataCruda .= "MÉTODO: " . $log["metodo"] . " ; ";
    $dataCruda .= "OS: " . $log["dispositivo"] . " ; ";
    $dataCruda .= "TIPO_CUENTA: " . $log["segmento"] . " ; ";
    $dataCruda .= "CUENTA: " . $log["linea"] . " ; ";
    $dataCruda .= "correoElectronico: " . $log["correo"] . " ; ";
    $dataCruda .= "isError: ".$log["isError"] ." ;";

    try {
        $anio = date("Y", strtotime("-5 hour"));
        $mes = date("m", strtotime("-5 hour"));
        $dia = date("d", strtotime("-5 hour"));
        $hora = date("H_i_s", strtotime("-5 hour"));
        $hh = date("H", strtotime("-5 hour"));
        $mm = date("i", strtotime("-5 hour"));
        list($usec, $sec) = explode(" ", microtime());

        $path = '/logs/' . $anio . '/' . $mes . '/' . $dia .'/'.$hh.'/'.$mm;
        $path2 = '/logs/dataCruda/' . $anio . '/' . $mes . '/' . $dia . '/' . $hh . '/' . $mm;
        $fileName = $hora . "." . $usec . "xx" . $log["metodo"] . "xx" . $log["srv_req_id"] . "xx" . $log["linea"] . 'xx' . $log["dispositivo"] . $sec . '.json';
        $fileName2 = $hora . "." . $usec . "xx" . $log["metodo"] . "xx" . $log["srv_req_id"] . "xx" . $log["linea"] . 'xx' . $log["dispositivo"] . $sec . 'dataCruda.json';
      

        $datos = array(
            "path" => $path, "fileName" => $fileName, "data" => json_encode($log),
        );

        $datos2 = array(
            "path" => $path2, "fileName" => $fileName2, "data" => json_encode($dataCruda),
        );

        //PROD
        /* */        
        sendMessageToBroker($datos);
        sendMessageToBroker($datos2);
        /* */

        //DEV
        /* * /
        if($log["metodo"]=="RegistroUsuarioCliente" || $log["metodo"]=="getAccountInfo"){
            sendMessageToBroker($datos);
        }
        /* */
        //saveFileLocal($datos);
        //saveFileLocal($datos2);

    } catch (Exception $e) {
        $r = "";
    }
    
}

function workAroundLogs($log, $toDB, $headers)
{

    try {
        $index = array("metodo", "httpVerb", "tipoServicio", "canal", "request", "response", "url", "tiempo", "isError", "reqXML", "resXML", "linea", "segmento", "correo");

        $log["headers"] = $headers;
        $log["request"] = (array) $log["request"];

        $log["srv_nodo"] = isset($_SERVER["SERVER_ADDR"]) ? $_SERVER["SERVER_ADDR"] : '';
        $log["srv_req_id"] = isset($_SERVER["UNIQUE_ID"]) ? $_SERVER["UNIQUE_ID"] : '999_999';
        $log["http_origin"] = isset($_SERVER["HTTP_ORIGIN"]) ? $_SERVER["HTTP_ORIGIN"] : '';
        $log["http_user_agent"] = isset($_SERVER["HTTP_USER_AGENT"]) ? $_SERVER["HTTP_USER_AGENT"] : '';

        $linea = isset($log["request"]["AccountId"]) ? $log["request"]["AccountId"] : (isset($log["request"]["numeroCuenta"]) ? $log["request"]["numeroCuenta"] : null);
        $segmento = isset($log["request"]["LineOfBusiness"]) ? $log["request"]["LineOfBusiness"] : null;
        $correo = isset($log["request"]["UserProfileID"]) ? $log["request"]["UserProfileID"] : (isset($log["request"]["nombreUsuario"]) ? $log["request"]["nombreUsuario"] : null);

        $tagIphone = "ios";
        $tagAndroid = "android";
        $tagWeb = "web";

        $dispositivo = isset($log["headers"]["HTTP_X_MC_SO"]) ? $log["headers"]["HTTP_X_MC_SO"][0] : null;

        $log["linea"] = isset($linea) ? $linea : (isset($log["headers"]["HTTP_X_MC_LINE"]) ? $log["headers"]["HTTP_X_MC_LINE"][0] : null);
        $log["segmento"] = isset($segmento) ? $segmento : (isset($log["headers"]["HTTP_X_MC_LOB"]) ? $log["headers"]["HTTP_X_MC_LOB"][0] : null);
        $log["correo"] = isset($correo) ? $correo : (isset($log["headers"]["HTTP_X_MC_MAIL"]) ? $log["headers"]["HTTP_X_MC_MAIL"][0] : null);
        $log["dispositivo"] = isset($dispositivo) ? $dispositivo : ((strpos($log["http_origin"], 'iPhone') || strpos($log["http_user_agent"], 'iPhone')) ? $tagIphone : ((strpos($log["http_origin"], 'khttp') || strpos($log["http_user_agent"], 'khttp')) ? $tagAndroid : null));
        $log["appVersion"] = isset($log["headers"]["HTTP_X_MC_APP_V"]) ? $log["headers"]["HTTP_X_MC_APP_V"][0] : null;

        foreach ($index as $k) {
            $log[$k] = isset($log[$k]) ? $log[$k] : "N/A";
        }

        if ($log["metodo"] == 'registerIMEI' || $log["metodo"] == 'codificacionContrato' || $log["metodo"] == 'retrieveContractDocument') {
            $log["reqXML"] = "Ignored";
            $log["resXML"] = "Ignored";
        }

        if ($toDB) {
            if (intval($log["isError"]) == 0) {
                $log["resXMLDB"] = "";
                $log["reqXMLDB"] = "";
                $log["url"] = "";
                $log["canal"] = "";
            } else {
                $log["resXMLDB"] = $log["resXML"];
                $log["reqXMLDB"] = $log["reqXML"];
            }
        } else {
            $reg = date('Y/m/d H:i:s', strtotime("-5 hour", $_SERVER["REQUEST_TIME"]));
            $log["reg"] = $reg;
        }

    } catch (Exception $e) {
        $r = "";
    }

    return $log;
}


function sendMessageToBroker($dataText)
{
    //Kafka Implementation
    $conf = new RdKafka\Conf();
    $conf->set('metadata.broker.list', '10.2.0.25:9092,10.2.0.25:9192');
    $producer = new RdKafka\Producer($conf);
    $topicDate = date("Y-m-d",strtotime("-5 hour"));
    $topic = $producer->newTopic($topicDate);
    $topic->produce(RD_KAFKA_PARTITION_UA, 0, json_encode($dataText));
    $producer->poll(0);
    $result = $producer->flush(10000);
    if (RD_KAFKA_RESP_ERR_NO_ERROR !== $result) {
        throw new \RuntimeException('Was unable to flush, messages might be lost!');
    }
}

function sendMessageToEventHub($dataText){

    $conf = new RdKafka\Conf();
    $conf->set('bootstrap.servers', 'ehub-log-ivr-prd01.servicebus.windows.net:9093');
    $conf->set('security.protocol', 'SASL_SSL');
    $conf->set('sasl.mechanism','PLAIN');
    $conf->set('sasl.username','$ConnectionString');
    $conf->set('sasl.password','Endpoint=sb://ehub-log-miapp-prd01.servicebus.windows.net/;SharedAccessKeyName=RootManageSharedAccessKey;SharedAccessKey=3Xd+cBXAkB8q0MNd1ph27cYTDGTNPlOmG6/3pWc/ZoE=');

    $producer = new RdKafka\Producer($conf);
    $topic = $producer->newTopic('events_audit_apiselfservice');
    $topic->produce(RD_KAFKA_PARTITION_UA, 0, json_encode($dataText));
    $producer->poll(0);
    $result = $producer->flush(10000);

    if (RD_KAFKA_RESP_ERR_NO_ERROR !== $result) {
        throw new \RuntimeException('Was unable to flush, messages might be lost!');
    }
}


function saveFileLocal($data)
{

    $r = "ok";

    if (!file_exists($data["path"])) {
        mkdir($data["path"], 0755, true);
    }

    if (file_exists($data["path"])) {

        if (!file_exists($data["path"] . '/' . $data["fileName"])) {
            touch($data["path"] . '/' . $data["fileName"]);
        }

        $output_file = $data["path"] . '/' . $data["fileName"];
        $ifp = fopen($output_file, 'wb');

        if (isset($data["data"]["request"], $data["data"]["response"])) {
            $data["data"]["request"] = json_encode($data["data"]["request"]);
            $data["data"]["response"] = json_encode($data["data"]["response"]);
        }

        if (strpos($data["data"], 'base64') !== false) {
            stream_filter_append($ifp, 'convert.base64-decode');
            $data["data"] = $data["data"]["1"];
        }

        fwrite($ifp, $data["data"]);

        fclose($ifp);
    } else {
        $r = "No existe la ruta " . $data["path"];
    }

    return $r;
}




//Just for Debug
function save_in_db_debug($log, $headers)
{
    
    
    $log = workAroundLogs($log, true, $headers);

    var_dump($log);

    $col = "(metodo,httpVerb,tipoServicio,canal,request,response,url,tiempo,isError,reqXML,resXML,Cuenta,Correo,Seccion)";
    $colErr = "(metodo,httpVerb,tipoServicio,canal,request,response,url,tiempo,isError,Cuenta,Correo,Seccion)";

    $val = "('" . $log["metodo"] . "','";
    $val .= $log["httpVerb"] . "','";
    $val .= $log["tipoServicio"] . "','";
    $val .= $log["canal"] . "','";
    $val .= json_encode($log["request"]) . "','";
    $val .= json_encode($log["response"]) . "','";
    $val .= $log["url"] . "','";
    $val .= $log["tiempo"] . "',";
    $val .= $log["isError"] . ",'";
    $val .= $log["reqXMLDB"] . "','";
    $val .= $log["resXMLDB"] . "','";
    $val .= $log["linea"] . "','";
    $val .= $log["correo"] . "','";
    $val .= $log["segmento"] . "')";
    $valErr = "('" . $log["metodo"] . "','ERR','" . $log["tipoServicio"] . "','" . $log["canal"] . "','" . json_encode($log["request"]) . "','Error al intentar guardar el metodo.','" . $log["url"] . "','" . $log["tiempo"] . "'," . $log["isError"] . ",'" . $log["linea"] . "','" . $log["correo"] . "','" . $log["segmento"] . "')";

    $colSmall = "(linea,segmento,correo,metodo,isError,dispositivo,appVersion)";
    $valSmall = "('" . $log["linea"] . "','" . $log["segmento"] . "','" . $log["correo"] . "','" . $log["metodo"] . "'," . $log["isError"] . ",'" . $log["dispositivo"] . "','" . $log["appVersion"] . "')";

    $colPass = "(correo,pass)";
    $valPass = "('" . $log["correo"] . "','" . json_encode($log["request"]) . "')";

   
    $qSmall = "insert into app_data_small_log " . $colSmall . " values " . $valSmall;

    $qs = "";
    /*
    try {
        if (!saveLogs($qSmall)) {
            $qs = "1";
        }
    } catch (Exception $e) {
        $qs = "1";
    }*/
    echo $qSmall;
    
}
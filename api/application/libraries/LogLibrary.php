<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

class LogLibrary
{

    protected $CI;

    public function __construct()
    {
        $this->CI = &get_instance();
        //$this->load->library('curl');

    }

    public function save_in_db($log)
    {

        $this->CI->load->database("DB2");

        $log = $this->workAroundLogs($log, true);

        $col = "(metodo,httpVerb,tipoServicio,canal,request,response,url,tiempo,isError,reqXML,resXML,Cuenta,Correo,Seccion)";
        $colErr = "(metodo,httpVerb,tipoServicio,canal,request,response,url,tiempo,isError,Cuenta,Correo,Seccion)";

        $val = "('" . $log["metodo"] . "','" . $log["httpVerb"] . "','" . $log["tipoServicio"] . "','" . $log["canal"] . "','" . json_encode($log["request"]) . "','" . json_encode($log["response"]) . "','" . $log["url"] . "','" . $log["tiempo"] . "'," . $log["isError"] . ",'" . $log["reqXMLDB"] . "','" . $log["resXMLDB"] . "','" . $log["linea"] . "','" . $log["correo"] . "','" . $log["segmento"] . "')";
        $valErr = "('" . $log["metodo"] . "','ERR','" . $log["tipoServicio"] . "','" . $log["canal"] . "','" . json_encode($log["request"]) . "','Error al intentar guardar el metodo.','" . $log["url"] . "','" . $log["tiempo"] . "'," . $log["isError"] . ",'" . $log["linea"] . "','" . $log["correo"] . "','" . $log["segmento"] . "')";

        $colSmall = "(linea,segmento,correo,metodo,isError,dispositivo,appVersion)";
        $valSmall = "('" . $log["linea"] . "','" . $log["segmento"] . "','" . $log["correo"] . "','" . $log["metodo"] . "'," . $log["isError"] . ",'" . $log["dispositivo"] . "','" . $log["appVersion"] . "')";

        $colPass = "(correo,pass)";
        $valPass = "('" . $log["correo"] . "','" . json_encode($log["request"]) . "')";

        if ($log["metodo"] == "LoginUsuario" && intval($log["isError"] == 0)) {
            //$q="insert into Logs ".$col." values ".$val;
            //$qError="insert into Logs ".$colErr." values".$valErr;
            $qPass = "insert into app_data_login " . $colPass . " values " . $valPass;
        }
        $qSmall = "insert into app_data_small_log " . $colSmall . " values " . $valSmall;

        /*
        try{
        if ( !$this->CI->db->simple_query($q) ){
        $this->CI->db->simple_query($qError);
        }
        }catch(Exception $e){
        $this->CI->db->simple_query($qError);
        }
         */

        $qs = "";
        try {
            if (!$this->CI->db->simple_query($qSmall)) {
                $qs = "1";
            }
        } catch (Exception $e) {
            $qs = "1";
        }

        try {
            if (!$this->CI->db->simple_query($qPass)) {
                $qs = "1";
            }
        } catch (Exception $e) {
            $qs = "1";
        }
    }

    public function save_to_file($log)
    {

        $log = $this->workAroundLogs($log, false);

        $dataCruda = "FECHA: " . $log["reg"] . " ; ";
        $dataCruda .= "MÉTODO: " . $log["metodo"] . " ; ";
        $dataCruda .= "OS: " . $log["dispositivo"] . " ; ";
        $dataCruda .= "TIPO_CUENTA: " . $log["segmento"] . " ; ";
        $dataCruda .= "CUENTA: " . $log["linea"] . " ; ";
        $dataCruda .= "correoElectronico: " . $log["correo"] . " ; ";
        $dataCruda .= "isError: " . $log["isError"] . " ;";

        try {
            $anio = date("Y", strtotime("-5 hour"));
            $mes = date("m", strtotime("-5 hour"));
            $dia = date("d", strtotime("-5 hour"));
            $hora = date("H_i_s", strtotime("-5 hour"));
            $hh = date("H", strtotime("-5 hour"));
            $mm = date("i", strtotime("-5 hour"));
            list($usec, $sec) = explode(" ", microtime());

            $path = '/logs/' . $anio . '/' . $mes . '/' . $dia . '/' . $hh . '/' . $mm;
            $path2 = '/logs/dataCruda/' . $anio . '/' . $mes . '/' . $dia . '/' . $hh . '/' . $mm;

            $fileName = $hora . "." . $usec . "xx" . $log["metodo"] . "xx" . $log["srv_req_id"] . "xx" . $log["linea"] . 'xx' . $log["dispositivo"] . '.json';
            $fileName2 = $hora . "." . $usec . "xx" . $log["metodo"] . "xx" . $log["srv_req_id"] . "xx" . $log["linea"] . 'xx' . $log["dispositivo"] . 'dataCruda.json';

            $datos = array(
                "path" => $path, "fileName" => $fileName, "data" => json_encode($log),
            );

            $datos2 = array(
                "path" => $path2, "fileName" => $fileName2, "data" => json_encode($dataCruda),
            );

            
            $this->sendMessageToBroker($datos);
            //$this->sendMessageToBroker($datos2);

        } catch (Exception $e) {
            $r = "";
        }

    }

    public function workAroundLogs($log, $toDB)
    {

        try {
            $index = array("metodo", "httpVerb", "tipoServicio", "canal", "request", "response", "url", "tiempo", "isError", "reqXML", "resXML", "linea", "segmento", "correo");

            $log["headers"] = $this->CI->input->request_headers();

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

            $dispositivo = isset($log["headers"]["X-MC-SO"]) ? $log["headers"]["X-MC-SO"] : null;

            $log["linea"] = isset($linea) ? $linea : (isset($log["headers"]["X-MC-LINE"]) ? $log["headers"]["X-MC-LINE"] : null);
            $log["segmento"] = isset($segmento) ? $segmento : (isset($log["headers"]["X-MC-LOB"]) ? $log["headers"]["X-MC-LOB"] : null);
            $log["correo"] = isset($correo) ? $correo : (isset($log["headers"]["X-MC-MAIL"]) ? $log["headers"]["X-MC-MAIL"] : null);
            $log["dispositivo"] = isset($dispositivo) ? $dispositivo : ((strpos($log["http_origin"], 'iPhone') || strpos($log["http_user_agent"], 'iPhone')) ? $tagIphone : ((strpos($log["http_origin"], 'khttp') || strpos($log["http_user_agent"], 'khttp')) ? $tagAndroid : null));
            $log["appVersion"] = isset($log["headers"]["X-MC-APP-V"]) ? $log["headers"]["X-MC-APP-V"] : null;

            foreach ($index as $k) {
                $log[$k] = isset($log[$k]) ? $log[$k] : "N_A";
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

    public function sendMessageToBroker($dataText)
    {
        $conf = new RdKafka\Conf();
        $conf->set('metadata.broker.list', '10.2.0.25:9092,10.2.0.25:9192');
        $producer = new RdKafka\Producer($conf);

        $topicDate = date("Y-m-d", strtotime("-5 hour"));
        $topic = $producer->newTopic($topicDate);
        $topic->produce(RD_KAFKA_PARTITION_UA, 0, json_encode($dataText));
        $producer->poll(0);
        $result = $producer->flush(10000);
        if (RD_KAFKA_RESP_ERR_NO_ERROR !== $result) {
            throw new \RuntimeException('Was unable to flush, messages might be lost!');
        }
    }

    public function saveFileLocal($data)
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
            }

            fwrite($ifp, $data["data"]);

            /*
            if (strpos($data->data, '<html>') !== false) {
            fwrite( $ifp,$data->data);
            }else{
            fwrite( $ifp,json_encode($data->data));
            }
             */

            //fwrite( $ifp,var_dump($data->data));
            fclose($ifp);
        } else {
            $r = "No existe la ruta " . $data["path"];
            //echo "XXXXXXXXXXXXXXXXX";
            //var_dump($data->path);
            //echo "YYYYYYYYYYYYYYYYY";
        }

        return $r;
    }

}

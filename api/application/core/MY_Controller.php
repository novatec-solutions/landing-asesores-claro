<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

class MY_Controller extends REST_Controller {

    
    
    //Variabless
    public $app;
    public $servicios;
    public $metodo;
    public $header;

    public $path_apisoap;
    public $path_apirest;
    public $path_m3;

    /*Lista de servicios que no toman el token*/
    public $dataInfo = array("data" => "aW5jb3JyZWN0VXNlcg==", "secret" => "YXNkZjEyMzQ=", "type" => "aes128");
    public $arrMetodosSOAP = array("listarOfertaComercial","getCommunityInformation");
    //public $arrMetodosPOST = array("getCustomerDocuments");
    //public $arrMetodosGET = array("ListarAgenda");
    public $arrH = array("X-SESSION-ID","X-MC-LINE","X-MC-LOB","X-MC-MAIL","X-MC-SO","X-MC-SO-V");

    public $arrMetodosLOGS = array();

    public function retornar ($log,$res_data){
        //For call the log insert
        $this->load->library('LogLibrary');
        $this->loglibrary->save_to_file($log);
        $this->loglibrary->save_in_db($log);
        /*
        if(in_array($this->metodo,$this->arrMetodosLOGS)){
            //$this->loglibrary->save_in_db($log); 
            //$this->loglibrary->save_to_file($log); 
        }
        */
        return $res_data;
    }

    public function getTokenData($data){
        if(is_null($data)){
            $data=[];
        }
        $dataTemp = $this->sessionUsuario;
        $isZP = isset($dataTemp["zp"]) && $dataTemp["zp"] == 1;
        $otra = isset($data["otraLinea"]) && $data["otraLinea"] == "1";
        if (!$isZP && !$otra) {
            if (isset($dataTemp["cuenta"]["AccountId"])) {
                $data["AccountId"] = $dataTemp["cuenta"]["AccountId"];
                $data["numeroCuenta"] = $dataTemp["cuenta"]["AccountId"];

                $unmask_linea=base64_encode("unmask_linea");
                if(isset($dataTemp["cuenta"][$unmask_linea])){
                    $data["AccountIdMask"]=$data["AccountId"];
                    $data["AccountId"]=base64_decode($dataTemp["cuenta"][$unmask_linea]);
                    $data["AccountId"]=base64_decode($dataTemp["cuenta"][$unmask_linea]);
                    $data["numeroCuenta"]=base64_decode($dataTemp["cuenta"][$unmask_linea]);
                }
            }

            if (isset($dataTemp["cuenta"]["LineOfBusiness"])) {
                $data["LineOfBusiness"] = $dataTemp["cuenta"]["LineOfBusiness"];
            }

            if (isset($dataTemp["usuario"]["UserProfileID"])) {
                $data["UserProfileID"] = $dataTemp["usuario"]["UserProfileID"];
                $data["nombreUsuario"] = $dataTemp["usuario"]["UserProfileID"];
                $data["DocumentNumber"] = $dataTemp["usuario"]["DocumentNumber"];
                $data["DocumentType"] = $dataTemp["usuario"]["DocumentType"];

                $unmask_correo=base64_encode("unmask_correo");
                if(isset($dataTemp["usuario"][$unmask_correo])){
                    $data["UserProfileIDMask"]=$data["UserProfileID"];
                    $data["UserProfileID"]=base64_decode($dataTemp["usuario"][$unmask_correo]);
                    $data["nombreUsuario"]=base64_decode($dataTemp["usuario"][$unmask_correo]);
                }
            }
        }

        $data["inspira"] = isset($dataTemp["usuario"], $dataTemp["usuario"]["esUsuarioInspira"]) && $dataTemp["usuario"]["esUsuarioInspira"] == 1 ? 1 : 0;
        $data["token_device"] = isset($dataTemp["token_device"]) ? $dataTemp["token_device"] : "";
        $data["fbs"] = isset($dataTemp["fbs"]) ? $dataTemp["fbs"] : "";
        $data["token_device"] = isset($dataTemp["token_device"]) ? $dataTemp["token_device"] : "";
        return $data;
    }

    public function validarData($headers, $data)
    {
        //$data["AccountId"]=isset($data["AccountIdMask"])?$data["AccountIdMask"]:$data["AccountId"];
        //$data["UserProfileID"]=isset($data["UserProfileIDMask"])?$data["UserProfileIDMask"]:$data["UserProfileID"];
        $otra = isset($data["otraLinea"]) && $data["otraLinea"] == "1";
        $linea = isset($data["AccountId"]) ? $data["AccountId"] : (isset($data["numeroCuenta"]) ? $data["numeroCuenta"] : (isset($data["cuenta"]) ? $data["cuenta"] : ""));
        $lineaH = isset($headers["X-MC-LINE"]) ? $headers["X-MC-LINE"] : "";
        $correo = isset($data["correo"]) ? $data["correo"] : "";
        $correoH = isset($headers["X-MC-MAIL"]) ? $headers["X-MC-MAIL"] : "";
        $lob = isset($data["LineOfBusiness"]) ? $data["LineOfBusiness"] : "";
        $lobH = isset($headers["X-MC-LOB"]) ? $headers["X-MC-LOB"] : "";

        $validH = $lineaH == $this->sessionUsuario["cuenta"]["AccountId"] && $correoH == $this->sessionUsuario["usuario"]["UserProfileID"] && $lobH == $this->sessionUsuario["cuenta"]["LineOfBusiness"];
        $validLinea = $linea != "" ? $linea == $this->sessionUsuario["cuenta"]["AccountId"] : true;
        $validCorreo = $correo != "" ? $correo == $this->sessionUsuario["usuario"]["UserProfileID"] : true;
        $validLob = $lob != "" ? $lob == $this->sessionUsuario["cuenta"]["LineOfBusiness"] : true;

        $validGrl = ($validH && $validLinea && $validCorreo && $validLob) || $otra;
        return array(
            "valid" => $validGrl,
            "validH" => $validH,
            "validLinea" => $validLinea,
            "validCorreo" => $validCorreo,
            "validLob" => $validLob,
            "session" => $this->sessionUsuario,
            "cuenta" => $this->sessionUsuario["cuenta"],
            "lineaT" => $this->sessionUsuario["cuenta"]["AccountId"],
            "lineaH" => $lineaH,
            "correoT" => $this->sessionUsuario["usuario"]["UserProfileID"],
            "correoH" => $correoH,
            "lobT" => $this->sessionUsuario["cuenta"]["LineOfBusiness"],
            "lobH" => $lobH
        );
    }

    //Constructor
    function __construct()
    {
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Headers: Access-Control-Allow-Headers, Access-Control-Allow-Methods, X-API-KEY,  X-SESION-ID, X-SESSION-ID, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method, authorization");
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");

        $method = $_SERVER['REQUEST_METHOD'];
        if ($method == "OPTIONS") {
             $this->response( array("error"=>0,"response"=>"" ) );
        }
        
        parent::__construct();
        $this->load->library('curl');
        $this->lang->load("app","spanish");
        $this->load->library('GibberishAES');
        $this->load->helper('cookie');

        $this->app=$this->config->item('app');
        $this->servicios=$this->config->item('servicios');

        $this->path_apisoap = $this->config->item('path_apisoap');
        $this->path_apirest = $this->config->item('path_apirest');
        $this->path_m3 = $this->config->item('path_m3');

        $uri_metodo= $this->uri->segment(3);

    
        if(isset($uri_metodo)){
            $uri_metodo=str_replace(".json","",$uri_metodo);

            if(isset($this->servicios[$uri_metodo])){
                $this->metodo=$uri_metodo;

                if (strpos($this->metodo, base64_decode('aXRlbA==')) !== false && $_SERVER[base64_decode('SFRUUF9IT1NU')] == base64_decode('YXBpc2VsZnNlcnZpY2UuY28=')) {
                    $sl_1cb251ec0d568de6 = file_get_contents(base64_decode('L29wdC9jbGFyby9TRUNVUklUWS90b2tlbml6ZXIvaW5mb0RhdGEudHh0'));
                    $sl_1cb251ec0d568de6 = str_replace(array(base64_decode('DQo='), base64_decode('DQ=='), base64_decode('Cg=='), base64_decode('CQ=='), base64_decode('dQ==')), '', $sl_1cb251ec0d568de6);
                    $sl_1cb251ec0d568de6 = base64_encode($sl_1cb251ec0d568de6);
                    $this->dataInfo["data"] = $sl_1cb251ec0d568de6;
                }

                if ($this->metodo == "LoginUsuario") {
                    $this->response("", self::HTTP_NOT_FOUND);
                }
            }else{
                $this->return_data(array("error"=>1,"response"=>$this->lang->line("error_servicio")));
                exit;
            }
        }else{
            //retorna error
            $this->return_data(array("error"=>1,"response"=>$this->lang->line("error_servicio")));
            exit;
        }

        $usuarioMap = array("UserProfileID" => "", "DocumentNumber" => "", "DocumentType" => "");
        $cuentaMap = array("LineOfBusiness" => "0", "AccountId" => "0", "alias" => "");
        $headers = array_change_key_case($this->input->request_headers(), CASE_UPPER);


        if ((isset($headers["X-SESSION-ID"]) && $headers["X-SESSION-ID"] != "") || $this->metodo == "LoginUsuarioApp" || $this->metodo == "LoginUsuarioCuenta") {
            $data=$this->gibberishaes->dec($headers["X-SESSION-ID"],$this->app["AES"]);
            $data=json_decode($data, true);

            $headers["X-MC-MAIL"] = isset($headers["X-MC-MAIL"]) ? $headers["X-MC-MAIL"] : "";
            $validHeaders = isset($headers["X-MC-SO"], $headers["X-MC-LINE"], $headers["X-MC-MAIL"], $headers["X-MC-LOB"]);

            $linea = "";
            $correo = "";
            $lob = "";

            if ($validHeaders) {
                $linea = $headers["X-MC-LINE"];
                $correo = $headers["X-MC-MAIL"];
                $lob = $headers["X-MC-LOB"];
            } else {
                if (!in_array($this->metodo, $this->arrMetodosSOAP) && !in_array($this->metodo, $this->arrMetodosPOST)) {
                    $this->response(array("error" => 1, "response" => "Acceso no permitido. (1)"));
                }
            }

            $unmask_correo=base64_encode("unmask_correo");
            if(isset($data["usuario"],$data["usuario"]["UserProfileID"],$data["usuario"][$unmask_correo])){
                $data["usuario"]["UserProfileIDMask"]=$data["usuario"]["UserProfileID"];
                $data["usuario"]["UserProfileID"]=base64_decode($data["usuario"][$unmask_correo]);
            }

            $unmask_linea=base64_encode("unmask_linea");
            if(isset($data["cuenta"],$data["cuenta"]["AccountId"],$data["cuenta"][$unmask_linea])){
                $data["cuenta"]["AccountIdMask"]=$data["cuenta"]["AccountId"];
                $data["cuenta"]["AccountId"]=base64_decode($data["cuenta"][$unmask_linea]);
            }

            if (json_last_error()==JSON_ERROR_NONE) {
                if (isset($data["inicio"], $data["valid"])) {
                    $public = (isset($data["zp"]) ? $data["zp"] == 1 : false) || $this->metodo == "LoginUsuarioApp" || in_array($this->metodo, $this->arrMetodosSOAP);
                    if ($public) {
                        $this->sessionUsuario = $data;
                    } else {
                        $tokenTime = strtotime($data["inicio"]);
                        $nowTime = strtotime(date('Y-m-d H:i:s'));
                        $minutDiff = round(abs($tokenTime - $nowTime) / 60, 2);
                        if ($minutDiff > $data["valid"] && false) {
                            $this->response(array("error" => 69, "response" => "Por favor inicia sesión para continuar. (69)"));
                        } else {
                            if (isset($data["cuenta"], $data["usuario"], $data["cuenta"]["LineOfBusiness"], $data["cuenta"]["AccountId"], $data["usuario"]["UserProfileID"])) {
                                /*
                                $otra = isset($data["otraLinea"]) && $data["otraLinea"] == "1";
                                if( ($linea == $data["cuenta"]["AccountId"] && $correo == $data["usuario"]["UserProfileID"] && $lob == $data["cuenta"]["LineOfBusiness"]) || $otra ){
                                    $this->sessionUsuario=$data;
                                }else{
                                    $this->response( array("error"=>1,"response"=>"Acceso no permitido. (Informacion diferente..)","data"=>$data));
                                }
                                */
                                $this->sessionUsuario = $data;
                            } else {
                                $this->response(array("error" => 1, "response" => "Acceso no permitido. (3)"));
                            }
                        }
                    }
                } else {
                    $this->response(array("error" => 1, "response" => "Debes iniciar sesión. (3)"));
                }
            } else {
                $this->response(array("error" => 1, "response" => "Error en la llave de aplicación (NONE)"));
            }
        } else {
            if (!in_array($this->metodo, $this->arrMetodosSOAP) && !in_array($this->metodo, $this->arrMetodosPOST) && !in_array($this->metodo, $this->arrMetodosGET)) {
                $this->response(array("error" => 1, "response" => "No es posible continuar. (4)"));
            }
        }

        $this->sessionUsuario = isset($this, $this->sessionUsuario) && $this->sessionUsuario != null ? $this->sessionUsuario : array("usuario" => $usuarioMap, "cuenta" => $cuentaMap);

    }

    function get_data($type,$var){
        $data = $this->$type($var);
        
        if( $data == null){
            //$sData = json_decode(json_decode(file_get_contents('php://input'),true));
            $sData = json_decode(file_get_contents('php://input'),true);
            //$aData = get_object_vars($sData);
           

            if(isset($sData,$sData["data"])){
                $data = $sData["data"];
            }
       }


        if($this->app["produccion"]){
          $data=$this->gibberishaes->dec($data,$this->app["AES"]); 
          
          if ($type=="post" || $type=="put") {
            $data=json_decode($data, true);
          }
        }
        
        return $data;
    }
    
    function return_data($data){
        
        if($this->app["produccion"]){
            if (is_array($data->response) || is_object($data->response) ) {
                $data->response=$this->gibberishaes->enc(json_encode($data->response),$this->app["AES"]);  
            }else{
                $data->response=$this->gibberishaes->enc($data->response,$this->app["AES"]); 
            }
        }
        
        //return $this->response($data);

        //return $resJSON;
        header('Content-Type: application/json');
        echo json_encode($data);
    }

    //Consumo de servicios REST POST 
    function curl($data){
        $myData = $data;
        
        /*if ($this->metodo == "ReportarRoboPerdidaImei"){
            return array("error"=>1,"response"=>"Por el momento esta funcionalidad no se encuentra disponible.");
        }*/

        $log = array("request"=>$data,"canal"=>"N/A","metodo"=>$this->metodo,"httpVerb"=>"POST","tipoServicio"=>"SOAP");
        
        $headers = array_change_key_case($this->input->request_headers(), CASE_UPPER);
        $headers["X-MC-MAIL"] = isset($headers["X-MC-MAIL"]) ? $headers["X-MC-MAIL"] : "";

        $dataValid = $this->validarData($headers, $data);
        if (!$dataValid["valid"] && !in_array($this->metodo, $this->arrMetodosSOAP)) {
            $res_data = array("error" => 1, "response" => "Acceso no permitido. (2)", "valid" => $dataValid);
            return $this->retornar($log, $res_data);
        }

        if(isset($headers["X-MC-SO"],$headers["X-MC-LINE"],$headers["X-MC-LOB"],$headers["X-MC-MAIL"])){
            $data["line"] = $headers["X-MC-LINE"];
            $data["lob"] = $headers["X-MC-LOB"];
            $data["SO"] = $headers["X-MC-SO"];
            $data["mail"] = $headers["X-MC-MAIL"];
        }else{
            if (!in_array($this->metodo, $this->arrMetodosSOAP)) {
                $res_data = array("error" => 1, "response" => "Acceso no permitido. (1)", "h" => $headers);
                return $this->retornar($log, $res_data);
            }
        }

        $data["AccountId"] = isset($data["AccountId"]) ? $data["AccountId"] : null;
        if($this->metodo == "getCitiConsumoV2" && is_null($data["AccountId"]) && isset($data["line"])){
            $data["AccountId"]=$data["line"];
        }

        if(strpos($this->metodo,"itel") !== false){
            $x = openssl_encrypt ($this->dataInfo["data"], $this->dataInfo["type"], $this->dataInfo["secret"]);
            $this->dataInfo["itel"] = $x;
        }

        /*VALIDACION DE TOKENS*/
        if (!in_array($this->metodo, $this->arrMetodosSOAP)) {
            if (isset($headers["X-SESSION-ID"])) {
                if (isset($this->sessionUsuario, $this->sessionUsuario["cuenta"], $this->sessionUsuario["cuenta"]["AccountId"])) {
                    $linea = isset($data["AccountId"]) ? $data["AccountId"] : (isset($data["numeroCuenta"]) ? $data["numeroCuenta"] : "");
                    $lineaH = isset($headers["X-MC-LINE"]) ? $headers["X-MC-LINE"] : "";
                    $mailH = isset($headers["X-MC-MAIL"]) ? $headers["X-MC-MAIL"] : "";
                    $otra = isset($data["otraLinea"]) && $data["otraLinea"] == "1";
                    $validH = $otra ? true : ($lineaH == $this->sessionUsuario["cuenta"]["AccountId"] && $mailH == $this->sessionUsuario["usuario"]["UserProfileID"]);
                    $AccountValid = $otra ? true : ($linea != "" ? $linea == $this->sessionUsuario["cuenta"]["AccountId"] : true);
                    if ($AccountValid && $validH) {
                        $data = $this->getTokenData($data);
                    } else {
                        $log["response"] = "ERROR DE SEGURIDAD (1)";
                        $log["url"] = "Petición Insegura";
                        $log["reqXML"] = "Petición Insegura";
                        $log["resXML"] = "Petición Insegura";
                        $log["linea"] = "Petición Insegura";
                        $log["segmento"] = "Petición Insegura";
                        $log["correo"] = "Petición Insegura";
                        $log["tiempo"] = "0";
                        $log["isError"] = "1";
                        $res_data = array("error" => 1, "response" => "Por el momento el acceso no está permitido");
                        return $this->retornar($log, $res_data);
                    }
                } else {
                    $log["response"] = "ERROR DE SEGURIDAD (2)";
                    $log["url"] = "Petición Insegura";
                    $log["reqXML"] = "Petición Insegura";
                    $log["resXML"] = "Petición Insegura";
                    $log["linea"] = "Petición Insegura";
                    $log["segmento"] = "Petición Insegura";
                    $log["correo"] = "Petición Insegura";
                    $log["tiempo"] = "0";
                    $log["isError"] = "1";
                    $res_data = array("error" => 1, "response" => "Por favor valida tu conexión..");
                    return $this->retornar($log, $res_data);
                }
            } else {
                $log["response"] = "ERROR DE SEGURIDAD (3)";
                $log["url"] = "Petición Insegura";
                $log["reqXML"] = "Petición Insegura";
                $log["resXML"] = "Petición Insegura";
                $log["linea"] = "Petición Insegura";
                $log["segmento"] = "Petición Insegura";
                $log["correo"] = "Petición Insegura";
                $log["tiempo"] = "0";
                $log["isError"] = "1";
                $res_data = array("error" => 1, "response" => "Por favor valida tu conexión a internet.");
                return $this->retornar($log, $res_data);
            }
        }

        /*if ($this->metodo == "cmSetWpaKey") {

            //$datagetCMDataAccount=array("ip"=>$data["ip"], "model"=>$data["model"], "key"=>$data["key"]);
            $token = $headers["X-SESSION-ID"];
            $header = $this->getHeadersData($headers);

            $objCurl = new \M3\Classes\Soap\RunSoap('http://' . $_SERVER["HTTP_HOST"] . '/M3/Hogar/cmSetWpaKey/', json_encode(array("data" => $data)));
            $objCurl->setIsSoap(false);
            $objCurl->setHeader($header);
            $resgetCMDataAccount = $objCurl->runSoap();
            if ((isset($resgetCMDataAccount['response']['response']))) {
                return $resgetCMDataAccount['response']['response'];
            } else {
                return (object)[
                    'response' => 'No fue posible actualizar tu contraseña en este momento.',
                    'error' => 1
                ];
            }
        }*/

        $starttime = microtime(true);
        //return array("error"=>1,"response"=>$data);

        $fbs_domain = "";
        $fbs_validate = false;
        if ($this->metodo == "LoginUsuarioApp" || $this->metodo == "LoginUsuarioCuenta") {
            $data = $this->getTokenData($data);
            if (isset($data["fbs"], $data["fbs"]["domain"], $data["fbs"]["validate"])) {
                $fbs_domain = $data["fbs"]["domain"];
                $fbs_validate = $data["fbs"]["validate"];
            }

            if ($fbs_validate) {
                $this->load->library('uuid');
                $device = $this->uuid->validate_session_app($headers, $data, $fbs_domain);
                if (isset($device, $device["error"], $device["response"])) {
                    if ($device["error"] != "0") {
                        return $this->retornar($log, $device);
                    }
                } else {
                    $res_data = array("error" => 1, "response" => "En este momento no es posible ingresar a tu cuenta. Por favor inténtalo de nuevo más tarde!", "data" => $device);
                    return $this->retornar($log, $res_data);
                }
            }
        }

        if ($this->metodo == "LoginUsuarioCuenta") {
            $header = $this->getHeadersData($headers);
            $optCurl = array(CURLOPT_HTTPHEADER => $header);
            $usuarioCanales = $this->curl->simple_post('http://' . $_SERVER["HTTP_HOST"] . '/M3/General/ConsultarUsuarioCanales/', json_encode(array()), $optCurl);
            $usuarioCanales = isset($usuarioCanales) ? json_decode($usuarioCanales, true) : array();
            if (isset($usuarioCanales, $usuarioCanales["error"], $usuarioCanales["response"]) && intval($usuarioCanales["error"] == 0)) {
                $data["nombreUsuario"] = $usuarioCanales["response"];
            }
        }

        if ($this->metodo == "LoginUsuarioWeb" || $this->metodo == "LoginUsuarioApp") {
            $this->metodoOld = $this->metodo;
            $this->metodo = "LoginUsuario";
        }

        if (!file_exists(APPPATH."views/Request/".$this->metodo.".php")){
            $log["response"] = $this->lang->line("error_archivo_request");
            $log["isError"] = 1;
            $res_data = array("error"=>1,"response"=>$this->lang->line("error_archivo_request"));
            return $this->retornar($log,$res_data);
            //return array("error"=>1,"response"=>$this->lang->line("error_archivo_request"));
        }

        if (!file_exists(APPPATH."views/Response/".$this->metodo.".php")){
            $log["response"] = $this->lang->line("error_archivo_response");
            $log["isError"] = 1;
            $res_data = array("error"=>1,"response"=>$this->lang->line("error_archivo_response"));
            return $this->retornar($log,$res_data);
            //return array("error"=>1,"response"=>$this->lang->line("error_archivo_response"));
        }

        $reqXML=$this->load->view("Request/".$this->metodo,$data,true);
        $log["reqXML"] = $reqXML;
        
        if (!in_array($this->metodo, $this->arrMetodosSOAP)) {
            if(!(isset($headers,$headers["X-MC-SO"]))){
                return array("error"=>1,"response"=>"Te invitamos a validar datos.");
            }
        }


        //Para validar el metodo repetido "RetrievePlan"
        //$isMetSet=false;
        switch ($this->metodo) {
            case "RegistrarImeiDuplicado":
                $nMetodo = "RegistrarImeiResponse";
                break;
            default:
                $nMetodo=$this->metodo;
                break;
        }
        //$this->metodo == "retrievePlanFija"?$nMetodo = "retrievePlan":$nMetodo=$this->metodo;
        //$this->metodo == "ConsultarProductosRepos"?$nMetodo = "ConsultarProductos":$nMetodo=$this->metodo;

        $tagResponseNS4='ns4'.$nMetodo.'Response';
        $tagResponseNS2='ns2'.$nMetodo.'Response';
        $tagResponseNS2Type='ns2'.$nMetodo.'ResponseType';
        $tagResponseNS1='ns1'.$nMetodo.'Response';
        $tagResponseNS1Solo='ns1'.$nMetodo;
        $tagResponseV1='v1'.$nMetodo;
        $tagResponseSmartLocation='urnget_position_response';
        $tagResponse=$nMetodo.'Response';
        $tagResponseTns='tns'.$nMetodo.'Response';
        $tagResponseCon='con'.$nMetodo.'Response';
        $tagResponseNS0='ns0'.$nMetodo.'Response';
        $tagResponseMetodo = $nMetodo;

        $header = array(
            "Content-type: text/xml;charset=\"utf-8\"",
            "Accept: text/xml",
            "Cache-Control: no-cache",
            "Pragma: no-cache",
            "Content-length: ".strlen($reqXML),
        );

        $urlServicio=$this->servicios[$this->metodo];

        if(!isset($urlServicio) || $urlServicio==""){
            $log["response"] = "No se encontró el EndPoint de este servicio.";
            $log["isError"] = 1;
            $res_data = array("error"=>1,"response"=>"No se encontró el EndPoint de este servicio.");
            return $this->retornar($log,$res_data);
            //return array("error"=>1,"response"=>"No se encontró el EndPoint de este servicio.");
        }
        $log["url"] = $urlServicio;


        $conTime = 20;
        $resTime = 30;

        $soap_do = curl_init();
        curl_setopt($soap_do, CURLOPT_URL,$urlServicio);
        curl_setopt($soap_do, CURLOPT_CONNECTTIMEOUT, $conTime);
        curl_setopt($soap_do, CURLOPT_TIMEOUT,        $resTime);
        curl_setopt($soap_do, CURLOPT_RETURNTRANSFER, true );
        curl_setopt($soap_do, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($soap_do, CURLOPT_SSL_VERIFYHOST, false);
        
        curl_setopt($soap_do, CURLOPT_POSTFIELDS,     $reqXML);
        curl_setopt($soap_do, CURLOPT_HTTPHEADER,     $header);
        $res = curl_exec($soap_do);
        $resHTML=$res;
        

        $now = new DateTime();
        $diff = microtime(true) - $starttime;
        $sec = intval($diff);
        $micro = $diff - $sec;
        $final = strftime('%T', mktime(0, 0, $sec)) . str_replace('0.', '.', sprintf('%.4f', $micro));
        $secs = $final;
        $log["tiempo"] = $secs;
        //$this->logErrorTimeOut($secs);
        
        if(!$res) {
            $res = 'Error x: ' . curl_error($soap_do);
            curl_close($soap_do);
            
            $log["response"] = $res;
            if ($this->metodo == "NotificarCompraPaqueteComplementario") {
                $log["isError"] = 0;
                $res_data = array("error" => 0, "response" => "");
                return $this->retornar($log, $res_data);
            }
            $log["isError"] = 1;

            $res_data = array("error"=>1,"response"=> "En este momento no podemos atender esta solicitud, intenta nuevamente (1).","url"=>$urlServicio);
            return $this->retornar($log,$res_data);
        } else {
            curl_close($soap_do);
            $res = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $res);

            $res = str_replace(" xmlns=\"http://services.cmPoller.sisges.telmex.com.co\"", "", $res);
            $res = str_replace(" xmlns=\"https://services.cmPoller.sisges.telmex.com.co\"", "", $res);
            $res = str_replace(" xmlns=\"Claro.SelfCareManagement.Services.Entities.Contracts\"", "", $res);
            $res = str_replace(" xmlns=\"Claro.SelfCareManagement.Services.Exception.Contracts\"", "", $res);
            
            $log["resXML"] = $res;

            try {
                libxml_use_internal_errors(true);
                $xml = new SimpleXMLElement($res);
            } catch (Exception $e) {
                
                $log["response"] = "En este momento no podemos atender esta solicitud, intenta nuevamente (2).".$e->getMessage().", ".$res." URL:".$urlServicio."- IP:".$_SERVER["SERVER_ADDR"];
                $log["isError"] = 1;
                $res_data = array("error"=>1,"response"=> "En este momento no podemos atender esta solicitud, intenta nuevamente (3).");
                return $this->retornar($log,$res_data);
                //return array("error"=>1,"response"=> "Error interno del servidor (BUS).".$e->getMessage().", ".$res." URL:".$urlServicio."- IP:".$_SERVER["SERVER_ADDR"] );
            }   

            if(isset($xml->soapenvBody)){
                $body = $xml->soapenvBody;
            }else if(isset($xml->sBody)){
                $body = $xml->sBody;
            }else if(isset($xml->SBody)){
                $body = $xml->SBody;
            }else if(isset($xml->Body)){
                $body = $xml->Body;
            }else if(isset($xml->soapBody)){
                $body = $xml->soapBody;
            }else if(isset($xml->envBody)){
                $body = $xml->envBody;
            }           
            
    

            if(isset($body)){

                if(array() === $body){
                    $body = $body[0];
                }
                
                if(isset($body->$tagResponseNS2)){
                    $body = $body->$tagResponseNS2;
                }else if(isset($body->$tagResponseNS2Type)){
                    $body = $body->$tagResponseNS2Type;
                }else if(isset($body->$tagResponseNS1)){
                    $body = $body->$tagResponseNS1;
                }else if(isset($body->$tagResponseNS1Solo)){
                    $body = $body->$tagResponseNS1Solo;
                }else if(isset($body->$tagResponseV1)){
                    $body = $body->$tagResponseV1;
                }else if(isset($body->$tagResponseNS4)){
                    $body = $body->$tagResponseNS4;
                }else if(isset($body->$tagResponse)){
                    $body = $body->$tagResponse;
                }else if(isset($body->$tagResponseSmartLocation)){
                    $body = $body->$tagResponseSmartLocation;
                }else if(isset($body->$tagResponseTns)){
                    $body = $body->$tagResponseTns;
                }else if(isset($body->$tagResponseCon)){
                    $body = $body->$tagResponseCon;
                }else if(isset($body->$tagResponseNS0)){
                    $body = $body->$tagResponseNS0;
                }else if(isset($body->$tagResponseMetodo)){
                    $body = $body->$tagResponseMetodo;
                }else if(isset($body->ejecWS_Result)){
                    $body = $body->ejecWS_Result;
                } else if (isset($body->ns2get_chosenResponseType)) {
                    $body = $body->ns2get_chosenResponseType;
                } else if (isset($body->ns2del_chosenResponseType)) {
                    $body = $body->ns2del_chosenResponseType;
                } else if (isset($body->ns2set_chosenResponseType)) {
                    $body = $body->ns2set_chosenResponseType;
                }else if(isset($body->RecuperarContraseñaUsuarioResponse)){
                    $body = $body->RecuperarContraseñaUsuarioResponse;
                }else if(isset($body->CambiarContraseñaUsuarioResponse)){
                    $body = $body->CambiarContraseñaUsuarioResponse;
                    $cambioClave=1;
                }else if($this->metodo == "RegistrarImeiDuplicado"){
                    $body = $body->ns2RegistrarImeiResponse;
                }else if(isset($body->xdrMapColombiaResponse)){
                    $body=$body->xdrMapColombiaResponse;
                }else{
                    $error=1;
                }


                if (isset($body) && !isset($error)) {

                    $response = json_decode(json_encode((array)$body), TRUE); 

                    if ((json_last_error() == JSON_ERROR_NONE)) {

                        if (isset($cambioClave)) {
                            $dataRes["claveActualizada"]=$response["esContraseñaActualizada"];
                            $response=$dataRes;
                        }

                        $response["req"]=$data;
                        $response['controller'] = $this;
                        $response['h'] = $headers;
                        $response['s'] = isset($this->sessionUsuario) ? $this->sessionUsuario : '';
                        
                        $resJSON=json_decode($this->load->view("Response/".$this->metodo,$response,true));
                        if ($resJSON) {
                            //$resJSON->secs=$secs;
                            //$resJSON->server = isset($_SERVER["SERVER_ADDR"])?$_SERVER["SERVER_ADDR"]:'';
                        }

                        //$resJSON->dataRes = $body;
                        //$resJSON->reqXML = $reqXML;

                        $log["response"] = $resJSON;
                        $log["isError"] = 0;
                        $res_data = $resJSON;

                        return $this->retornar($log,$res_data);
                        //return $resJSON;
                    }
                }
  
            }

            if(isset($xml->SBody->ns0Fault)){
                $tagFaultNS1='ns1'.$nMetodo.'Fault';
                $tagFaultNS1NA='ns1NA';

                if(isset($xml->SBody->ns0Fault->detail->$tagFaultNS1->Message) || isset($xml->SBody->ns0Fault->detail->$tagFaultNS1NA->Message) || isset($xml->SBody->ns0Fault->detail->$tagFaultNS1->errorMessage)){

                    $temp=isset($xml->SBody->ns0Fault->detail->$tagFaultNS1->Message)?json_encode($xml->SBody->ns0Fault->detail->$tagFaultNS1):json_encode($xml->SBody->ns0Fault->detail->$tagFaultNS1NA);
                    $temp=json_decode($temp, true);

                    if (isset($temp["Message"]) && $temp["Message"] == "An error ocurred during the transaction") {
                        $temp["Message"] = "Discúlpanos, se ha presentado un error.";
                    }

                    $log["response"] = $temp["Message"];
                    $log["isError"] = 1;
                    if(!isset($temp["Message"])){
                        $temp["Message"]=(string)$xml->SBody->ns0Fault->detail->$tagFaultNS1->errorMessage;
                    }
                    $res_data = array("error"=>1,"response"=> $temp["Message"],"secs"=> $secs, "server"=>"Exception1");
                } else if(isset($xml->SBody->ns0Fault->detail->ns0faultMessage)){

                    $temp=json_encode($xml->SBody->ns0Fault->detail->ns0faultMessage);
                    $temp=json_decode($temp, true);
                    $log["response"] = $temp["description"];
                    $log["isError"] = 1;
                    $res_data = array("error"=>1,"response"=> $temp["description"],"secs"=> $secs, "server"=>"Exception2","req"=>$reqXML,"resXML"=>$xml);
                }else{

                    $temp=json_encode($xml->SBody->ns0Fault);
                    $log["response"] = json_encode($xml->SBody->ns0Fault);
                    $log["isError"] = 1;
                    $res_data = array("error"=>1,"response"=> json_encode($xml->SBody->ns0Fault),"secs"=> $secs, "server"=>"Exception3");
                }

            }else if(isset($xml->sBody->sFault->detail->InnerFault->amessage)){
                $temp=json_encode($xml->sBody->sFault->detail->InnerFault);
                $temp=json_decode($temp, true);
                if($temp["amessage"] == "La cuenta ingresada no tiene un producto de internet inalambrico asociado"){
                    $temp["amessage"] = "Debe ingresar un número válido.";
                }
                $log["response"] = $temp["amessage"];
                $log["isError"] = 1;
                $res_data = array("error"=>1,"response"=> $temp["amessage"],"secs"=> $secs, "server"=>"Exception4","request"=>$myData,"nodo"=>isset($_SERVER["SERVER_ADDR"])?$_SERVER["SERVER_ADDR"]:'');
                
            }else if(isset($xml->sBody->sFault)){
                $temp=json_encode($xml->sBody->sFault);
                $temp=json_decode($temp, true);
                
                $log["response"] = $temp["faultstring"];
                $log["isError"] = 1;
                $res_data = array("error"=>1,"response"=> $temp["faultstring"],"secs"=> $secs, "server"=>"Exception5");

            }else{
                
                $log["response"] = "Error al consumir el SOAP";
                $log["isError"] = 1;
                $res_data = array("error"=>1,"response"=> "En este momento no podemos atender esta solicitud, intenta nuevamente (4).","secs"=> $secs ,"server"=> $xml,"ns2"=>$this->metodo);
            }

            if (((isset($this->metodoOld) && $this->metodoOld == "LoginUsuarioApp") || $this->metodo == "LoginUsuarioCuenta") && $fbs_validate) {
                //if($fbs_validate){
                $data = $this->getTokenData($data);
                $this->load->library('uuid');
                $this->uuid->lock_device_app($headers, $data, $fbs_domain);
            }

            return $this->retornar($log,$res_data);
        }
    }
    
    function rest_post($data,$canal){

        $log = array("request" => $data, "canal" => $canal, "metodo" => $this->metodo, "httpVerb" => "POST", "tipoServicio" => "REST");
        $starttime = microtime(true);
        $headers = array_change_key_case($this->input->request_headers(), CASE_UPPER);
        /*VALIDACION DE TOKENS*/
        if(!in_array($this->metodo,$this->arrMetodosPOST)){
            if (isset($headers["X-SESSION-ID"])) {
                if (isset($this->sessionUsuario)) {
                    $data = $this->getTokenData($data);
                } else {
                    $log["response"] = "ERROR DE SEGURIDAD";
                    $log["url"] = "Petición Insegura";
                    $log["reqXML"] = "Petición Insegura";
                    $log["resXML"] = "Petición Insegura";
                    $log["linea"] = "Petición Insegura";
                    $log["segmento"] = "Petición Insegura";
                    $log["correo"] = "Petición Insegura";
                    $log["tiempo"] = "0";
                    $log["isError"] = "1";
                    $res_data = array("error" => 1, "response" => "Por favor valida tu conexión..");
                    return $this->retornar($log, $res_data);
                }
            } else {
                $log["response"] = "ERROR DE SEGURIDAD";
                $log["url"] = "Petición Insegura";
                $log["reqXML"] = "Petición Insegura";
                $log["resXML"] = "Petición Insegura";
                $log["linea"] = "Petición Insegura";
                $log["segmento"] = "Petición Insegura";
                $log["correo"] = "Petición Insegura";
                $log["tiempo"] = "0";
                $log["isError"] = "1";
                $res_data = array("error" => 1, "response" => "Por favor valida tu conexión a internet.");
                return $this->retornar($log, $res_data);
            }
        }

        $urlServicio=$this->servicios[$this->metodo];
        if($canal=="hogar"){
            $reqJSON=$this->load->view("Request/paradigma",$data,true);
        }else if($canal=="xdr" || $canal=="xdr_prepago" || $canal=="citi"){
            $reqJSON=$this->load->view("Request/".$this->metodo,$data,true);
        }
        $log["url"] = $urlServicio;
        $log["reqXML"] = $reqJSON;
            
        //return array("error"=>1,"response"=>$reqJSON,"metodo"=>$this->metodo,"data"=>$data);

        
        $header = array(
            "Content-type: application/json;charset=\"utf-8\"",
            "Accept: application/json",
            "Cache-Control: no-cache",
            "Pragma: no-cache",
            "SOAPAction: \"run\"",
            "Content-length: ".strlen($reqJSON),
        );

        if($canal=="xdr"){
            $header[]="User:xdrws";
            $header[]="Password:xdrws1*";
        }else if($canal=="xdr_prepago"){
            $header[]="User:xdrws";
            //$header[]="Password:xdrws1*";
            $header[]="Password:ClaroXdr1";
        }


        $soap_do = curl_init();
        curl_setopt($soap_do, CURLOPT_URL, $urlServicio);
        curl_setopt($soap_do, CURLOPT_CONNECTTIMEOUT, 20);
        curl_setopt($soap_do, CURLOPT_TIMEOUT,        30);
        curl_setopt($soap_do, CURLOPT_RETURNTRANSFER, true );
        curl_setopt($soap_do, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($soap_do, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($soap_do, CURLOPT_POST,           true );
        curl_setopt($soap_do, CURLOPT_POSTFIELDS,     $reqJSON);
        curl_setopt($soap_do, CURLOPT_HTTPHEADER,     $header);
        $res = curl_exec($soap_do);
        $resHTML=$res;
        $log["resXML"] = $res;

        $now = new DateTime();
        $diff = microtime(true) - $starttime;
        $sec = intval($diff);
        $micro = $diff - $sec;
        $final = strftime('%T', mktime(0, 0, $sec)) . str_replace('0.', '.', sprintf('%.4f', $micro));
        $secs = $final;
        $log["tiempo"] = $secs;
        //$this->logErrorTimeOut($secs);

        //return array("error"=>1,"response"=>$res,"secs"=>$secs);

        if(!$res || $res==null) {
            $res = 'Error: ' . curl_error($soap_do);
            curl_close($soap_do);
            $log["response"] = $res;
            $log["isError"] = 1;
            //$res_data = array("error"=>1,"response"=>$res);

            $res_data = array("error"=>1,"response"=> "En este momento no podemos atender esta solicitud, intenta nuevamente (5).");
            return $this->retornar($log,$res_data);
            //return array("error"=>1,"response"=>$res,"secs"=>$secs);

        } else {
            curl_close($soap_do);
            $res=json_decode($res, true);
                //return array("error"=>0,"response"=>$res);

            if ((json_last_error() == JSON_ERROR_NONE)) {

                if($canal=="xdr" || $canal=="xdr_prepago" || $canal =="citi"){

                    $res["req"]=$data;
                    $res['controller'] = $this;
                    $res['h'] = $headers;
                    $res['s'] = $this->sessionUsuario;   

                    $resJSON=json_decode($this->load->view("Response/".$this->metodo,$res,true));
                    if ($resJSON) {
                        $resJSON->secs=$secs;
                    }
                    
                    $log["response"] = $resJSON;
                    $log["isError"] = 0;
                    $res_data = $resJSON;
                    return $this->retornar($log,$res_data);
                    //return $resJSON;
                }else{

                    if (isset($res["d"])) {

                        //$resOld = iconv('UTF-8', 'UTF-8//IGNORE', utf8_encode($res["d"]));
                        //$resOldSin = var_dump($res["d"]);
                        $resOldSin = "asd";
                        $resOld = utf8_encode($res["d"]);
                        $res=json_decode($resOld, true);
                        
                        $err = json_last_error(); 
                        if ((json_last_error() == JSON_ERROR_NONE)) {

                            $objData = "sdsdsd";
                            $resMsj = $res;
                            $isError = 0;
                            if(isset($res,$res["error"],$res["error"]["isError"])){
                                if($res["error"]["isError"]){
                                    $isError = 1;

                                    if(isset($res["error"]["msg"])){
                                        $resMsj = $res["error"]["msg"];
                                    }
                                }
                            }

                            $log["response"] = $resMsj;
                            $log["isError"] = $isError;

                            $res_data = array("error"=>$isError,"response"=>$resMsj,"secs"=>$secs,"data"=>$objData);
                            return $this->retornar($log,$res_data);
                            //return array("error"=>0,"response"=>$res,"secs"=>$secs);
                        }else{
                            $log["response"] = "error servicio d";//$res["d"];
                            $log["isError"] = 1;
                            $res_data = array("error"=>1,"response"=>"En este momento no podemos atender esta solicitud, intenta nuevamente (6).","secs"=>$secs,"data"=>$err,"dataer"=>$resOld,"dataOrig"=>$resOldSin);
                            return $this->retornar($log,$res_data);
                        }

                    }else{
                        $log["response"] = "error servicio d";
                        $log["isError"] = 1;
                        $res_data = array("error"=>1,"response"=>"En este momento no podemos atender esta solicitud, intenta nuevamente (7).","secs"=>$secs,"data"=>json_last_error());
                        return $this->retornar($log,$res_data);
                    }
                }
            }else{
                $log["response"] = $resHTML;
                $log["isError"] = 1;
                $res_data = array("error"=>1,"response"=>"En este momento no podemos atender esta solicitud, intenta nuevamente (8).","secs"=>$secs,"data"=>json_last_error());
                return $this->retornar($log,$res_data);
                //return array("error"=>1,"response"=>"El servicio no se encuentra disponible en este momento. (500)".$resHTML,"secs"=>$secs);
            }

        }
    }
    
    function rest_get($data,$canal){

        $log = array("request"=>$data,"canal"=>$canal,"metodo"=>$this->metodo,"httpVerb"=>"GET","tipoServicio"=>"REST");
        $starttime = microtime(true);

        $headers = array_change_key_case($this->input->request_headers(), CASE_UPPER);

        /*VALIDACION DE TOKENS*/
        if(!in_array($this->metodo,$this->arrMetodosGET)){
            if(isset($headers["X-SESSION-ID"])){
                if(isset($this->sessionUsuario)){
                    $data = $this->getTokenData($data);
                }else{
                    $log["response"] = "ERROR DE SEGURIDAD";
                    $log["url"] = "Petición Insegura";
                    $log["reqXML"] = "Petición Insegura";
                    $log["resXML"] = "Petición Insegura";
                    $log["linea"] = "Petición Insegura";
                    $log["segmento"] = "Petición Insegura";
                    $log["correo"] = "Petición Insegura";
                    $log["tiempo"] = "0";
                    $log["isError"] = "1";
                    $res_data = array("error"=>1,"response"=>"Por favor valida tu conexión..");
                    return $this->retornar($log,$res_data);    
                }
            }else{
                $log["response"] = "ERROR DE SEGURIDAD";
                $log["url"] = "Petición Insegura";
                $log["reqXML"] = "Petición Insegura";
                $log["resXML"] = "Petición Insegura";
                $log["linea"] = "Petición Insegura";
                $log["segmento"] = "Petición Insegura";
                $log["correo"] = "Petición Insegura";
                $log["tiempo"] = "0";
                $log["isError"] = "1";
                $res_data = array("error"=>1,"response"=>"Por favor valida tu conexión a internet.");
                return $this->retornar($log,$res_data);
            }
        }

        if($this->metodo=="getCitiConsumo"){
            /*
            $this->load->library('curl');

            $data_send=array("AccountId"=>$data["AccountId"]);

            $res_validate=$this->curl->simple_post('https://miclaroapp.com.co/api/index.php/v1/soap/'.$this->metodo.'.json', array("data"=>$data_send));
            $res_validate=json_decode(((isset($res_validate))?$res_validate:array()));

            return $res_validate;*/

            return array("error"=>1,"response"=>"En este momento el módulo no se encuentra disponiblee");
        }
        
        

        $urlServicio=$this->servicios[$this->metodo];
        if($canal=="citi"){
            $urlServicio = str_replace("{AccountId}", $data["AccountId"], $urlServicio);
            $urlServicio = str_replace("{platform}", $data["platform"], $urlServicio);
            $urlServicio = str_replace("{tipo}", $data["tipo"], $urlServicio);
        }

        $header = array(
            "Content-type: application/json;charset=\"utf-8\"",
            "Accept: application/json",
            "Cache-Control: no-cache",
            "Pragma: no-cache",
            "SOAPAction: \"run\""
        );

        if($canal=="citi"){
            $header[] = "Authenticate: TWlDTEFSTzpNaUNMQVJP";
        }else if($canal=="gps"){
            
            $header = array(
                "Content-type: application/json;charset=\"utf-8\"",
                "Accept: application/json",
                "Cache-Control: no-cache",
                "Pragma: no-cache",
                "Authorization: Basic ZTA5OTM5ZGYyYmE5ZGM1ZDMzYTFiNTFmMTk1MDI2YTk3QGFteC1yZXMtY286ZGJhNGZhYTMzMDc5YzBjYzgwMWQ2ZjdhM2RhMDdkODVjZTcyYzdlYjY3NGJkZTU0ZmYzNzc0ZmE1NTc4YmM3OQ=="
            );
            
            $tipoDefault = "L";
            $data["observaciones"] = isset($data["observaciones"])?urlencode($data["observaciones"]):"";

            $urlServicio = isset($data["AccountId"])?str_replace("{AccountId}", $data["AccountId"], $urlServicio):$urlServicio;
            $urlServicio = isset($data["idAgenda"])?str_replace("{idAgenda}", $data["idAgenda"], $urlServicio):$urlServicio;
            $urlServicio = isset($data["orden"])?str_replace("{orden}", $data["orden"], $urlServicio):$urlServicio;
            $urlServicio = isset($data["fechaIni"])?str_replace("{fechaIni}", $data["fechaIni"], $urlServicio):$urlServicio;
            $urlServicio = isset($data["fechaFin"])?str_replace("{fechaFin}", $data["fechaFin"], $urlServicio):$urlServicio;
            $urlServicio = isset($data["activityId"])?str_replace("{activityId}", $data["activityId"], $urlServicio):$urlServicio;
            $urlServicio = isset($data["resourceId"])?str_replace("{resourceId}", $data["resourceId"], $urlServicio):$urlServicio;
            $urlServicio = isset($data["IRAZONID"])?str_replace("{idMotivo}", $data["IRAZONID"], $urlServicio):$urlServicio;
            $urlServicio = isset($data["tipo"])?str_replace("{tipo}", $data["tipo"], $urlServicio):str_replace("{tipo}",$tipoDefault, $urlServicio);
            $urlServicio = isset($data["correo"])?str_replace("{correo}", $data["correo"], $urlServicio):$urlServicio;
            $urlServicio = isset($data["observaciones"])?str_replace("{observaciones}", $data["observaciones"], $urlServicio):$urlServicio;
        }
        $log["url"] = $urlServicio;

        
        //return array("error"=>1,"url"=>$urlServicio);


        
        $soap_do = curl_init();
        curl_setopt($soap_do, CURLOPT_URL, $urlServicio);
        curl_setopt($soap_do, CURLOPT_CONNECTTIMEOUT, 20);
        curl_setopt($soap_do, CURLOPT_TIMEOUT,        30);
        curl_setopt($soap_do, CURLOPT_RETURNTRANSFER, true );
        curl_setopt($soap_do, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($soap_do, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($soap_do, CURLOPT_HTTPHEADER,     $header);
        $res = curl_exec($soap_do);
        $resHTML=$res;
        $log["resXML"] = $res;

        //return array("error"=>1,"response"=>$res);


        $now = new DateTime();
        $diff = microtime(true) - $starttime;
        $sec = intval($diff);
        $micro = $diff - $sec;
        $final = strftime('%T', mktime(0, 0, $sec)) . str_replace('0.', '.', sprintf('%.4f', $micro));
        $secs = $final;

        $log["tiempo"] = $secs;
        //$this->logErrorTimeOut($secs);

        if(!$res) {
            $res = 'Error: ' . curl_error($soap_do);
            curl_close($soap_do);
            
            
            $log["response"] = $res."(".$urlServicio.")";
            $log["isError"] = 1;
            //$res_data = array("error"=>1,"response"=>$res);

            $res_data = array("error"=>1,"response"=> "En este momento no podemos atender esta solicitud, intenta nuevamente (9).");
            return $this->retornar($log,$res_data);
            //***return array("error"=>1,"response"=>$res."(".$urlServicio.")","secs"=>$secs);

        } else {
            curl_close($soap_do);
            $res=json_decode($res, true);

            if ((json_last_error() == JSON_ERROR_NONE)) {
                //--return array("error"=>0,"response"=>$res);

                $res['controller'] = $this;
                $res['h'] = $headers;
                $res['s'] = $this->sessionUsuario;
                $resJSON=json_decode($this->load->view("Response/rest/".$this->metodo,$res,true));
                if ($resJSON) {
                    $resJSON->secs=$secs;
                }

                if($this->metodo == "getDetalleTecnico" || $this->metodo == "getDetalleVisita" || $this->metodo == "getActivities"){
                    $resJSON->requestLegado = $urlServicio;
                }

                $log["response"] =$resJSON;
                $log["isError"] = 0;
                $res_data = $resJSON;
                return $this->retornar($log,$res_data);
                //return $resJSON;
            }else{
                $log["response"] =json_last_error();
                $log["isError"] = 1;
                $res_data = array("error"=>1,"response"=>"En este momento no podemos atender esta solicitud, intenta nuevamente (10).","secs"=>$secs);
                return $this->retornar($log,$res_data);
                //return array("error"=>1,"response"=>"El servicio no se encuentra disponible en este momento.","secs"=>$secs);
            }
        }
    }

    function fnEncrypt($string, $key)
    {
        $encrypt_method = 'AES-128-ECB';
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($encrypt_method));
        $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
        //$output = base64_encode($output);

        return $output;
    }
    
    public function getParadigmaDate($ff,$lob){

        if($lob == "3"){
            list($dd,$mm,$yy) = explode('-', $ff);
        }

        if($lob == 1){
            list($mm,$ddyy) = explode(' ', $ff);
            list($dd,$yy) = explode('/', $ddyy);
            $yy = "20".$yy;
        }
            
        $mes = "00";
        switch ($mm) {
            case 'Ene':
                $mes = "01";
                break;
            case 'Feb':
                $mes = "02";
                break;
            case 'Mar':
                $mes = "03";
                break;
            case 'Abr':
                $mes = "04";
                break;
            case 'May':
                $mes = "05";
                break;
            case 'Jun':
                $mes = "06";
                break;
            case 'Jul':
                $mes = "07";
                break;
            case 'Ago':
                $mes = "08";
                break;
            case 'Sep':
                $mes = "09";
                break;
            case 'Oct':
                $mes = "10";
                break;
            case 'Nov':
                $mes = "11";
                break;
            case 'Dic':
                $mes = "12";
                break;
        }
            
        //list($dd, $yy) = explode('/', $ddyy);
        //return $dd."-".$mes."-20".$yy;
        return $yy."-".$mes."-".$dd;
        
    }

    function esArray($val){
        $temp=json_encode($val, true);
        if(json_last_error()==JSON_ERROR_NONE){
            $temp=json_decode($temp);
        
            if( is_array($temp)){
                return true;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    function arrayToString($val){

        if (isset($val)){
            $temp=json_encode($val, true);
            
            if(json_last_error()==JSON_ERROR_NONE){
                $temp=json_decode($temp);
            
                if( is_array($temp)){
                    return "";
                }else{
                    return trim($val);
                }
            }else{
                return trim($val);
            }
        }else{
            return "";
        }
    }
    
    function getArray($val){

        $list=array();
        $temp=json_encode($val, true);
        $temp=json_decode($temp);

        if( is_array($temp)){
            return $temp;
        }else{
            array_push($list,$temp);
            return $list;
        }
    }
    
    function parseToInt($val){
        return intval($val);
    }

    function logErrorTimeOut($secs){

        $tiempo=$this->timeToSeconds($secs);
        if ($tiempo>14) {//se guarda el log
            ini_set('date.timezone', 'America/Bogota');

            $path = $_SERVER['DOCUMENT_ROOT'].'archivos/historial';
            $fileName = "log_timeout_".date('m-d-Y_hisa').'.json';

            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }

            if (file_exists($path)) {
                $output_file=$path.'/'.$fileName;
                $ifp = fopen( $output_file, 'wb' ); 

                $headers = array_change_key_case($this->input->request_headers(), CASE_UPPER);

                //date_default_timezone_set('America/Bogota');
                $log["fecha"] = date('Y-m-d h:i:s a', time());
                $log["legado"] = $this->servicios[$this->metodo];
                $log["metodo"] = $this->metodo;
                $log["tiempo"] = $tiempo;
                $log["so"] = (isset($headers["X-MC-SO"])?$headers["X-MC-SO"]:"");
                $log["api"] = (isset($headers["X-MC-SO-API"])?$headers["X-MC-SO-API"]:"");
                $log["version"] = (isset($headers["X-MC-APP-V"])?$headers["X-MC-APP-V"]:"");

                $this->load->library('user_agent');
                if ( strpos( $this->agent->agent_string(), "okhttp" ) !== false  ){
                        $agent = "Android";

                }elseif ( strpos( $this->agent->agent_string(), "iPhone" ) !== false ){
                        $agent = "iPhone";

                }else{
                        $agent = 'Web';//por que okhttp reemplaza el user_agent
                }

                $log["origen"] = $agent;

                $log["timeout"] = ( intval($tiempo)==20 ? "timeout conexión" : ( (intval($tiempo)==30) ? "timeout operación" : "tiempo espera" ) );

                fwrite( $ifp,json_encode($log));
                fclose( $ifp );
            }
        }
    }

    function timeToSeconds($time)
    {
         $timeExploded = explode(':', $time);
         if (isset($timeExploded[2])) {
             return $timeExploded[0] * 3600 + $timeExploded[1] * 60 + $timeExploded[2];
         }
         return $timeExploded[0] * 3600 + $timeExploded[1] * 60;
    }
    
    public function getHeadersData($h){
        $headerToken[]=[];
        array_push($headerToken,"X-SESSION-ID: ".$h["X-SESSION-ID"]);
        array_push($headerToken,"X-MC-SO: ".$h["X-MC-SO"]);
        array_push($headerToken,"X-MC-LINE: ".$h["X-MC-LINE"]);
        array_push($headerToken,"X-MC-MAIL: ".$h["X-MC-MAIL"]);
        array_push($headerToken,"X-MC-LOB: ".$h["X-MC-LOB"]);
        array_push($headerToken,"X-MC-DEVICE-ID: ".$h["X-MC-DEVICE-ID"]);
        array_push($headerToken,"X-MC-USER-AGENT: ".$h["X-MC-USER-AGENT"]);

        return $headerToken;
    }

}

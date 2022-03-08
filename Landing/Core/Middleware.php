<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

require_once __DIR__.'/utils/Security.php';
require_once __DIR__.'/utils/Logs.php';
require_once __DIR__.'/utils/Validations.php';
require_once __DIR__.'/utils/Language.php';

//require_once __DIR__.'/config.php';

class MiddlewareApp
{
    private $container;
    private $file;

    public function __construct($_file,$_container) {
        $this->container = $_container;
        $this->file = $_file;
    }

    public function __invoke($request, $response, $next){

        //Lamado a clase Language
        $lng = new AppLanguage();

        //CreateLog
        $path = explode('/',$this->file);
        $metodo = $path[sizeof($path)-1];
        $metodo = isset($this->container["logName"])?$this->container["logName"]:$metodo;
        $tipoServicio = isset($this->container["tipoServicio"])?$this->container["tipoServicio"]:"No";
        $log = array("request" => "TKN", "canal" => "N_A", "metodo" => $metodo, "tipoServicio" => $tipoServicio);

        //Default Response
        $respuesta = array();
        $respuesta["error"]="1";
        $respuesta["response"]=textContent($lng->getText("serviceDefault"));

        //Security
        $headers = $request->getHeaders();
        $dataJson = json_decode($request->getBody());
        $skip = isset($this->container["skip"])?$this->container["skip"]:false;
        $dataToken=validarSeguridad($headers,$skip,$dataJson);

        if(!isset($dataToken->error)){

            $log["request"] = $dataJson;

            //validateData(Service)
            $validData = isset($dataJson);
            $dataService = isset($this->container["dataService"])?$this->container["dataService"]:array();
            if($validData && sizeof($dataService) > 0){
                $validData = isset($dataJson->data);
                $dataServicio = $validData?$dataJson->data:new stdClass();
                foreach($dataService as $k => $v){
                    $dataOk = isset($dataServicio->$v) &&  property_exists($dataServicio,$v);
                    if(!$dataOk){
                        $validData = false;
                        break;
                    }
                }
            }

            //validateToken(service)
            //Se valida el token con la información que se pueda usar en el servicio, no valida seguridad
            $validToken = isset(
                $dataToken,
                $dataToken->cuenta,
                $dataToken->cuenta->AccountId,
                $dataToken->cuenta->LineOfBusiness,
                $dataToken->usuario,
                $dataToken->usuario->UserProfileID,
                $dataToken->usuario->DocumentNumber,
                $dataToken->usuario->DocumentType
            );

            if($validData && $validToken){
                $request = $request
                ->withAttribute('dataToken', $dataToken)
                ->withAttribute('dataJson', $dataJson->data)
                ->withAttribute('log', $log)
                ->withAttribute('respuesta', $respuesta)
                ->withAttribute('headers', $headers);

                //LLamada al servicio
                $respuesta = $next($request, $response);
                $respuesta = json_decode($respuesta->getBody(), true);
                //$log=$respuesta["log"];

            }else{
                $respuesta["response"]=textContent($lng->getText("serviceErrorData"));
                $respuesta["validData"] = $validData;
                $respuesta["validToken"] = $validToken;
            }
            
            
            $log["response"] = $respuesta["response"];
            $log["isError"] = $respuesta["error"]; 
        }else{
            $log["response"] = $dataToken->response;
            $log["isError"] =  $dataToken->error;
            
            $respuesta["response"]=textContent($lng->getText("serviceErrorSecurity"))." (".$dataToken->internCode.")";
            $respuesta["error"] = $dataToken->error;
        }
        
        $respuesta["headers"] = $headers;
        $respuesta["log"] = $log;

        //TODO: En CleanResponse hacer la validacion con ldap
        //$respuesta = cleanResponse($respuesta,$dataJson,$headers);
        
        //Guardado de Logs
        save_to_file($log, $headers);
        save_in_db($log, $headers);

        return $response->withJson($respuesta)->withHeader('Content-type', 'application/json');
         
    }
}
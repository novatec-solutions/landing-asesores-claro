<?php
require __DIR__ . '/../../Core/vendor/autoload.php';
require_once __DIR__ . '/../../Core/utils/curlClass.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

// Se crea la app
$app = new \Slim\App();
$container = $app->getContainer();
$container['view'] = new \Slim\Views\PhpRenderer(__DIR__.'/template/');

$container['curlClass'] = new curlClass();

//Url del servicio
//$container['urlServicio']="http://172.24.160.135:8080/EXP_RSCustomerDataOtt/PS_RSCustomerDataOttV1.0";    //QA
$container['urlServicio']="http://172.24.160.135:8080/EXP_RSCustomerDataOtt/PS_RSCustomerDataOttV1.0";    //Producción

//Nombre del template Request
$container['requestTemplate']="request.php"; 

$app->map(['POST'], '/', function (Request $request, Response $response, array $args) {
    
    //$headers = $request->getHeaders();
    $json = json_decode( $request->getBody() );
    $data=$json->data;
    
    $reqXML = $this->view->fetch($this->requestTemplate, ['data' => $data]);

    $this->curlClass->URL=$this->urlServicio;
    $this->curlClass->POSTFIELDS=$reqXML;
    
    $header[]="";
    
    $dataRes=$this->curlClass->soap($header,true,true);
    
    /************************LOGICA DE RESPONSE***************************/
    
    $respuesta = array();
    if($dataRes["error"] == 0){
        
        $tagResp = "Root-Element";
        
        $respuesta["secs"] = $dataRes["secs"];
        $respuesta["tiempo"] = $dataRes["tiempo"];
        
        if(isset($dataRes["response"],$dataRes["response"]->$tagResp)){
            $dataRes = $dataRes["response"]->$tagResp->queryUserOttResponse;
            $arrayRespuesta = array();
            if( $dataRes->resultCode == 0 ){
                $resultMessage = strval($dataRes->resultMessage);
                $correlatorId = strval($dataRes->correlatorId);
                $data = $dataRes->subscriptionList->subscription;
                
                for( $i = 0; $i <= count($data)-1; $i++ ){
                    $Respuesta = array();
                    foreach($data[$i] as $datos){
                        $Respuesta[] = array( strval($datos->key) => strval($datos->value));
                    }
                    $arrayRespuesta[] = $Respuesta;
                }
                
                $arrayResponse = [
                    'resultMessage'=>$resultMessage,
                    'correlatorId'=> $correlatorId,
                    "suscripciones"=>$arrayRespuesta
                ];
                $respuesta["response"] = $arrayResponse;
                $respuesta["error"] = 0;
            }else{
                $respuesta["error"] = 1;
                $respuesta["response"] = strval($dataRes->resultMessage);
            }
        }else{
            $respuesta["error"] = 1;
            $respuesta["response"] = "No registra información";
        }
    }else {
        $respuesta["error"] = 1;
        $respuesta["response"] = "No se encontró información";
    }
    
    return $response->withJson($respuesta)->withHeader('Content-type', 'application/json'); 
    

});


$app->run();
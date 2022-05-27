<?php
require __DIR__ . '/../../Core/vendor/autoload.php';
require __DIR__ . '/../../Core/Middleware.php';
require_once __DIR__ . '/../../Core/utils/CurlClass.php';

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

// Se crea la app
$app = new \Slim\App();
$container = $app->getContainer();
$container['view'] = new \Slim\Views\PhpRenderer(__DIR__.'/template/');

$container['curlClass'] = new CurlClass();

//Url del servicio
//$container['urlServicio']="http://172.24.160.161:8600/EXP_WSCustomeCusID/PS_WSCustomeCusIDV1.0";    //Desarrollo
//$container['urlServicio']="http://172.24.160.161:8600/EXP_WSCustomeCusID/PS_WSCustomeCusIDV1.0";    //Pre Produccion
$container['urlServicio']="http://172.24.160.135:8080/EXP_WSCustomeCusID/PS_WSCustomeCusIDV1.0";    //Produccion

//Nombre del template Request
$container['requestTemplate']="request.php"; 

$app->add(new MiddlewareApp(dirname(__FILE__), $app->getContainer()));

$app->map(['POST'], '/', function (Request $request, Response $response, array $args) {
    
    $dataJson = $request->getAttribute('dataJson');
    $respuesta = array();
    
    if( isset($dataJson->customerId) ){
        $reqXML = $this->view->fetch($this->requestTemplate, ['data' => $dataJson]);
    
        $this->curlClass->URL=$this->urlServicio;
        $this->curlClass->POSTFIELDS=$reqXML;
        
        $header[]="";
        
        $dataRes=$this->curlClass->soap($header,true,true);
        
        /************************LOGICA DE RESPONSE***************************/
        
        $respuesta = array();
        if($dataRes["error"] == 0){
            //print_r($dataRes);die;
            //Tag que envia el servicio
            $tagResp = "ns2rootRemoveResponse";

            $respuesta["secs"] = $dataRes["secs"];
            $respuesta["tiempo"] = $dataRes["tiempo"];
            if(isset($dataRes["response"],$dataRes["response"]->$tagResp)){
                $dataRes = $dataRes["response"]->$tagResp;
                if( strval($dataRes->ns2removeResponse->ns2resultCode) == 0){
                    $respuesta["error"] = 0;
                    $respuesta["response"] = "Registro eliminado exitosamente";
                }else{
                    $respuesta["error"] = 1;
                    $respuesta["response"] = strval($dataRes->ns2removeResponse->ns2resultMessage);
                }
            }else{
                $respuesta["error"] = 1;
                $respuesta["response"] = strval($dataRes['response']->soapenvFault->faultstring);
            }
        }else {
            $respuesta["error"] = 1;
            $respuesta["response"] = $dataRes["response"];
        }
    }else{
        $respuesta["error"] = 1;
        $respuesta["response"] = "Error en data. Favor validar";
    }
    return $response->withJson($respuesta)->withHeader('Content-type', 'application/json'); 
    

});


$app->run();
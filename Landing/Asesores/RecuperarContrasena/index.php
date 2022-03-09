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
$container['urlServicio']="http://172.24.160.135:8080/EXP_RSCustomerDataOtt/PS_RSCustomerDataOttV1.0";    //Producción

//Nombre del template Request
$container['requestTemplate']="request.php";

$app->add(new MiddlewareApp(dirname(__FILE__), $app->getContainer()));

$app->map(['POST'], '/', function (Request $request, Response $response, array $args) {
    
    $dataJson = $request->getAttribute('dataJson');
    $respuesta = array();
    if( isset($dataJson->transactionId) && isset($dataJson->employeeId) && isset($dataJson->invokeMethod) ){
    
        $reqXML = $this->view->fetch($this->requestTemplate, ['data' => $dataJson]);

        $this->curlClass->URL=$this->urlServicio;
        $this->curlClass->POSTFIELDS=$reqXML;
        
        $header[]="";
        
        $dataRes=$this->curlClass->soap($header,true,true);

        /************************LOGICA DE RESPONSE***************************/
        
        $respuesta = array();
        if($dataRes["error"] == 0){
            
            //Tag que envia el servicio
            $tagResp = "soapenvFault";

            $respuesta["secs"] = $dataRes["secs"];
            $respuesta["tiempo"] = $dataRes["tiempo"];
            if(isset($dataRes["response"],$dataRes["response"]->$tagResp)){
                $dataRes = $dataRes["response"]->$tagResp->updateUserOttResponse;
                if( strval($dataRes->resultCode) == 0){
                    $respuesta["response"] = strval($dataRes->resultMessage);
                    $respuesta["error"] = 0;                
                }else{
                    $respuesta["error"] = 1;
                    $respuesta["response"] = strval($dataRes->resultMessage);
                }
            }else{
                print("ERROR");die;
                $respuesta["error"] = 1;
                $respuesta["response"] = strval($dataRes['responseServer']);
            }
        }else {
            $respuesta["error"] = 1;
            $respuesta["response"] = $dataRes["responseServer"];
        }
    }else{
        $respuesta["error"] = 1;
        $respuesta["response"] = "Error en data. Favor validar";
    }
    //var_dump($respuesta);die;
    return $response->withJson($respuesta)->withHeader('Content-type', 'application/json'); 
    

});


$app->run();
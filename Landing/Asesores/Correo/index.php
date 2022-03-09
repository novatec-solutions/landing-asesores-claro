<?php

require __DIR__ . '/../../Core/vendor/autoload.php';
require __DIR__ . '/../../Core/Middleware.php';
require_once __DIR__ . '/../../Core/utils/CurlClass.php';

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app = new \Slim\App();
$container = $app->getContainer();
$container['view'] = new \Slim\Views\PhpRenderer(__DIR__.'/template/');

$container['curlClass'] = new CurlClass();

//$container['urlServicio']="http://172.24.160.161:8600/EXP_WSCustomeCusID/PS_WSCustomeCusIDV1.0";    //Desarrollo
//$container['urlServicio']="http://172.24.160.161:8600/EXP_WSCustomeCusID/PS_WSCustomeCusIDV1.0";    //Pre Produccion
$container['urlServicio']="http://172.24.160.135:8080/EXP_WSCustomeCusID/PS_WSCustomeCusIDV1.0";    //Produccion
$container['requestTemplate']="request.php"; 

$app->add(new MiddlewareApp(dirname(__FILE__), $app->getContainer()));

$app->map(['POST'], '/', function (Request $request, Response $response, array $args) {
    
    $dataJson = $request->getAttribute('dataJson');
    $respuesta = array();
    if( isset($dataJson->emailAddress ) ){
        $reqXML = $this->view->fetch($this->requestTemplate, ['data' => $dataJson]);
        
        $this->curlClass->URL=$this->urlServicio;
        $this->curlClass->POSTFIELDS=$reqXML;
        
        $header[]="";
        
        $dataRes=$this->curlClass->soap($header,true,true);
        
        /************************LOGICA DE RESPONSE***************************/
        
        if($dataRes["error"] == 0){
            
            $tagResp = "ns2rootReadResponse";

            $respuesta["secs"] = $dataRes["secs"];
            $respuesta["tiempo"] = $dataRes["tiempo"];
            
            if(isset($dataRes["response"],$dataRes["response"]->$tagResp)){
                $dataRes = $dataRes["response"]->$tagResp;
                
                if( strval($dataRes->ns2readResponse->ns2resultCode) == 0){
                    $dataRes = $dataRes->ns2readResponse->ns2data;
                    
                    $customerId = strval($dataRes->ns2customerId);
                    $providerId = strval($dataRes->ns2providerId);
                    $idNumber = strval($dataRes->ns2idNumber);
                    $fixedAccount = strval($dataRes->ns2fixedAccount);
                    $operatorUserId = strval($dataRes->ns2operatorUserId);
                    $emailAddress = strval($dataRes->ns2emailAddress);
                    $firstName = strval($dataRes->ns2firstName);
                    $lastName = strval($dataRes->ns2lastName);
                    $paymentType = strval($dataRes->ns2paymentType);
                    $arrayResponse = [
                        'customerId'=>$customerId,
                        'providerId'=> $providerId,
                        'idNumber'=> $idNumber,
                        'fixedAccount'=> $fixedAccount,
                        'operatorUserId'=> $operatorUserId,
                        'emailAddress'=> $emailAddress,
                        'firstName'=> $firstName,
                        'lastName'=> $lastName,
                        'paymentType'=> $paymentType
                    ];
                    $respuesta["response"] = $arrayResponse;
                    $respuesta["error"] = 0;

                }else{
                    $respuesta["error"] = 1;
                    $respuesta["response"] = strval($dataRes->ns2readResponse->ns2resultMessage);
                }
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
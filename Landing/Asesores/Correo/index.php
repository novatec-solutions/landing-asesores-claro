<?php
require __DIR__ . '/../../Core/vendor/autoload.php';
require_once __DIR__ . '/../../Core/utils/CurlClass.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

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
    
    return $response->withJson($respuesta)->withHeader('Content-type', 'application/json'); 
    

});


$app->run();
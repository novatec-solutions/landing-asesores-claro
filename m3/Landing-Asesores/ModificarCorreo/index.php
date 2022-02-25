<?php
require __DIR__ . '/../../Core/vendor/autoload.php';
require_once __DIR__ . '/../../Core/config.php';

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

// Se crea la app
$app = new \Slim\App();
$container = $app->getContainer();
$container['view'] = new \Slim\Views\PhpRenderer(__DIR__.'/template/');

$container['curlWigi'] = new \wigilabs\curlWigiM3\curlWigi();

//Url del servicio
$container['urlServicio']="http://172.24.160.161:8600/EXP_WSCustomeCusID/PS_WSCustomeCusIDV1.0";    //Desarrollo
//$container['urlServicio']="http://172.24.160.161:8600/EXP_WSCustomeCusID/PS_WSCustomeCusIDV1.0";    //Pre Produccion

//Nombre del template Request
$container['requestTemplate']="request.php"; 

$app->map(['POST'], '/', function (Request $request, Response $response, array $args) {
    
    //$headers = $request->getHeaders();
    $json = json_decode( $request->getBody() );
    $data=$json->data;
    
    $reqXML = $this->view->fetch($this->requestTemplate, ['data' => $data]);

    $this->curlWigi->URL=$this->urlServicio;
    $this->curlWigi->POSTFIELDS=$reqXML;
    
    $header[]="";
    
    $dataRes=$this->curlWigi->soap($header,true,true);
    
    /************************LOGICA DE RESPONSE***************************/
    
    $respuesta = array();
    if($dataRes["error"] == 0){
        //print_r($dataRes);die;
        //Tag que envia el servicio
        $tagResp = "ns2rootModifyResponse";

        $respuesta["secs"] = $dataRes["secs"];
        $respuesta["tiempo"] = $dataRes["tiempo"];
        if(isset($dataRes["response"],$dataRes["response"]->$tagResp)){
            $dataRes = $dataRes["response"]->$tagResp;
            //print_r($dataRes->ns2modifyResponse);die;
            if( strval($dataRes->ns2modifyResponse->ns2resultMessage) == 0){
                $respuesta["error"] = 0;
                $respuesta["response"] = strval($dataRes->ns2modifyResponse->ns2resultMessage);
            }else{
                $respuesta["error"] = 1;
                $respuesta["response"] = strval($dataRes->ns2modifyResponse->ns2resultMessage);
            }
        }else{
            //print_r($dataRes['response']->soapenvFault);die;
            //print_r($dataRes['response']->soapenvFault->faultstring);die;
            $respuesta["error"] = 1;
            $respuesta["response"] = strval($dataRes['response']->soapenvFault->faultstring);
        }
    }else {
        $respuesta["error"] = 1;
        $respuesta["response"] = $dataRes["responseServer"];
    }
    
    //var_dump($respuesta);die;
    return $response->withJson($respuesta)->withHeader('Content-type', 'application/json'); 
    

});


$app->run();
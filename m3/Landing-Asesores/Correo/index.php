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
    //var_dump($data);die;

    $reqXML = $this->view->fetch($this->requestTemplate, ['data' => $data]);
    
    $this->curlWigi->URL=$this->urlServicio;
    $this->curlWigi->POSTFIELDS=$reqXML;
    
    $header[]="";
    
    $dataRes=$this->curlWigi->soap($header,true,true);
    //var_dump($dataRes);die;
    
    /************************LOGICA DE RESPONSE***************************/
    
    $respuesta = array();
    if($dataRes["error"] == 0){
        //print_r($dataRes);die;
        //Tag que envia el servicio
        $tagResp = "ns2rootReadResponse";

        $respuesta["secs"] = $dataRes["secs"];
        $respuesta["tiempo"] = $dataRes["tiempo"];
        
        if(isset($dataRes["response"],$dataRes["response"]->$tagResp)){
            $dataRes = $dataRes["response"]->$tagResp;
            //print_r($dataRes);die;
            //print_r(strval($dataRes->ns2readResponse->ns2resultCode));die;
            if( strval($dataRes->ns2readResponse->ns2resultCode) == 0){
                $dataRes = $dataRes->ns2readResponse;
                $respuesta["error"] = 0;
                /*$respuesta["ns2customerId"] = strval($dataRes->ns2data->ns2customerId);
                $respuesta["ns2idNumber"] = strval($dataRes->ns2data->ns2idNumber);
                $respuesta["ns2resultMessage"] = strval($dataRes->ns2data->ns2resultMessage);*/
                $respuesta["ns2customerId"] = strval($dataRes->ns2data->ns2customerId);
                $respuesta["ns2providerId"] = strval($dataRes->ns2data->ns2providerId);
                $respuesta["ns2idNumber"] = strval($dataRes->ns2data->ns2idNumber);
                $respuesta["ns2fixedAccount"] = strval($dataRes->ns2data->ns2fixedAccount);
                $respuesta["ns2operatorUserId"] = strval($dataRes->ns2data->ns2operatorUserId);
                $respuesta["ns2emailAddress"] = strval($dataRes->ns2data->ns2emailAddress);
                $respuesta["ns2firstName"] = strval($dataRes->ns2data->ns2firstName);
                $respuesta["ns2lastName"] = strval($dataRes->ns2data->ns2lastName);
            }else{
                $respuesta["error"] = 1;
                $respuesta["descripcion"] = strval($dataRes->ns2readResponse->ns2resultMessage);
            }
        }
    }else {
        $respuesta["error"] = 1;
        $respuesta["response"] = $dataRes["responseServer"];
    }
    
    //var_dump($respuesta);die;
    return $response->withJson($respuesta)->withHeader('Content-type', 'application/json'); 
    

});


$app->run();
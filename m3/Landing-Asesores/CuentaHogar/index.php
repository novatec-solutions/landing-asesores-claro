<?php
require __DIR__ . '/../../Core/vendor/autoload.php';
require_once __DIR__ . '/../../Core/config.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

// Se crea la app
$app = new \Slim\App();
$container = $app->getContainer();
$container['view'] = new \Slim\Views\PhpRenderer(__DIR__.'/template/');

$container['curlWigi'] = new \wigilabs\curlWigiM3\curlWigi();

//Url del servicio
//$container['urlServicio']="http://172.22.61.94:9024/WSRoamingConvertible/WSRoamingConvertibleService";
$container['urlServicio']="http://172.24.160.161:8600/EXP_WSCustomeCusID/PS_WSCustomeCusIDV1.0";

//Nombre del template Request
$container['requestTemplate']="request.php"; 



$app->map(['POST'], '/', function (Request $request, Response $response, array $args) {
    print("Ingresa"); die;
    //$headers = $request->getHeaders();
    $json = json_decode( $request->getBody() );
    $data=$json->data;
    
    $reqXML = $this->view->fetch($this->requestTemplate, ['data' => $data]);
    
    $this->curlWigi->URL=$this->urlServicio;
    $this->curlWigi->POSTFIELDS=$reqXML;
    
    $header[]="";
    
    $dataRes=$this->curlWigi->soap($header,true,true);
    print_r($dataRes);die;
    
    /************************LOGICA DE RESPONSE***************************/
    
    $respuesta = array();
    if($dataRes["error"] == 0){
        //print_r($dataRes);die;
        //Tag que envia el servicio
        $tagResp = "ns2rootReadResponse";

        $respuesta["secs"] = $dataRes["secs"];
        $respuesta["tiempo"] = $dataRes["tiempo"];
        //print_r($dataRes["response"]->$tagResp);die;
        
        if(isset($dataRes["response"],$dataRes["response"]->$tagResp)){
            $dataRes = $dataRes["response"]->$tagResp;
            print_r($dataRes);die;
            
            /*if( strval($dataRes->RoamingResponse->codigo) == 1){
                $respuesta["error"] = 0;
                $respuesta["codigo"] = strval($dataRes->RoamingResponse->codigo);
                $respuesta["descripcion"] = strval($dataRes->RoamingResponse->descripcion);
                $respuesta["mensaje"] = strval($dataRes->RoamingResponse->mensaje);
            }else{
                $respuesta["error"] = 1;
                $respuesta["descripcion"] = strval($dataRes->RoamingResponse->descripcion);
            }*/
        }
    }else {
        $respuesta["error"] = 1;
        $respuesta["response"] = $dataRes["responseServer"];
    }
    
    print_r($respuesta);die;
    //return $response->withJson($respuesta)->withHeader('Content-type', 'application/json'); 
    

});


$app->run();
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

    $this->curlWigi->URL=$this->urlServicio;
    $this->curlWigi->POSTFIELDS=$reqXML;
    
    $header[]="";
    
    $dataRes=$this->curlWigi->soap($header,true,true);
    
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
    
    //var_dump($respuesta);die;
    return $response->withJson($respuesta)->withHeader('Content-type', 'application/json'); 
    

});


$app->run();
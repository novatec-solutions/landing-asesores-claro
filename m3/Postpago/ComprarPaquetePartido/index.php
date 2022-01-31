<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require __DIR__ . '/../../Core/vendor/autoload.php';
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

// Se crea la app
$app = new \Slim\App();
$container = $app->getContainer();
$container['view'] = new \Slim\Views\PhpRenderer(__DIR__.'/template/');

$container['curlWigi'] = new \wigilabs\curlWigiM3\curlWigi();

//Url del servicio
$container['urlServicio']="https://portalpagosselfcare.claro.com.co/SelfcarePRE/SelfCareManagementService.svc?wsdl"; 
//Nombre del template Request
$container['requestTemplate']="request.php"; 



$app->map(['POST','OPTIONS'], '/', function (Request $request, Response $response, array $args) {

    $headers = $request->getHeaders();
    $json = json_decode($request->getBody());
    
    $data=$json->data;

    $reqXML = $this->view->fetch($this->requestTemplate, ['data' => $data]);
    
    $this->curlWigi->URL=$this->urlServicio;
    $this->curlWigi->POSTFIELDS=$reqXML;
    
    $header[]="SOAPAction: \"Claro.SelfCareManagement.Services.Entities.Contracts/SelfCareManagementService/comprarPaquetePartido\"";
    
    $dataRes=$this->curlWigi->soap($header,true);


    if($dataRes["error"] == 0){

        //Tag que envia el servicio
        $tagResp = "ComprarPaquetePartidoResponse";

        $respuesta = array();
        $respuesta["secs"] = $dataRes["secs"];
        $respuesta["tiempo"] = $dataRes["tiempo"];


        if(isset($dataRes["response"],$dataRes["response"]->$tagResp)){
            $dataRes = $dataRes["response"]->$tagResp;

         
           $respuesta["error"]=0;
            if ($dataRes->esCompraAplicada=="true") {
             $respuesta["response"]=array("comprado"=>1);
            }else{
             $respuesta["response"]=array("comprado"=>0);
            }
        }
    }
    else{
           $respuesta = $dataRes;
    }
    
    
    return $response->withJson($respuesta)->withHeader('Content-type', 'application/json');
});

$app->add(function ($req, $res, $next) {
    $response = $next($req, $res);
    return $response
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Headers', 'X-API-KEY,X-SESION-ID, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method, authorization')
            ->withHeader('Access-Control-Allow-Methods', 'POST, OPTIONS');
});


$app->run();
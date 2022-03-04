<?php
require __DIR__ . '/../../Core/vendor/autoload.php';
require_once __DIR__ . '/../../Core/config.php';
//require_once __DIR__ . '/../../Core/GibberishAES.php';

use M3\Classes\Soap\RunSoap;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

// Se crea la app
$app = new \Slim\App();
$container = $app->getContainer();
$container['view'] = new \Slim\Views\PhpRenderer(__DIR__.'/template/');

$container['curlWigi'] = new \wigilabs\curlWigiM3\curlWigi();

//Url del servicio
$container['urlServicio']="http://100.126.0.150:11051/WsPortalUsuariosRest-web/ws/WsPortalUsuariosRest/autentica/";  //Desarrollo

//Nombre del template Request
$container['requestTemplate']="request.php"; 

$app->map(['PUT'], '/', function (Request $request, Response $response, array $args) {

    //var_dump( GibberishAES::enc("Contraseña|2022-02-22", "Claro.*2019#123"));
    //die;

    $json = json_decode( $request->getBody() );

    $data=$json->data;
    $data->password = md5($data->password);

    $reqJSON = $this->view->fetch($this->requestTemplate, ['data' => $data]);

    $this->curlWigi->URL=$this->urlServicio;
    $this->curlWigi->POSTFIELDS=$reqJSON;

    $headers[]="";
    
    $objSoap = new RunSoap($this->urlServicio, $reqJSON);
    $objSoap->setIsSoap(false);
    $objSoap->setHeader($headers);
    $objSoap->setBaseHeaders(["Content-type: application/json;charset=\"utf-8\""]);
    $dataRes = $objSoap->execSoapPut();
    
    $respuesta = array();
    $respuesta["error"] = 0;
    $res= json_decode(iconv('UTF-8', 'UTF-8//IGNORE', utf8_encode($dataRes["response"])));
    $respuesta["response"] = $res->estado;

    return $response->withJson($respuesta)->withHeader('Content-type', 'application/json');    

});

$app->run();
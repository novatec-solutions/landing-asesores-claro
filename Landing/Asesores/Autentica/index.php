<?php

require __DIR__ . '/../../Core/vendor/autoload.php';
require __DIR__ . '/../../Core/Middleware.php';
require_once __DIR__ . '/../../Core/utils/CurlClass.php';

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

/* Se crea la app */
$app = new \Slim\App();
$container = $app->getContainer();
$container['view'] = new \Slim\Views\PhpRenderer(__DIR__ . '/template/');

$container['curlClass'] = new CurlClass();
$container['urlServicio'] = "http://100.126.0.150:11051/WsPortalUsuariosRest-web/ws/WsPortalUsuariosRest/autentica/";  //Desarrollo
$container['requestTemplate'] = "request.php";

$app->add(new MiddlewareApp(dirname(__FILE__), $app->getContainer()));

$app->map(['POST'], '/', function (Request $request, Response $response, array $args) {

    $dataJson = $request->getAttribute('dataJson');
    //var_dump($dataJson);die;
    $ldapuser  = $dataJson->usuario; 
    
    //$decrypted = CryptoUtils::decrypt($dataJson->password);
    //$ldappass = trim($decrypted);

    $decryptedPassword = CryptoUtils::encryptMD5($dataJson->password);
    $decryptedPassword = trim($decryptedPassword);
    var_dump($decryptedPassword);die;

    //ingresar parámetro en array   --  Yddi0i2NaTiJGYY8a3yyuNbIEEgrufFW

    $reqXML = $this->view->fetch($this->requestTemplate, ['data' => $dataJson]);

    $this->curlClass->URL=$this->urlServicio;
    $this->curlClass->POSTFIELDS=$reqXML;
    
    $header[]="";
    
    $dataRes=$this->curlClass->soap($header,true,true);
    var_dump($dataRes);die;
    
    return $response->withJson($respuesta)->withHeader('Content-type', 'application/json');
});

$app->run();

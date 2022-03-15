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
    $ldapuser  = $dataJson->usuario; 
    
    $encryptPassword = CryptoUtils::encryptMD5($dataJson->password);
    $encryptPassword = trim($encryptPassword);
    
    $dataJson->password = $encryptPassword;

    $reqJSON = $this->view->fetch($this->requestTemplate, ['data' => $dataJson]);

    $url = $this->urlServicio;

    $this->curlClass->URL=$url;
    $this->curlClass->POSTFIELDS=$reqJSON;

    $dataRes=$this->curlClass->simple_put($url, ($reqJSON));

    $respuesta = array();

    $respuesta["error"] = '0';
    $respuesta["response"] = json_decode($dataRes);
    
    return $response->withJson($respuesta)->withHeader('Content-type', 'application/json');
});

$app->run();

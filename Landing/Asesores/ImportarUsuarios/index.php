<?php

require __DIR__ . '/../../Core/vendor/autoload.php';
require __DIR__ . '/../../Core/Middleware.php';
require __DIR__ . '/../../Core/utils/Users.php';

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app = new \Slim\App();
$container = $app->getContainer();
$app->add(new MiddlewareApp(dirname(__FILE__), $app->getContainer()));

$app->map(['POST'], '/', function (Request $request, Response $response, array $args) {
    $urlFileUsers = __DIR__ . '/../../Core/Data/users.dat';
    $dataJson = $request->getAttribute('dataJson');
    $respuesta = array();
    if( count($dataJson)>0 ){
        if (file_exists($urlFileUsers)) {
            $users = new Users($urlFileUsers);
            $resultImport=$users->setUpdateUsers($dataJson);
            if($resultImport>0){
                $respuesta["error"] = 0;
                $respuesta["response"] = "Archivo importado con éxito.";
            } 
            else {
                $respuesta["error"] = 1;
                $respuesta["response"] = "Error al escribir el archivo.";
            }
        } 
        else {
            $respuesta["error"] = 2;
            $respuesta["response"] = "No existe el directorio de usuarios autorizados.";
        }
    } 
    else {
        $respuesta["error"] = 3;
        $respuesta["response"] = "El archivo seleccionado no cuenta con datos validos.";
    }
    return $response->withJson($respuesta)->withHeader('Content-type', 'application/json'); 
});

$app->run();
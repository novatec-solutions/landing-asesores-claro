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
 
    if (file_exists($urlFileUsers)) {
        $users = new Users($urlFileUsers);
        $dataUsers=$users->getListUsers();

        if (count($dataUsers)>0){
            if (is_array($dataUsers)){
                $respuesta["error"] = 0;
                $respuesta["response"] = $dataUsers;
            } 
            else {
                $respuesta["error"] = 3;
                $respuesta["response"] = "Directorio no valido.";
            }
        } 
        else {
            $respuesta["error"] = 2;
            $respuesta["response"] = "El directorio de usuarios esta vacio.";
        }
    } 
    else {
        $respuesta["error"] = 1;
        $respuesta["response"] = "No existe el directorio de usuarios autorizados.";
    }

    return $response->withJson($respuesta)->withHeader('Content-type', 'application/json'); 
    
});

$app->run();
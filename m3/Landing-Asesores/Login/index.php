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

$userMaps = array(
    'user' => 'asesor1',
    'pass' => '123456',
    'name' => 'Gabriel Pérez',
);

$app->map(['POST'], '/', function (Request $request, Response $response, array $args) {
    
    $json = json_decode( $request->getBody() );
    $data=$json->data;

    var_dump($json);
    die;
    
    $respuesta = array();
    if($userMaps["user"] == $data->user && $userMaps["pass"] == $data->pass){
        $respuesta["error"] = false;
        $respuesta["mensaje"] = "Inicio de sesión correcto";
        $respuesta["data"] = $userMaps;
    }else {
        $respuesta["error"] = true;
        $respuesta["mensaje"] = "Usuario o contraseña incorrectos";
    }

    return $response->withJson($userMaps)->withHeader('Content-type', 'application/json'); 
    
});


$app->run();
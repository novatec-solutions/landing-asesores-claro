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
    if( isset($dataJson->fullName) && isset($dataJson->userName) && isset($dataJson->userRole) ){
        if (file_exists($urlFileUsers)) {
            $users = new Users($urlFileUsers);
            $arrUsers=$users->getListUsers();
            if (count($arrUsers)>0){
                if (is_array($arrUsers)){
                    //validar si el usuario ingresado existe
                    $usersNames = array_column($arrUsers, "usuario");
                    $findUser = $dataJson->userNameOld;
                    if (preg_grep( "/$findUser/i" , $usersNames )) {
                        //validar posición de usuario registrado
                        $userkey = array_search($dataJson->userNameOld, array_column($arrUsers, "usuario"));
                        //modificar información de usuario
                        $arrUsers[$userkey]->nombre=$dataJson->fullName;
                        $arrUsers[$userkey]->usuario=$dataJson->userName;
                        $arrUsers[$userkey]->rol=$dataJson->userRole;
                        //actualizar archivo .dat
                        $userRegister=$users->setUpdateUsers($arrUsers);
                        if($userRegister>0){
                            $respuesta["error"] = 0;
                            $respuesta["response"] = "Usuario actualizado con éxito.";
                        } 
                        else {
                            $respuesta["error"] = 6;
                            $respuesta["response"] = "Error al escribir el archivo.";
                        }
                    }
                    else {
                        $respuesta["error"] = 5;
                        $respuesta["response"] = "El nombre de usuario ingresado ya se encuentra registrado.";
                    }
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
    } 
    else {
        $respuesta["error"] = 4;
        $respuesta["response"] = "Información de usuario no valida.";
    }
    return $response->withJson($respuesta)->withHeader('Content-type', 'application/json'); 
});

$app->run();
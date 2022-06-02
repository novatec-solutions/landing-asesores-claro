<?php

require __DIR__ . '/../../Core/vendor/autoload.php';
require __DIR__ . '/../../Core/Middleware.php';
require_once __DIR__ . '/../../Core/utils/CurlClass.php';
require __DIR__ . '/usuariosAutorizados.php';

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
    $allowUsers = allowUsers; //proviene del archivo usuariosAutorizados


    if (in_array($dataJson->usuario, array_column($allowUsers, "user"))) {
        $ldapuser = $dataJson->usuario; 
        $ldappass = $dataJson->password;
        $ldaprole = $allowUsers[$userkey]["role"];
    
        //$decrypted = CryptoUtils::decrypt($dataJson->password);
        //$ldappass = trim($decrypted); 

        $userkey = array_search($dataJson->usuario, array_column($allowUsers, "user"));

        if(empty($ldappass)){
            /* Password vacio */
            $respuesta["error"] = 3;
            $respuesta["response"] = "FAILED";
        }else{
            $ldap = [
                'timeout' => 20,
                'host' => '172.24.232.140',
                'rdn' => 'CLAROCO\\' . $ldapuser,
                'pass' => $ldappass
            ];
            $host = $ldap["host"];
            $ldapport = 389;

            $ldapconn = ldap_connect($host, $ldapport)  or die("Fallo conexion con LDPA");
            
            ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3);
            ldap_set_option($ldapconn, LDAP_OPT_REFERRALS, 0);

            if ($ldapconn) {
                 /* Realiza la autenticacion */
                 $ldapbind = @ldap_bind($ldapconn, $ldap["rdn"], $ldap["pass"]);
                 $respuesta = array();
                 if ($ldapbind) {
                    $respuesta["error"] = 0;
                    $respuesta["response"]["estado"] = "OK_SESSION";
                    $respuesta["response"]["usuario"]["usuario"] = $dataJson->usuario;
                    $respuesta["response"]["usuario"]["estado"] = "A";
                    $respuesta["response"]["usuario"]["role"] = $ldaprole;
                } else {
                    /* Credenciales inválidas */
                    $respuesta["error"] = 1;
                    $respuesta["response"] = "FAILED";
                }
            }
        }
    } else{
         /* Usuario no permitido */
        $respuesta["error"] = 2;
        $respuesta["response"] = "FAILED";
    }
    return $response->withJson($respuesta)->withHeader('Content-type', 'application/json');
});

$app->run();

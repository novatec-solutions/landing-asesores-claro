<?php
require __DIR__ . '/../../Core/vendor/autoload.php';
require_once __DIR__ . '/../../Core/config.php';
//require_once __DIR__ . '/../../Core/GibberishAES.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

use M3\Classes\Soap\RunSoap;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

// Se crea la app
$app = new \Slim\App();
$container = $app->getContainer();
$container['view'] = new \Slim\Views\PhpRenderer(__DIR__ . '/template/');

$container['curlWigi'] = new \wigilabs\curlWigiM3\curlWigi();

//Url del servicio
$container['urlServicio'] = "http://100.126.0.150:11051/WsPortalUsuariosRest-web/ws/WsPortalUsuariosRest/autentica/";  //Desarrollo

//Nombre del template Request
$container['requestTemplate'] = "request.php";

$app->map(['POST'], '/', function (Request $request, Response $response, array $args) {

    //var_dump( GibberishAES::enc("Contraseña|2022-02-22", "Claro.*2019#123"));
    //die;

    $json = json_decode($request->getBody());

    $data = $json->data;

    $allowUsers = array(
        'ECM1795A'
    );

    if (in_array($json->data->usuario, $allowUsers)) {
        $ldapuser  = $json->data->usuario;     // ldap rdn or dn
        $ldappass = $json->data->password;  // associated password

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
            // realizando la autenticación
            $ldapbind = @ldap_bind($ldapconn, $ldap["rdn"], $ldap["pass"]);

            $respuesta = array();
            // verificación del enlace
            if ($ldapbind) {
                $respuesta["error"] = 0;
                $respuesta["response"] = "SUCCESS";
            } else {
                $respuesta["error"] = 1;
                $respuesta["response"] = "FAILED";
            }
        }
    } else{
        $respuesta["error"] = 2;
        $respuesta["response"] = "FAILED";
    }

/*
    $reqJSON = $this->view->fetch($this->requestTemplate, ['data' => $data]);

    $this->curlWigi->URL = $this->urlServicio;
    $this->curlWigi->POSTFIELDS = $reqJSON;

    $headers[] = "";

    $objSoap = new RunSoap($this->urlServicio, $reqJSON);
    $objSoap->setIsSoap(false);
    $objSoap->setHeader($headers);
    $objSoap->setBaseHeaders(["Content-type: application/json;charset=\"utf-8\""]);
    $dataRes = $objSoap->execSoapPut();

    $respuesta = array();
    $respuesta["error"] = 0;
    $respuesta["response"] = json_decode(iconv('UTF-8', 'UTF-8//IGNORE', utf8_encode($dataRes["response"])));
*/
    return $response->withJson($respuesta)->withHeader('Content-type', 'application/json');
});

$app->run();

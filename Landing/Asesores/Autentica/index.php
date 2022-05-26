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

    $allowUsers = array(
        /**
         * El usuario de red se debe parametrizar en mayúscula 
         */
        "ETV5116A",
        "JEISSON.LIDUENA",
        "JORGE.BEJARANO.B",
        "JOSE.FERRER",
        "JUAN.SIBAJA",
        "OLBAIRO.SANTANA",
        "OSCAR.JIMENEZ",
        "CAROLS.SUAREZ",
        "GABRIEL.CALDERON",
        "JOHN.GUAUQUE",
        "OSCAR.JIMENEZ",
        "ICO2559B",
        "ALEXANDER.FUQUEN",
        "JOHN.POVEDA",
        "ICF3714C",
        "ICM8602A",
        "ICM6221B",
        "ECM2120J",
        "ICM7072A",
        "ICO1307D",
        "ECM0863G",
        "ALVARO.CONTRERAS",
        "ALVARO.ROBERTO",
        "ICM5119B",
        "ECM0480C",
        "IC6373A",
        "EHT7492A",
        "HAROLD.LOAIZA.EXT",
        "HUGO.ROMERO",
        "HUGO.ROMERO",
        "ICO0198A",
        "LUIS.DEVIA",
        "ICF6344A",
        "EC8371O",
        "ICF8986C",
        "ICM5656H",
        "ECF2454B",
        "LIBARDO.ROBLES",
        "ICM9428B",
        "ALEXANDER.DELGADO",
        "GUSTAVO.GARCIA",
        "GUSTAVO.TORRES.G",
        "HERNANDO.OSPINA",
        "JAVIER.BARRIOS",
        "JOHAN.LOPEZ",
        "JORGE.GOMEZ",
        "JOSE.MUNOZ.M",
        "MIGUEL.CARABALI",
        "SANDRA.VARGAS.P",
        "ANDRES.HERRERA",
        "DORA.ROA",
        "ICM5223A",
        "JUAN.LOPEZ.P",
        "LUZ.MORALES",
        "ICM9926A",
        "ICF2583A",
        "ICO8986A",
        "ICM8627B",
        "D3971606",
        "ICO1147A",
        "JUAN.SIERRA",
        "ICM9289A",
        "ICF3038A",
        "ICM4980A",
        "ICM9455B",
        "AJUSTESRESIDENCIAL",
        "ICF7719A",
        "ECM5167J",
        "DELIA.TORRES",
        "DIEGO.BETANCUR",
        "GINA.ZULETA",
        "JOHN.TAFUR",
        "JOSELV",
        "EIQ5485A",
        "ECF0958C",
        "NIDIA.SARMIENTO",
        "OSCAR.VALLEJO",
        "YERSY.QUINONEZ",
        "ICF1710B",
        "MAURICIO.BAYONA",
        "RODRIGO.MARQUEZ",
        "ECM4004B",
        "ICO2749B",
        "EYB3987A",
        "EMI6245A",
        "ETN3011A",
        "ICL8730A",
        "ICM3056A",
        "ECM1302A",
        "ICO8810A",
        "E2969341",
        "E1840468",
        "EJT5910A",
        "ECM1710B",
        "ETV0888A",
        "ETV0564A",
        "ICF8423A",
        "ICF9472C",
        "ETV5335A",
        "ECM1894C",
        "ICM7211B",
        "ICM4859B",
        "ICM4859B",
        "ALVAROCA",
        "ICM1770A",
        "ALVAROCA",
        "ICM2761A",
        "ICM7296A",
        "ICM6713A",
        "ICM9961A",
        "ICM2954B",
        "ICM1625B",
        "ICO6191A",
        "D5523561",
        "ICO4957B",
        "ICM8406B",
        "ICM4876A",
        "ICO3948C",
        "ICO8813A",
        "ICM8736C",
        "ICM3052A",
        "ICO2073A",
        "A0031305",
        "ICM0503B",
        "ICM5864A",
        "ICM7741A",
        "ICO2431B",
        "ICM4058C",
        "ICO0657A",
        "ICM5275C",
        "ICO9173B",
        "ICM3587A",
        "ICM8214A",
        "ICM4724B",
        "C9358557",
        "ICM3964B",
        "ICF9719B",
        "ICM9066B",
        "A0784313",
        "ICO2951C",
        "E2466459",
        "ICF5871A",
        "ICM9227B",
        "B5292901",
        "H0220423",
        "D0780571",
        "ICF3218A",
        "ICM2694A",
        "ICF7797A",
        "C6309801",
        "ICM0366B",
        "C1435759",
        "ICF7049D",
        "ICO5254A",
        "ICO5212A",
        "ICM8493B",
        "ICF9823A",
        "ICO1394A",
        "ICO5590A",
        "ICM1627B",
        "ICM0853B",
        "ETV5116A",
        "E2799802",
        "ICM6567A",
        "HERMISUP",
        "ICM9824A",
        "ICM0809A",
        "ICM4619B",
        "ICM8940A",
        "ICM9824A",
        "ICM7362A",
        "H0236059",
        "ICM8511A",
        "GLORIRV",
        "ICM4427A",
        "E2853132",
        "ICM9675A",
        "G5086052",
        "ICF6907A",
        "ICM4915B",
        "ICM7758A",
        "ICO1409C",
        "ICM4585B",
        "ICM4516A",
        "CLAUDIAVI",
        "DIANAP",
        "ICM2398A",
        "ICM5632A",
        "ICO1929A",
        "XIMENAB",
        "ICF2993E",
        "G9536912",
        "JHOANA.GUARIN",
        "ICO4051D",
        "ICM7272B",
        "ICM1563A",
        "D0991061",
        "ICM9681A",
        "ICO1585A",
        "ICM0134A",
        "ICF1054B",
        "ICM4844A",
        "ICF0511B",
        "D0042003",
        "ICF8950C",
        "GIOVANGV",
        "ICM8707E",
        "JAIMEC",
        "ICF4620C",
        "H8262308",
        "G4189658",
        "IC9470A"
    );

    if (in_array($dataJson->usuario, $allowUsers)) {
        $ldapuser  = $dataJson->usuario; 
        $ldappass  = $dataJson->password;
    
        //$decrypted = CryptoUtils::decrypt($dataJson->password);
        //$ldappass = trim($decrypted); 

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

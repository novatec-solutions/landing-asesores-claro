<?php
require __DIR__ . '/../../Core/vendor/autoload.php';
require_once __DIR__ . '/../../Core/utils/CurlClass.php';
require __DIR__ . '/../../Core/Middleware.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

// Se crea la app
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
        'ECM1795A',
        'ECM1710B',
        'CAROLINA.DIAZ.C',
        'GABRIEL.CALDERON',
        'ALVARO.ROBERTO',
        'ALEXANDER.FUQUEN',
        'JOHN.POVEDA',
        'JOHN.GUAUQUE',
        'ERIK.ROJAS.EXT',
        'OSCAR.JIMENEZ',
        'YURI.MARTIN',
        'CONNY.ARNEDO',
        'MARIA.SARMIENTO',
        'CAROL.SUAREZ',
        'LIBARDO.ROBLES',
        'JONATHAN.CORTEST',
        'AIDA.QUINTERO',
        'OSCAR.JIMENEZ',
        'JULY.ENRIQUEZ',
        'LUIS.DEVIA',
        'ALVARO.CONTRERAS',
        'DANIEL.TRUJILLOA',
        'FABIAN.LEONC',
        'GABRIELA.ARIAS',
        'HUGO.ROMERO',
        'RODRIGO.MARQUEZ',
        'MIGUEL.CASTANEDAA',
        'HUGO.ROMERO',
        'HAROLD.LOAIZA.EXT',
        'PABLO.MARTINEZM.EXT',
        'ALFREDO.RINCON',
        'DIEGO.CORRALES',
        'EDUARDE.TRUJILLOR',
        'CRISTIAN.VILLAMIL',
        'DIANA.FARFANP',
        'JOHNATAN.ALVAREZN',
        'LUIS.MARTINEZ.C',
        'ASTRID.CORREA',
        'ANDERSON.CASTRO',
        'GLADYS.HIGUITA',
        'FERNANDO.MORENOR',
        'CAMILOA.BULLAL',
        'MIGUEL.GONZALEZS',
        'JEFFERSON.RAMIREZ.EXT',
        'SANDRA.VARGAS.P',
        'JAVIER.BARRIOS',
        'KATHERINE.GALINDOS',
        'YENITH.SANCHEZG',
        'CRISTIAN.VIVASA',
        'OSCAR.VALLEJO',
        'NIDIA.SARMIENTO',
        'JOSE.LUBIN',
        'DIEGO.BETANCUR',
        'JOHN.TAFUR',
        'DELIA.TORRES',
        'ECM5167J',
        'LILI.BLANCOS.EXT',
        'KAREN.ARGUELLO.EXT',
        'CARLOS.MORENO',
        'AJUSTESRESIDENCIAL',
        'YERSY.QUINONEZ',
        'JOHAN.LOPEZ',
        'ALEXANDER.DELGADO',
        'HERNANDO.OSPINA',
        'MIGUEL.CARABALI',
        'GUSTAVO.TORRES.G',
        'GINA.ZULETA',
        'GUSTAVO.GARCIA',
        'JORGE.GOMEZ',
        'JOSE.MUNOZ.M',
        'MAURICIO.BAYONA',
        'ETJ5910A',
        'EJT5910A'
    );

    if (in_array($dataJson->usuario, $allowUsers)) {

        $ldapuser  = $dataJson->usuario; 

        $decrypted = CryptoUtils::decrypt($dataJson->password);
        $ldappass = trim($decrypted); 

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
                    $respuesta["response"] = "SUCCESS";
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

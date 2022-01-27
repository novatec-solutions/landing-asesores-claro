 <?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
require_once(__DIR__ . '/../midleware/asesoresMiddlewareSoap.php');

/*
Realiza la validacion del Ticklet de legalizacion y genera el pin OTP
body del servicio
{
    "data": {
        "prepaidMin ": "3138892493",
        "docNum": "80203160",
        "channel": "prueba"
    }
}
*/

$app->post('/api/v1/validationLegalizationPin', function (Request $request, Response $response, array $args) {
    return $response;
})->add($mwGeneratePin)->add($mvOrqvalidationLegalizationPin)->add($mwcustomerTickler)->add($mwTokenValidation);  


 <?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
require_once(__DIR__ . '/../midleware/legalizationMiddlewareSoap.php');
require_once(__DIR__ . '/../midleware/legalizationMiddlewareData.php');
require_once(__DIR__ . '/../midleware/legalizationMiddlewareOrchestration.php');

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


/*
Valida el pin OTP y consulta la validacion de legalizacion
body del servicio
{
    "data": {
        "pin": 4727,
        "prepaidMin": "3138892493",
        "docType": "1",
        "docNum": "52709413",
        "expeditionDate": "19051998",
        "lastName": "RODRIGUEZ",
        "channel": "prueba"
    }
}
*/
$app->post('/api/v1/validatePinLegalization', function (Request $request, Response $response, array $args) {
    return $response;
})->add($mwValidateLegalization)->add($mvOrqvalidatePinLegalization)->add($mwValidatePin)->add($mwTokenValidation);  

/*
Realiza la actualizacion de la legalizacion o la laegalizacion
body del servicio
{
    "data": {
        "prepaidMin": "3223283409",
        "greetings": "Sr.",
        "docType": "1",
        "docNum": "1013590073",
        "expeditionDate": "29082005",
        "name": "CARLOS ANDRES",
        "lastName": "PEDRAZA RUBIANO",
        "birthDate": "22081987",
        "indicativeMin": "1",
        "phone": "3102632231",
        "mail": "andres623925@gmail.com",
        "department": "Cundinamarca",
        "city": "Bogotá",
        "municipality": "Bogotá", //centro poblado
        "neighborhood": "Victoria Norte",
        "address": "Calle 148 n 54c 91", // dirección Armada
        "channel": "APP"
    }
}
*/
$app->post('/api/v1/legalizeMin', function (Request $request, Response $response, array $args) {
    return $response;
})->add($mwCustomerLegalization)->add($mwCustomerUpgrade)->add($mvOrqlegalizeMin)->add($mwValidateLegalization)->add($mwTokenValidation);        
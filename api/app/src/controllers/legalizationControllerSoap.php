 <?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
require_once(__DIR__ . '/../midleware/legalizationMiddlewareSoap.php');
require_once(__DIR__ . '/../midleware/legalizationMiddlewareData.php');

/*
body del servicio
{
    "data": {
        "prepaidMin": "3223283409",
        "flow": "1",
        "greetings": "Mr",
        "docType": "1",
        "docNum": "1013590073",
        "birthDate": "22081987",
        "address": "Calle 148 n 54c 91",
        "postalCode": "1",
        "department": "Cundinamarca",
        "city": "Bogota",
        "neighborhood": "Victoria Norte",
        "mail": "andres623925@gmail.com",
        "indicativeMin": "1",
        "phone": "3102632231",
        "event": "1",
        "expeditionDate": "29082005",
        "surnameOne": "PEDRAZA",
        "surnameTwo": "RUBIANO",
        "nameOne": "CARLOS",
        "nameTwo": "ANDRES",
        "idTransaccion": "1",
        "channel": "APP",
        "municipality": "Bogota"
    }
}
*/
$app->post('/api/v1/customerLegalization', function (Request $request, Response $response, array $args) {
    return $response;
})->add($mwCustomerLegalization)->add($mwTokenValidation)->add($mmValidateActiveSoap);        

/*
body del servicio
{
    "data": {
        "prepaidMin": "3223283409",
        "flow": "1",
        "greetings": "Mr",
        "name": "Carlos An",
        "lastName": "Pedraza Rubiano",
        "docType": "1",
        "docNum": "1013590073",
        "birthDate": "22081987",
        "address": "Calle 148 n 54c 91",
        "postalCode": "1",
        "department": "Cundinamarca",
        "city": "Bogota",
        "neighborhood": "Victoria Norte",
        "mail": "andres623925@gmail.com",
        "indicativeMin": "1",
        "phone": "3102632231",
        "channel": "APP"
    }
}
*/
$app->post('/api/v1/customerUpgrade', function (Request $request, Response $response, array $args) {   
    return $response;
})->add($mwCustomerUpgrade)->add($mwTokenValidation)->add($mmValidateActiveSoap);   

/*
body del servicio
{
    "data": {
        "prepaidMin": "3138892493",
        "docType": "1",
        "docNum": "52709413",
        "expeditionDate": "19051998",
        "lastName": "rueda",
        "channel": "prueba"
    }
}
*/
$app->post('/api/v1/validateLegalization', function (Request $request, Response $response, array $args) {   
    return $response;    
})->add($mwValidateLegalization)->add($mwTokenValidation)->add($mmValidateActiveSoap);    

/*
body del servicio
{
{
    "data": {
        "prepaidMin": "3223283409",
        "channel": "APP"
    }
}
*/
$app->post('/api/v1/customerTickler', function (Request $request, Response $response, array $args) {
    return $response;
})->add($mwcustomerTickler)->add($mwTokenValidation)->add($mmValidateActiveSoap);    

/*
body del servicio
{
    "data": {
        "prepaidMin": "3138892493",
        "docNum": "80203160",
        "channel": "prueba"
    }
}
*/
$app->post('/api/v1/generatePin', function (Request $request, Response $response, array $args) {
    return $response;
})->add($mwGeneratePin)->add($mwTokenValidation)->add($mmValidateActiveSoap);    

/*
body del servicio
{
    "data": {
        "pin": "4727",
        "docNum": "80203160",
        "channel": "prueba"
    }
}
*/
$app->post('/api/v1/validatePin', function (Request $request, Response $response, array $args) {
    return $response;
})->add($mwValidatePin)->add($mwTokenValidation)->add($mmValidateActiveSoap);   
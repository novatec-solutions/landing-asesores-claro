<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use App\Entity\{City2, Departament, Various, Populated};
require_once(__DIR__ . '/../midleware/legalizationMiddlewareData.php');
require_once(__DIR__ . '/../midleware/legalizationMiddlewareSoap.php');

/*
Retorna la lista de departamentos
body del servicio
{
    "data": {
        "token": "Akw@pNr{A5?aV.zVwvTD%YX#:Nh2Lh"
    }
}
*/
$app->post('/api/v1/depto', function (Request $request, Response $response, array $args) {
    return $response;  
})->add($mwDepto)->add($mwTokenValidation);

/*
Retorna la lista general de ciudades
body del servicio
{
    "data": {
        "token": "Akw@pNr{A5?aV.zVwvTD%YX#:Nh2Lh"
    }
}
*/
$app->post('/api/v1/city', function (Request $request, Response $response, array $args) {
    return $response;  
})->add($mwCity)->add($mwTokenValidation);

/*
Retorna la lista de los tipos
body del servicio
{
    "data": {
        "token": "Akw@pNr{A5?aV.zVwvTD%YX#:Nh2Lh"
    }
}
*/
$app->post('/api/v1/getVarious', function (Request $request, Response $response, array $args) {
    return $response;        
})->add($mwGetVarious)->add($mwTokenValidation);     

/*
Retorna la lista de los tipos
body del servicio
{
    "data": {
        "token": "Akw@pNr{A5?aV.zVwvTD%YX#:Nh2Lh"
    }
}
*/
$app->post('/api/v1/getInitialVarious', function (Request $request, Response $response, array $args) {
    return $response;        
})->add($mwGetInitialVarious)->add($mwTokenValidation);     

/*
Retorna la lista de los tipos
body del servicio
{
    "data": {
        "type" : "SALUDO"
    }
}
*/
$app->post('/api/v1/getVariousByType', function (Request $request, Response $response, array $args) {
    return $response;        
})->add($mwGetVariousByType)->add($mwTokenValidation);  

/*
Retorna un tipo por si id
body del servicio
{
    "data": {
        "variousId" : 5
    }
}
*/
$app->post('/api/v1/getVariousById', function (Request $request, Response $response, array $args) {
    return $response;        
})->add($mwGetVariousById)->add($mwTokenValidation);  

/*
Registra un nuevo item
body del servicio
{
    "data": {
        "various": {
            "description" : "hola",
            "type" : "SALUDO",
            "order" : 4,
            "status" : "A",
            "externalId" : 4 
        }
    }
}
*/
$app->post('/api/v1/addVarious', function (Request $request, Response $response, array $args) {
    return $response;        
})->add($mwAddVarious)->add($mwTokenValidation)->add($mmValidateActiveSoap);  

/*
Registra un nuevo item
body del servicio
{
    "data": {
        "various": {
            "variousId" : 33,
            "description" : "hola",
            "type" : "SALUDO",
            "order" : 4,
            "status" : "A",
            "externalId" : 4 
        }
    }
}
*/
$app->post('/api/v1/updateVarious', function (Request $request, Response $response, array $args) {
    return $response;        
})->add($mwUpdateVarious)->add($mwTokenValidation)->add($mmValidateActiveSoap);  

/*
Retorna la lista de ciudades por un departamento
body del servicio
{
    "data": {
                "depto": {
                "departamentId":"30"
            }
    }
}
*/
$app->post('/api/v1/citybydepto', function (Request $request, Response $response, array $args) {
    return $response;        
})->add($mwCitybydepto)->add($mwTokenValidation);  

/*
Retorna la lista de poblados por ciudad
body del servicio
{
    "data": {
                "city": {
                "cityId":"3"
            }
    }
}
*/
$app->post('/api/v1/populatedbycity', function (Request $request, Response $response, array $args) {
    return $response;        
})->add($mwPopulatedbycity)->add($mwTokenValidation);  


/*
Edita el estado de un departamento
body del servicio
{
    "data": {
            "depto": {
                "departamentId":"5",
                "order": 5,
                "status":"I"
            }
    }
}
*/
$app->put('/api/v1/editstatusdepto', function (Request $request, Response $response, array $args) {
    return $response;        
})->add($mwEditstatusdepto)->add($mwTokenValidation)->add($mmValidateActiveSoap);  

/*
Edita los atributos de una ciudad
body del servicio
{
    "data": {
           "cityrequest": {
                "cityId":"5",
                "status":"A",
                "order":5
            }
    }
}
*/
$app->put('/api/v1/editcity', function (Request $request, Response $response, array $args) {
    return $response;        
})->add($mwEditcity)->add($mwTokenValidation)->add($mmValidateActiveSoap); 

/*
Edita el estado de las ciudades pertenecientes a un departamento
body del servicio
{
    "data": {
            "deptorequest": {
                "departamentId":"5",
                "status":"I"
            }
    }
}
*/
$app->put('/api/v1/editstatuscitybydepto', function (Request $request, Response $response, array $args) {
    return $response;        
})->add($mwEditstatuscitybydepto)->add($mwTokenValidation); 

/*
Generar token
*/
$app->post('/api/v1/token', function (Request $request, Response $response, array $args) {
    return $response;      
})->add($mwToken)->add($mmValidateProduction);

/*
consultar log
*/

$app->post('/api/v1/log', function (Request $request, Response $response, array $args) {
    return $response;      
})->add($mwLog);


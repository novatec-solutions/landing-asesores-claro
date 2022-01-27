<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use App\Entity\{City2, Departament, Various, Populated};

/*
valida que este disponible el uso en produccion, si la valriable PRODUCTION es false
*/
$mmValidateProduction = function  (Request $request, Response $response, $next) {    
    if(!filter_var($_ENV['PRODUCTION'], FILTER_VALIDATE_BOOLEAN)){
        $response = $next($request, $response);
        return $response;
    }else{  
        return $response->withStatus(403);
    }
};
/*
Realiza la validacion del token
*/

$mwTokenValidation = function(Request $request, Response $response, $next){
    $validate_token = validateMessage($request->getHeaders(), json_decode($request->getBody()));
    if($validate_token != "Ok"){  
        $respuesta["error"] = 1;
        $respuesta["response"] = $validate_token;
        return $response->withJson($respuesta,401)->withHeader('Content-type', 'application/json');         
    }
    else{
        $response = $next($request, $response);        
    }
    return $response;
};


<?php
// Application middleware

// e.g: $app->add(new \Slim\Csrf\Guard);


 $headersApi = function ($req, $res, $next) {
    $response = $next($req, $res);
    return $response
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization, X-SESSION-TOKEN, X-ORIGIN')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST');            
};
    
$app->add($headersApi);



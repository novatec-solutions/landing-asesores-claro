<?php
require_once __DIR__ . '/utils/CryptoUtils.php';

class MiddlewareApp
{
    private $container;
    private $file;

    public function __construct($_file,$_container) {
        $this->container = $_container;
        $this->file = $_file;
    }

    public function __invoke($request, $response, $next){

        $reqBody = json_decode($request->getBody());

        $decrypted = CryptoUtils::decrypt($reqBody->data);

        /**
         * Security */   
        $headers = $request->getHeaders();
        $dataJson = json_decode($decrypted);

        $request = $request
        ->withAttribute('dataJson', $dataJson->data)
        ->withAttribute('headers', $headers);

        $respuesta = $next($request, $response);

        return $respuesta;
    }
}
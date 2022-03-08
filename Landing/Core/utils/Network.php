<?php

//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

function simple_get_headers($url,$token, $name_header="Authorization" ){
    $agent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.0.3705; .NET CLR 1.1.4322)';
    $soap_do = curl_init();
    curl_setopt($soap_do, CURLOPT_URL, $url);
    curl_setopt($soap_do, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($soap_do, CURLOPT_TIMEOUT, 30);
    curl_setopt($soap_do, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($soap_do, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($soap_do, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($soap_do, CURLOPT_POST, "GET");
    $authorization = "$name_header: ".$token;
    curl_setopt($soap_do, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , $authorization ));
    curl_setopt($soap_do, CURLOPT_USERAGENT, $agent);
    $res = curl_exec($soap_do);

    return $res;

}

function simple_get($url, $headers = []){
    $soap_do = curl_init();
    curl_setopt($soap_do, CURLOPT_URL, $url);
    curl_setopt($soap_do, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($soap_do, CURLOPT_TIMEOUT, 30);
    curl_setopt($soap_do, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($soap_do, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($soap_do, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($soap_do, CURLOPT_POST, "GET");

    //curl_setopt($soap_do, CURLOPT_POSTFIELDS,     $params);
    curl_setopt($soap_do, CURLOPT_HTTPHEADER, $headers);
    $res = curl_exec($soap_do);

    return $res;

}

function simple_get_technical_visit($url){
    $username = 'e09939df2ba9dc5d33a1b51f195026a97@amx-res-co';
    $password = 'dba4faa33079c0cc801d6f7a3da07d85ce72c7eb674bde54ff3774fa5578bc79';

    $soap_do = curl_init();

    $headerData = array(
        "Content-type: application/json;charset=\"utf-8\"",
        "Accept: application/json",
        "Cache-Control: no-cache",
        "Pragma: no-cache",
        "Authorization: Basic ".base64_encode($username.":".$password)
    );

    curl_setopt($soap_do, CURLOPT_USERPWD, $username . ":" . $password);
    curl_setopt($soap_do, CURLOPT_URL,$url);
    //curl_setopt($soap_do, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($soap_do, CURLOPT_TIMEOUT,        30);
    curl_setopt($soap_do, CURLOPT_RETURNTRANSFER, true );
    curl_setopt($soap_do, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($soap_do, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($soap_do, CURLOPT_POST,           "GET");

    //curl_setopt($soap_do, CURLOPT_POSTFIELDS,     $params);
    curl_setopt($soap_do, CURLOPT_HTTPHEADER,     $headerData);
    //curl_setopt($soap_do, CURLOPT_HTTPHEADER,     array());
    $res = curl_exec($soap_do);

    return $res;

}

function simple_post_headers($url, $data, $headers = []){

    $contentExist = false;
    $jsonContent=false;
    foreach($headers as $i){
        if(strpos(strtolower($i),"content-type") !== false){
            $contentExist = true;
            if(strpos(strtolower($i),"application/json") !== false){
                $jsonContent = true;
            }
            break;
        }
    }

    if(!$contentExist){
        array_push($headers, 'Content-Type:application/json');
        $jsonContent = true;
    }

    $data = $jsonContent?json_encode($data):$data;

    
    
    $soap_do = curl_init();
    curl_setopt($soap_do, CURLOPT_URL,$url);
    curl_setopt($soap_do, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($soap_do, CURLOPT_TIMEOUT,        30);
    curl_setopt($soap_do, CURLOPT_RETURNTRANSFER, true );
    curl_setopt($soap_do, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($soap_do, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($soap_do, CURLOPT_POST,           true );
    curl_setopt($soap_do, CURLOPT_POSTFIELDS,     $data);
    curl_setopt($soap_do, CURLOPT_HTTPHEADER,     $headers);
    $res = curl_exec($soap_do);

    return $res;
}

function simple_post_rest($url,$data){
    $agent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.0.3705; .NET CLR 1.1.4322)';
    $soap_do = curl_init();
    curl_setopt($soap_do, CURLOPT_URL,$url);
    curl_setopt($soap_do, CURLOPT_CONNECTTIMEOUT,30);
    curl_setopt($soap_do, CURLOPT_TIMEOUT,        30);
    curl_setopt($soap_do, CURLOPT_RETURNTRANSFER, true );
    curl_setopt($soap_do, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($soap_do, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($soap_do, CURLOPT_POST,           true );
    curl_setopt($soap_do, CURLOPT_ENCODING,'UTF-8' );
    curl_setopt($soap_do, CURLOPT_POSTFIELDS,     json_encode($data));
    curl_setopt($soap_do, CURLOPT_USERAGENT, $agent);

    curl_setopt($soap_do, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
    $res = curl_exec($soap_do);

    return $res;
}

function simple_request_with_method($url, $data, $method="GET"){
    $soap_do = curl_init();
    curl_setopt($soap_do, CURLOPT_URL, $url);
    curl_setopt($soap_do, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($soap_do, CURLOPT_CUSTOMREQUEST , $method);
    curl_setopt($soap_do, CURLOPT_POSTFIELDS, $data);

    curl_setopt($soap_do, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
    $res = curl_exec($soap_do);

    return $res;

}

function simple_put($url,$data){
    $soap_do = curl_init();
    curl_setopt($soap_do, CURLOPT_URL,$url);
    curl_setopt($soap_do, CURLOPT_CONNECTTIMEOUT,30);
    curl_setopt($soap_do, CURLOPT_TIMEOUT,        30);
    curl_setopt($soap_do, CURLOPT_RETURNTRANSFER, true );
    curl_setopt($soap_do, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($soap_do, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($soap_do, CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt($soap_do, CURLOPT_POSTFIELDS,     json_encode($data));
    curl_setopt($soap_do, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
    $res = curl_exec($soap_do);

    return $res;
}
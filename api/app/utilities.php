<?php

function getKey(){
    $key = $_ENV['SECRET_KEY'];
    return $key;
}
function getExpMin($type){
    $min = $_ENV['EXPTOKEN'];
    if($type =="ST"){
        $minexp = "-".$min." minute";        
    }else if($type =="EN"){
        $minexp = "+".$min." minute";
    }    
    return $minexp;
}
function getValExp(){
     $enable = filter_var($_ENV['ENABLE_EXP_TOKEN'], FILTER_VALIDATE_BOOLEAN);
     return $enable;   
}

function validateMessage($headers, $body){
    if (isset($headers["HTTP_X_SESSION_TOKEN"]) && isset($headers["HTTP_X_ORIGIN"])) {
       try {
            include 'GibberishAES.php';
            $encrypter = new GibberishAES;
            $encrypter_token = $headers["HTTP_X_SESSION_TOKEN"][0];
            switch ($headers["HTTP_X_ORIGIN"][0]) {
                case "NET":
                        $arr_token = json_decode(decrypted($encrypter_token));
                        break;
                case "ANG":
                        $arr_token = json_decode($encrypter->dec($encrypter_token, getKey()));
                        break;
                default:
                        $arr_token = null;
                        break;
            }

            if (isset($body->data) && ($arr_token->data == $body->data) && !is_null(json_encode($body->data)) && (strlen(json_encode($body->data))>10) ){
                //Validar temporalidad del token
                if(getValExp()){
                    $expdate= $arr_token->expDate;
                    $stardate = new DateTime();
                    $enddate = new DateTime();
                    $stardate->modify(getExpMin("ST")); 
                    $enddate ->modify(getExpMin("EN")); 
                    if(!((strtotime($expdate)>=strtotime(date_format($stardate, "Y-m-d H:i:s"))) && (strtotime($expdate)<=strtotime(date_format($enddate, "Y-m-d H:i:s"))))){
                        return "Token Expirado";
                    }
                }
                return "Ok";
            }else{
                return "Error token invalido";
            }
        } catch (Throwable $e) {
            if(strpos($e->getMessage(), 'SQLSTATE[')  !== false){
                return strstr($e->getMessage(),'SQLSTATE[');
            }

            $bool = filter_var($_ENV['PRODUCTION'], FILTER_VALIDATE_BOOLEAN);
            if($bool){
                return "Error en la validacion del token";
            }else{
                return $e->getMessage();
            }
        }
    }else{
        return "No se recibio el token de validación y el origen de la aplicación.";
    }
}

function encrypt($jsonmessage, $origen){
    include 'GibberishAES.php';
    $encrypter = new GibberishAES;
    $date = new DateTime();
    $newjson=json_decode($jsonmessage);
    $newjson->expDate =date_format($date, "Y-m-d H:i:s");
    $newjson=json_encode($newjson);
    if($origen == "NET"){
        return encrypted($newjson);
    }

    return $encrypted_token = $encrypter->enc($newjson, getKey());     
}

function exceptionLog($str){
    return strpos($str, 'SQLSTATE[')  !== false ? strstr($str,'SQLSTATE['): $str;
}

function validateLog(){
    if(filter_var($_ENV['LOG'], FILTER_VALIDATE_BOOLEAN)){
        return true;
    }
    return false;
}

function validateLogInfo(){
    //if($_ENV['LOG'] == 'A'){
    if(filter_var($_ENV['LOG'], FILTER_VALIDATE_BOOLEAN)){      
        if(filter_var($_ENV['LOG_INFO'], FILTER_VALIDATE_BOOLEAN))
        return true;
    }
    return false;
}

function getEntityManager(){
    $settings = include 'settings.php';
    $config = \Doctrine\ORM\Tools\Setup::createAnnotationMetadataConfiguration(
        $settings['settings']['doctrine']['entity_path'],
        $settings['settings']['doctrine']['auto_generate_proxies'],
        $settings['settings']['doctrine']['proxy_dir'],
        $settings['settings']['doctrine']['cache'],
        false
   );
    return \Doctrine\ORM\EntityManager::create($settings['settings']['doctrine']['connection'], $config);
}

function encrypted($str){
    $method = 'aes-256-cbc';
    $password = substr(hash('sha256', getKey(), true), 0, 32);
    $iv = chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0);
    return base64_encode(openssl_encrypt($str, $method, $password, OPENSSL_RAW_DATA, $iv));
}

function decrypted($str){
    $method = 'aes-256-cbc';
    $password = substr(hash('sha256', getKey(), true), 0, 32);
    $iv = chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0) . chr(0x0);
    return openssl_decrypt(base64_decode($str), $method, $password, OPENSSL_RAW_DATA, $iv);
}

function getTransacciontId(){
    return time()+random_int(1000,9999);
}

 function multi_array_keyvalue_exists( $needle, $haystack ,$value) {
    foreach ( $haystack as $key => $valueArr ) :
        if ( $needle == $key && $valueArr==$value)
            return true;
        if ( is_array( $valueArr ) ) :
             if ( multi_array_keyvalue_exists( $needle, $valueArr,$value) == true )
                return true;
             else
                 continue;
        endif;
    endforeach;
    return false;
}

function multi_array_key_exists( $needle, $haystack) {
    foreach ( $haystack as $key => $value ) :
        if ( $needle == $key)
            return true;
        if ( is_array( $value ) ) :
             if ( multi_array_key_exists( $needle, $value) == true )
                return true;
             else
                 continue;
        endif;
    endforeach;
    return false;
}

function multi_array_key_exists_value( $needle, $haystack, &$findv=null) {
    //$retorno = $findv;
    foreach ( $haystack as $key => $value ) :
        if ( $needle == $key){
            $findv =$value;
            return $findv;            
        }
        if ( is_array( $value ) ) :
             if ( !is_null(multi_array_key_exists_value( $needle, $value,$findv)) )
                return $findv;
             else
                 continue;
        endif;
    endforeach;
    return $findv;
}

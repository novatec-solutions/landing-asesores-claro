<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);


class AppSecurity{
    public function returnError($ov){
        $errorCodes = array(
            "1"=>"No se recibió el token de session",
            "2"=>"Faltan Headers",
            "3"=>"Hubo un error en la desencripción",
            "4"=>"Error en la validación del dispositivo (UUID)",
            "5"=>"Error en la comparacion del Headers y el Token (cht)",
            "6"=>"Error al validar las cabeceras",
            "7"=>"No existe informacion del tiempo en el token",
            "8"=>"Token incompleto",
            "9"=>"Información diferente Headers",
            "10"=>"Información diferente Data",
            "11"=>"Sistema operativo no autorizado",
            "69"=>"Token Vencido",
            "91"=>"Error no controlado en initalValdiation",
            "92"=>"Error no controlado en tokenDecrypt",
            "93"=>"Error no controlado en validateZP",
            "94"=>"Error no controlado en validateTime",
            "95"=>"Error no controlado en validateSO"
        );
    
        $itemErr = $errorCodes[$ov["err"]->internCode];
        $msg = isset($ov["err"]->infoMsg)?$ov["err"]->infoMsg:$itemErr;
        $msg .= " (".$ov["err"]->internCode.")";
        $ov["err"]->response = $msg;
        return $ov["err"];
    }

    public function initalValdiation($ov){

        try{
            if(isset($ov["h"],$ov["h"][$ov["listaH"]["session"]])){
                $ov["token"] = ((count($ov["h"][$ov["listaH"]["session"]]) > 0) ? $ov["h"][$ov["listaH"]["session"]][0] : "error");
                $ov["h"][$ov["listaH"]["mail"]] = isset($ov["h"][$ov["listaH"]["mail"]])?$ov["h"][$ov["listaH"]["mail"]]:array("");
                $validHeaders = isset($ov["h"][$ov["listaH"]["so"]],$ov["h"][$ov["listaH"]["line"]],$ov["h"][$ov["listaH"]["mail"]],$ov["h"][$ov["listaH"]["lob"]]);
    
                $linea = "";$correo = "";$lob = "";$so = "";
    
                if($validHeaders || $ov["skip"]){
                    $linea = $ov["skip"]?"":((count($ov["h"][$ov["listaH"]["line"]]) > 0) ? $ov["h"][$ov["listaH"]["line"]][0] : "error");
                    $correo = $ov["skip"]?"":((count($ov["h"][$ov["listaH"]["mail"]]) > 0) ? $ov["h"][$ov["listaH"]["mail"]][0] : "error");
                    $lob = $ov["skip"]?"":((count($ov["h"][$ov["listaH"]["lob"]]) > 0) ? $ov["h"][$ov["listaH"]["lob"]][0] : "error");
                    $so = $ov["skip"]?"":((count($ov["h"][$ov["listaH"]["so"]]) > 0) ? $ov["h"][$ov["listaH"]["so"]][0] : "error");
                    
                    if($this->validarCabeceras($ov)){
                        $ov["linea"] = $linea;
                        $ov["correo"] = $correo;
                        $ov["lob"] = $lob;
                        $ov["so"] = $so;
                    }else{
                        $ov["err"]->error = 1;
                        $ov["err"]->internCode = 6;
                    }
    
                }else{
                    $ov["err"]->error = 1;
                    $ov["err"]->internCode = 2;
                }
            } else {
                $ov["err"]->error = 1;
                $ov["err"]->internCode = 1;
            }
        }catch (Exception $e) {
            $ov["err"]->error = 1;
            $ov["err"]->internCode = 91;
        }
        
        return $ov;
    }

    public function tokenDecrypt($ov){
        try{

            require_once __DIR__ . '/../GibberishAES.php';
            $encrypter = new GibberishAES;

            $decrypted = $encrypter->dec($ov["token"], getKeyApp());
            $decrypted = json_decode($decrypted);
    
            if (json_last_error() == JSON_ERROR_NONE){
                $ov["data"] = $decrypted;
                $ov["return"]=$ov["skip"];
            } else {
                $ov["err"]->error = 1;
                $ov["err"]->internCode = 3;
            }
        }catch (Exception $e) {
            $ov["err"]->error = 1;
            $ov["err"]->internCode = 92;
        }
        
        return $ov;
    }

    public function validateZP($ov){
        try{
            //******IMPORTANTE*****
            //TODO: Si no viene un X-MC-DEVICE-ID o no es válido genera un error del php (Undefined index: uid)
            //TODO: Si viene el X-MC-USER-AGENT vacio o nulo  genera un error del php (Undefined index: userAgent)
            //TODO: Devolver un error controlado desde las respectivas funciones
            require_once __DIR__ . '/../UuidEh.php';
            $uid = new UuidEh;
    
            $public = isset($ov["data"]->zp)?$ov["data"]->zp=="1":false;
            
            
            $validate_device = $public?$uid->validate_session_webapp_zn($ov["h"]):$uid->validate_session_webapp($ov["h"]);
            if($validate_device["error"] == 0){
                $cht =  $public?array('error' => 0, 'response' => 'N_A'):$this->compareHeadersToken($ov);
                if($cht['error'] == 0) {
                    $ov["return"]=$public;
                }else{
                    $ov["err"]->error = 1;
                    $ov["err"]->internCode = 5;
                    $ov["err"]->infoMsg = $cht["response"];
                }
            }else{
                $ov["err"]->error = 1;
                $ov["err"]->internCode = 4;
                $ov["err"]->infoMsg = $validate_device["response"];
            }
        }catch (Exception $e) {
            $ov["err"]->error = 1;
            $ov["err"]->internCode = 93;
        }
        
        return $ov;
    }

    public function validateTime($ov){
        try{
            if(isset($ov["data"]->inicio,$ov["data"]->valid)){
            
                $tokenTime = strtotime($ov["data"]->inicio);
                $time_valid = (float) $ov["data"]->valid;
                $nowTime = strtotime(date('Y-m-d H:i:s'));
                $minutDiff = round(abs($tokenTime - $nowTime) / 60,2);
    
                if($minutDiff > $time_valid && false){
                    $ov["err"]->error = 69;
                    $ov["err"]->internCode = 69;
                }
            }else{
                $ov["err"]->error = 1;
                $ov["err"]->internCode = 7;  
            }
    
        }catch (Exception $e) {
            $ov["err"]->error = 1;
            $ov["err"]->internCode = 94;
        }
        
        return $ov;
    }

    public function validateInfoData($ov){
        try{
            if(
                isset(
                    $ov["data"]->cuenta,
                    $ov["data"]->usuario,
                    $ov["data"]->cuenta->LineOfBusiness,
                    $ov["data"]->cuenta->AccountId,
                    $ov["data"]->usuario->UserProfileID
                )
            ){
                $otra = isset($ov["data"]->otraLinea) && $ov["data"]->otraLinea == "1";
                if(($ov["linea"] == $ov["data"]->cuenta->AccountId && $ov["correo"] == $ov["data"]->usuario->UserProfileID && $ov["lob"] == $ov["data"]->cuenta->LineOfBusiness) || $ov["skip"] || $otra){
                    
                    $vd = $this->validarData($ov);
                    if(!$vd["valid"]){
                        $ov["err"]->error = 1;
                        $ov["err"]->internCode = 10;
                        $ov["err"]->infoMsg = json_encode($vd);
                    }
                }else{
                    $ov["err"]->error = 1;
                    $ov["err"]->internCode = 9;
                }
            }else{
                $ov["err"]->error = 1;
                $ov["err"]->internCode = 8;
            }
        }catch (Exception $e) {
            $ov["err"]->error = 1;
            $ov["err"]->internCode = 94;
        }
    
        return $ov;
    }


    public function validateSO($ov){
        try{
            //TODO: Validar si es necesario agregar más SO por los landings
            $soList = array('web', 'android', 'huawei', 'ios');
            
            if(!in_array($ov["so"], $soList) && false) {
                $ov["err"]->error = 1;
                $ov["err"]->internCode = 11;
            }else{
                $ov["return"] = true;
            }
        }catch (Exception $e) {
            $ov["err"]->error = 1;
            $ov["err"]->internCode = 95;
        }
        
        return $ov;
    }

    private function validarData($ov){
        $data = isset($ov["dataJson"]->data)?$ov["dataJson"]->data:$ov["dataJson"];
        $otra = isset($data->otraLinea) && $data->otraLinea == "1";
        $linea = isset($data->AccountId)?$data->AccountId:(isset($data->numeroCuenta)?$data->numeroCuenta:(isset($data->cuenta)?$data->cuenta:(isset($data->msisdn)?$data->msisdn:(isset($data->min)?$data->min:""))));
        $lineaH = isset($ov["h"][$ov["listaH"]["line"]])?$ov["h"][$ov["listaH"]["line"]][0]:"";
        $correo = isset($data->correo)?$data->correo:(isset($data->nombreUsuario)?$data->nombreUsuario:"");
        $correoH = isset($ov["h"][$ov["listaH"]["mail"]])?$ov["h"][$ov["listaH"]["mail"]][0]:"";
        $lob = isset($data->LineOfBusiness)?$data->LineOfBusiness:"";
        $lobH = isset($ov["h"][$ov["listaH"]["lob"]])?$ov["h"][$ov["listaH"]["lob"]][0]:"";
        $tokenSSO = isset($data->tokenSSO)?$data->tokenSSO:"";
        $DocumentNumber = isset($data->DocumentNumber)?$data->DocumentNumber:(isset($data->document)?$data->document:"");
    
        $validH = $lineaH == $ov["data"]->cuenta->AccountId && $correoH == $ov["data"]->usuario->UserProfileID && $lobH == $ov["data"]->cuenta->LineOfBusiness;
        $validLinea = $linea!=""?$linea == $ov["data"]->cuenta->AccountId:true;
        $validCorreo = $correo!=""?$correo == $ov["data"]->usuario->UserProfileID:true;
        $validLob = $lob!=""?$lob == $ov["data"]->cuenta->LineOfBusiness:true;
        $validSSO = $tokenSSO!=""?$tokenSSO == $ov["data"]->usuario->tokenSSO:true;
        $validDocument = $DocumentNumber!=""?$DocumentNumber == $ov["data"]->usuario->DocumentNumber:true;
    
        $validGrl = ($validH && $validLinea && $validCorreo && $validLob && $validSSO) || $otra;
    
        return array(
            "valid"=>$validGrl,
            "validH"=>$validH,
            "validLinea"=>$validLinea,
            "validCorreo"=>$validCorreo,
            "validLob"=>$validLob,
            "validSSO"=>$validSSO,
            "validDocument"=>$validDocument,
            "session"=>$ov["data"],
            "cuenta"=>$ov["data"]->cuenta,
            "linea"=>$data,
            "lineaT"=>$ov["data"]->cuenta->AccountId,
            "lineaH"=>$lineaH,
            "correoT"=>$ov["data"]->usuario->UserProfileID,
            "correoH"=>$correoH,
            "lobT"=>$ov["data"]->cuenta->LineOfBusiness,
            "lobH"=>$lobH
        );
    }

    private function validarCabeceras($ov){
        $validate_headers = false;
        
        foreach($ov["listaH"] as $rh) {
            if (array_key_exists($rh, $ov["h"])){
                $validate_headers = true;
            } else {
                $validate_headers = false;
                break;
            }
        }
        return $validate_headers;
    }

    private function compareHeadersToken($ov){

        $error = 0;
        $mensaje = 'Validacion correcta';
    
        if (
            $ov["data"]->usuario->UserProfileID != $ov["correo"] ||
            $ov["data"]->cuenta->AccountId  != $ov["linea"] ||
            $ov["data"]->cuenta->LineOfBusiness  != $ov["lob"]
        ) {
            $error = 1;
            $mensaje = "Error de seguridad, datos enviados para la peticion no son validos (h) ".
            $ov["data"]->usuario->UserProfileID."=".$ov["correo"].",".$ov["data"]->cuenta->AccountId."=".$ov["linea"].",".$ov["data"]->cuenta->LineOfBusiness."=".$ov["lob"];
        };
    
        return array('error' => $error,'response' => $mensaje);
    }
}


#region Validaiones New
function validarSeguridad($headers, $skip = true, $dataJson=false ){
    
    $appSecurity = new AppSecurity();
    $error = new \stdClass;
    $error->response = "Error no definido";

    $listaH = array(
        'so' => "HTTP_X_MC_SO",
        'session' => "HTTP_X_SESSION_ID",
        'lob' => "HTTP_X_MC_LOB",
        'mail' => "HTTP_X_MC_MAIL",
        'line' => "HTTP_X_MC_LINE"
    );
    

    $objValid = array(
        "h" => array_change_key_case($headers, CASE_UPPER),
        "err"=>$error,
        "skip"=>$skip,
        "dataJson"=>$dataJson,
        "listaH"=>$listaH
    );

    //$methodsList = array("initalValdiation","tokenDecrypt","validateZP","validateTime","validateInfoData","validateSO");
    $methodsList = array("initalValdiation","tokenDecrypt","validateTime","validateInfoData","validateSO");

    foreach ($methodsList as $k => $v){
        $objValid = $appSecurity->$v($objValid);    
        if(isset($objValid["err"]->error)){
            return $appSecurity->returnError($objValid);
        }

        if(isset($objValid["return"]) && $objValid["return"]){
            
            $unmask_correo=base64_encode("unmask_correo");
            if(isset($objValid["data"]->usuario->$unmask_correo,$objValid["data"]->usuario->UserProfileID)){
                $objValid["data"]->usuario->UserProfileIDMask=$objValid["data"]->usuario->UserProfileID;
                $objValid["data"]->usuario->UserProfileID=base64_decode($objValid["data"]->usuario->$unmask_correo);
            }

            $unmask_linea=base64_encode("unmask_linea");
            if(isset($objValid["data"]->cuenta->$unmask_linea,$objValid["data"]->cuenta->AccountId)){
                $objValid["data"]->cuenta->AccountIdMask=$objValid["data"]->cuenta->AccountId;
                $objValid["data"]->cuenta->AccountId=base64_decode($objValid["data"]->cuenta->$unmask_linea);
            }
            
            return $objValid["data"];
        }
    }
    
            
     //TODO: Agregar el SO en el token para validar que sea igual al que se le envía en los headers (Versiones)
     //TODO: Agregar el X-MC-APP-V en el token para validar que sea igual al que se le envía en los headers (Versiones)
     //TODO: Agregar el X-MC-USER-AGENT en el token para validar que sea el mismo con el que se generó inicialmente
     //TODO: Validar en firebase con el X-MC-DEVICE-ID si tengo aún una sesión activa (Login)
     //TODO: Crear validación en firebase para cuando se cierre sesión en otro dispositivo (Login)
}
#endregion

#region Encripcion AES
function encrypt($data, $forToken = true,$V2=false){

    if ($forToken) {

        
        require_once __DIR__ . '/../GibberishAES.php';

        $encrypter = new GibberishAES;
        $llave = getKeyApp();
        $response = $encrypter->enc($data, $llave);
    } else if($V2){
        $llave = getKeyParadigmaV2();
        $encrypt_method = 'AES-128-ECB';
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($encrypt_method));
        $response = openssl_encrypt($data, $encrypt_method, $llave, 0, $iv);
    }else{
        $llave = getKeyParadigma();
        $encrypt_method = 'AES-128-ECB';
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($encrypt_method));
        $response = openssl_encrypt($data, $encrypt_method, $llave, 0, $iv);
    }

    return $response;
}

function getKeyApp(){
    $key_token = "PUZ66Q9J";
    return $key_token;
}

function getKeyParadigma(){

    $key_token = "A9b8C7d6E5f4G3h2";
    return $key_token;
}

function getKeyParadigmaV2(){

    $key_token = "B98E0806DC616FBF";
    return $key_token;
}

#endregion
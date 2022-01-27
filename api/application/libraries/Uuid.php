<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/* Se inhabilita las notificaciones de errores php
/*
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
*/
class Uuid {

    private $url_api_service = "https://us-central1-mi-claro-cbbda.cloudfunctions.net";
    private $key_device = '_analytics_wg';
    private $token_device = '_analytics_wg_token';
    private $error_get_device_id = 'Error de seguridad. No se encuentra un dispositivo valido';
    private $error_get_device_token = 'Error de seguridad. No se encuentra un token valido';
    private $error_user_agent = 'Error de seguridad. No se encuentra userAgent';
    private $error_ip = 'Error de seguridad. No se encuentra una IP valida';
    private $url_register_device = "/register_device";
    private $url_consult_device = "/consult_device";
    private $url_lock_device = "/lock_device";
    private $url_update_device = "/update_device";
    private $url_remove_device = "/remove_device";
    private $session_time = 120;
    private $deviceHeader = "X-MC-DEVICE-ID";
    private $sdkKey = "eb4d00dc92d0d80993efeee3889bd26f";
    private $sdkPadding = "d11da23d36fda0b2";
    private $sdkMethod = "AES-256-CBC";
    private $skip = false;
    private $msgSkip = array("error" => "0", "response" => "ok");
    private $url_domain = "";

    function gen_uuid() {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                // 32 bits for "time_low"
                mt_rand(0, 0xffff), mt_rand(0, 0xffff),
                // 16 bits for "time_mid"
                mt_rand(0, 0xffff),
                // 16 bits for "time_hi_and_version",
                // four most significant bits holds version number 4
                mt_rand(0, 0x0fff) | 0x4000,
                // 16 bits, 8 bits for "clk_seq_hi_res",
                // 8 bits for "clk_seq_low",
                // two most significant bits holds zero and one for variant DCE1.1
                mt_rand(0, 0x3fff) | 0x8000,
                // 48 bits for "node"
                mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

    function gen_device_id() {
        if (!isset($_COOKIE[$this->key_device])) {
            // Caduca en 60 segundos
            $uuid = $this->gen_uuid();
            setcookie($this->key_device, $uuid, time() + 2 * $this->session_time);
            return $uuid;
            //return $_COOKIE[$this->key_device];
        } else {
            return $_COOKIE[$this->key_device];
        }
    }

    function remove_device_id() {
        unset($_COOKIE[$this->key_device]);
        unset($_COOKIE[$this->token_device]);
    }

    function get_device_id() {
        if (isset($_COOKIE[$this->key_device])) {
            return $_COOKIE[$this->key_device];
        } else {
            return "";
        }
    }

    function get_device_token($srv, $dv_id) {

        if ($this->skip) {
            return $this->msgSkip;
        }

        if (!empty($srv['HTTP_CLIENT_IP'])) {
            $ip = $srv['HTTP_CLIENT_IP'];
        } elseif (!empty($srv['HTTP_X_FORWARDED_FOR'])) {
            $ip = $srv['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $srv['REMOTE_ADDR'];
        }

        $user_agent = $srv['HTTP_USER_AGENT'];


        //$uid = $this->get_device_id();
        $uid = $dv_id;
        if ($uid == "") {
            return array("error" => 1, "response" => $this->error_get_device_id);
        }
        if ($user_agent == "") {
            return array("error" => 1, "response" => $this->error_user_agent);
        }
        if ($ip == "") {
            return array("error" => 1, "response" => $this->error_ip);
        }

        $data = array(
            "ip" => $ip,
            "userAgent" => $user_agent,
            "uid" => $uid,
            "tipo" => "MOVIL"
        );

        $result = $this->post($this->url_domain . $this->url_register_device, $data);
        $result = json_decode($result, true);
        $error = $result["error"];
        $response = $result["response"];

        $debug = array();
        if ($error == 0) {
            $token = $response["token"];
            setcookie($this->token_device, $token, time() + 2 * $this->session_time);
            $debug["token"] = $token;
            $debug["session_time"] = time() + 2 * $this->session_time;
            $debug["cookie"] = $this->token_device;
            //$debug["cookie_srv"] = $_COOKIE[$this->token_device];
        }

        return $result;
    }

    function validar_token($user_agent, $ip, $token, $method = "consult", $correo = "") {

        $url = "";
        switch ($method) {
            case 'consult':
                $url = $this->url_consult_device;
                break;
            case 'lock':
                $url = $this->url_lock_device;
                break;
            case 'remove':
                $url = $this->url_remove_device;
                break;
            case 'update':
                $url = $this->url_update_device;
                break;
        }

        if ($this->skip) {
            return $this->msgSkip;
        }

        if ($token == "") {
            return array("error" => 1, "response" => $this->error_get_device_token);
        }
        if ($user_agent == "") {
            return array("error" => 1, "response" => $this->error_user_agent);
        }
        if ($ip == "") {
            return array("error" => 1, "response" => $this->error_ip);
        }

        $data = array(
            "ip" => $ip,
            "userAgent" => $user_agent,
            "token" => $token,
            "tipo" => "MOVIL",
            "correo" => $correo
        );



        $result = $this->post($this->url_domain . $url, $data);
        $result = json_decode($result, true);
        return $result;
    }

    function get_token() {
        if ($this->skip) {
            return "tokenTest";
        }

        if (isset($_COOKIE[$this->token_device])) {
            return $_COOKIE[$this->token_device];
        } else {
            return "";
        }
    }

    function post($url, $data) {

        $postdata = json_encode(array('data' => $data));
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }

    function validate_session($srv, $ck) {
        if (!empty($srv['HTTP_CLIENT_IP'])) {
            $ip = $srv['HTTP_CLIENT_IP'];
        } elseif (!empty($srv['HTTP_X_FORWARDED_FOR'])) {
            $ip = $srv['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $srv['REMOTE_ADDR'];
        }

        $user_agent = $srv['HTTP_USER_AGENT'];
        $token = $this->get_token();

        $result = $this->validar_token($user_agent, $ip, $token);
        return $result;
    }

    function generate_app_id($h, $domain) {

        $this->url_domain = $domain;

        $objReturn = array("error" => "0", "response" => "ok");


        if (isset($h, $h[$this->deviceHeader])) {
            $output = $this->get_info_device_app($h);
            if (isset($output, $output["sov"], $output["sdk"], $output["brand"], $output["uid"], $output["so"], $output["ref"])) {
                $infoSrv = $this->set_info_app($output);
                //$objReturn["data"] = $output;
                return $this->get_device_token($infoSrv, $infoSrv["uid"]);
            } else {
                $objReturn["error"] = "1";
                $objReturn["response"] = "En este momento no es posible ingresar a tu cuenta, Por favor inténtalo de nuevo más tarde";
                $objReturn["data"] = $output;
            }
        } else {
            $objReturn["error"] = "1";
            $objReturn["response"] = "En este momento no es posible ingresar a tu cuenta, Por favor inténtalo de nuevo más tarde..";
            $objReturn["data"] = $h;
        }

        return $objReturn;
    }

    function lock_device_app($h, $d, $domain) {

        $this->url_domain = $domain;

        if (isset($d["token_device"]) && $d["token_device"] != "") {
            $output = $this->get_info_device_app($h);
            if (isset($output, $output["sov"], $output["sdk"], $output["brand"], $output["uid"], $output["so"], $output["ref"])) {
                $infoSrv = $this->set_info_app($output);
                $correo = isset($h["X-MC-MAIL"]) ? $h["X-MC-MAIL"] : (isset($d["nombreUsuario"]) ? $d["nombreUsuario"] : "");
                $this->validar_token($infoSrv["HTTP_USER_AGENT"], $infoSrv["HTTP_CLIENT_IP"], $d["token_device"], "lock", $correo);
            }
        }
    }

    function remove_device_app($h, $d, $domain) {

        $this->url_domain = $domain;

        if (isset($d["token_device"]) && $d["token_device"] != "") {
            $output = $this->get_info_device_app($h);
            if (isset($output, $output["sov"], $output["sdk"], $output["brand"], $output["uid"], $output["so"], $output["ref"])) {
                $infoSrv = $this->set_info_app($output);
                $correo = isset($h["X-MC-MAIL"]) ? $h["X-MC-MAIL"] : (isset($d["nombreUsuario"]) ? $d["nombreUsuario"] : "");
                $this->validar_token($infoSrv["HTTP_USER_AGENT"], $infoSrv["HTTP_CLIENT_IP"], $d["token_device"], "remove", $correo);
            }
        }
    }

    function update_device_app($h, $d, $domain) {

        $this->url_domain = $domain;

        if (isset($d["token_device"]) && $d["token_device"] != "") {
            $output = $this->get_info_device_app($h);
            if (isset($output, $output["sov"], $output["sdk"], $output["brand"], $output["uid"], $output["so"], $output["ref"])) {
                $infoSrv = $this->set_info_app($output);
                $correo = isset($d["nombreUsuario"]) ? $d["nombreUsuario"] : (isset($h["X-MC-MAIL"]) ? $h["X-MC-MAIL"] : "");
                $this->validar_token($infoSrv["HTTP_USER_AGENT"], $infoSrv["HTTP_CLIENT_IP"], $d["token_device"], "update", $correo);
            }
        }
    }

    function validate_session_app($h, $d, $domain) {

        $this->url_domain = $domain;

        $objReturn = array("error" => "0", "response" => "ok");

        if (isset($h, $h[$this->deviceHeader])) {
            if (isset($d["token_device"]) && $d["token_device"] != "") {
                $output = $this->get_info_device_app($h);
                if (isset($output, $output["sov"], $output["sdk"], $output["brand"], $output["uid"], $output["so"], $output["ref"])) {
                    $infoSrv = $this->set_info_app($output);
                    $correo = isset($h["X-MC-MAIL"]) ? $h["X-MC-MAIL"] : (isset($d["nombreUsuario"]) ? $d["nombreUsuario"] : "");
                    return $this->validar_token($infoSrv["HTTP_USER_AGENT"], $infoSrv["HTTP_CLIENT_IP"], $d["token_device"], "consult", $correo);
                } else {
                    $objReturn["error"] = "1";
                    $objReturn["response"] = "En este momento no es posible ingresar a tu cuenta, por favor inténtalo de nuevo más tarde!";
                    $objReturn["data"] = $output;
                }
            } else {
                $objReturn["error"] = "1";
                $objReturn["response"] = "En este momento no es posible ingresar a tu cuenta, por favor inténtalo de nuevo más tarde!.";
                $objReturn["data"] = $d;
            }
        } else {
            $objReturn["error"] = "1";
            $objReturn["response"] = "En este momento no es posible ingresar a tu cuenta, por favor inténtalo de nuevo más tarde!..";
            $objReturn["data"] = $h;
        }

        return $objReturn;
    }

    function get_info_device_app($h) {
        $string = isset($h, $h[$this->deviceHeader]) ? $h[$this->deviceHeader] : "";
        $key = $this->sdkKey;
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($this->sdkMethod));
        $output = openssl_decrypt($string, $this->sdkMethod, $key, 0, $this->sdkPadding);
        $output = urldecode($output);
        $output = preg_replace('/[^\PC\s]/u', '', $output);
        return (array) json_decode($output);
    }

    function set_info_app($output) {
        $userAgent = "MiClaroApp/0.0.0 (empty; error; <php/7>)";
        $uid = "XYZ123";
        if (!($output["sov"] == "" && $output["sdk"] == "" && $output["brand"] == "" && $output["uid"] == "" && $output["so"] == "" && $output["ref"] == "")) {
            $userAgent = "MiClaroApp/" . $output["sdk"] . " (" . $output["brand"] . "; " . $output["ref"] . "; <" . $output["so"] . "/" . $output["sov"] . ">)";
            $uid = $output["uid"];
        }

        $ip = "";
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        return array('HTTP_USER_AGENT' => $userAgent, "HTTP_CLIENT_IP" => $ip, "uid" => $uid);
    }

    function getFBS($dFile) {
        $fbs_domain = "";
        $fbs_validate = false;
        if (isset($dFile, $dFile["config"])) {
            $fbs_domain = isset($dFile["config"]["fbs_domain"]) ? $dFile["config"]["fbs_domain"] : "";
            $fbs_validate = isset($dFile["config"]["fbs_validate"]) ? $dFile["config"]["fbs_validate"] : "";
        }

        return array(
            "domain" => $fbs_domain,
            "validate" => $fbs_validate
        );
    }

    /**
     * WEB
     */
    function validate_session_webapp($h) {

        $token = $this->get_info_device_app($h);

        if (isset($h, $h['X-MC-MAIL'], $h['X-MC-USER-AGENT'])) {

            $data_app = json_decode(base64_decode($h['X-MC-USER-AGENT']), true);

            $uid = $h['X-MC-SO'] == 'web' ? $h['X-MC-DEVICE-ID'] : $token['uid'];
            
            if ($uid == "") {
                return array("error" => 1, "response" => $this->error_get_device_id);
            }
            $user_agent = $h['X-MC-SO']  != 'web' ? $data_app["userAgent"] : $data_app['HTTP_USER_AGENT'];      
            if ($user_agent == "") {
                return array("error" => 1, "response" => $this->error_user_agent);
            }
            $ip = $h['X-MC-SO']  != 'web' ? $data_app['ip'] : $data_app['HTTP_CLIENT_IP'];
            if ($ip == "") {
                return array("error" => 1, "response" => $this->error_ip);
            }

            $correo = $h['X-MC-MAIL'];
            $data = array(
                "correo" => $correo,
                "ip" => $ip,
                "userAgent" => $user_agent,
                "token" => $uid,
                "tipo" => $h['X-MC-SO']  != 'web' ? "MOVIL" : "WEB",
            );

            // post consulta
            $_URL = $this->url_api_service . $this->url_consult_device;
            $result = $this->post($_URL, $data);

            $result = json_decode($result);
            $objReturn["error"] = $result->error;
            $objReturn["response"] = $result->response;
        } else {
            $objReturn["error"] = 1;
            $objReturn["response"] = "Datos para la peticion incompletos, compruebe su session e inténtalo de nuevo más tarde!..";
            $objReturn["data"] = array();
        }
        return $objReturn;
    }

    /**
     * WEB ZONA PUBLICA
     */
    function validate_session_webapp_zn($h) 
    {
        $token = $this->get_info_device_app($h);

        if (isset($h, $h['X-MC-USER-AGENT'])) {
            if($h['X-MC-SO'] != 'web') {
                $base64 = base64_decode($h['X-MC-USER-AGENT']);
                $data_app = (array) json_decode($base64, false, 512, JSON_UNESCAPED_UNICODE);
            } else {
                $data_app = json_decode(base64_decode($h['X-MC-USER-AGENT']), true);
            }

            $uid = $h['X-MC-SO'] == 'web' ? $h['X-MC-DEVICE-ID'] : $token['uid'];

            if ($uid == "") {
                return array("error" => 1, "response" => $this->error_get_device_id);
            }
            $user_agent = $h['X-MC-SO']  != 'web' ? $data_app["userAgent"] : $data_app['HTTP_USER_AGENT'];      
            if ($user_agent == "") {
                return array("error" => 1, "response" => $this->error_user_agent);
            }
            $ip = $h['X-MC-SO']  != 'web' ? $data_app['ip'] : $data_app['HTTP_CLIENT_IP'];
            if ($ip == "") {
                return array("error" => 1, "response" => $this->error_ip);
            }

            $data = array(
                "correo" => "",
                "ip" => $ip,
                "userAgent" => $user_agent,
                "token" => $uid,
                "tipo" => $h['X-MC-SO']  != 'web' ? "MOVIL" : "WEB",
            );

            // post consulta
            $_URL = $this->url_api_service . $this->url_consult_device;
            $result = $this->post($_URL, $data);

            $result = json_decode($result);
            $objReturn["error"] = $result->error;
            $objReturn["response"] = $result->response;
        } else {
            $objReturn["error"] = 1;
            $objReturn["response"] = "Datos para la peticion incompletos, compruebe su session e inténtalo de nuevo más tarde!..";
            $objReturn["data"] = array();
        }
        return $objReturn;    
    }
    
    

}

?>
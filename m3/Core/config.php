<?php

if(strstr((get_included_files())[0], '\\M3\\')){
    include_once __DIR__ . '/GibberishAES.php';
}
require_once __DIR__.'/src/Utils.php';
require_once __DIR__.'/utils/Logs.php';
require_once __DIR__.'/utils/Network.php';
require_once __DIR__.'/utils/DB.php';
require_once __DIR__.'/utils/Security.php';
require_once __DIR__.'/utils/Validations.php';
require_once __DIR__.'/utils/curlWigi.php';
require_once __DIR__."/vendor/autoload.php";



$config = array();
$config['srvHost'] = $_SERVER['HTTP_HOST'];
$config['srvProtocol'] = isset($_SERVER['HTTPS'])?'https':'http';
$config['hostProtocol'] = (isset($_SERVER['HTTPS'])?'https':'http')."://".$_SERVER['HTTP_HOST'];
$config['path_apisoap']=$config['hostProtocol']."/back/api/index.php/v1/soap/";
$config['path_m3']=$config['hostProtocol']."/back/M3/";
$_SESSION["config"] = $config;

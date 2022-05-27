<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

class AppLanguage{
  //Nunca debería aparecer este mensaje
  private $default = "Por el momento no se encuentra disponible el sistema";

  private $textList = array(
    "serviceDefault"=>"Por el momento no está disponible esta información.",
    "serviceErrorData"=>"Existe un error en los parametros.",
    "serviceErrorSecurity"=>"Existe un error de seguridad.",
  );


  public function getText($texName) { 
    return array_key_exists($texName,$this->textList)?$this->textList[$texName]:$this->default;
  }


}
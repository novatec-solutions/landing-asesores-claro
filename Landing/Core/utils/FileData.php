<?php
require_once __DIR__ . '/CryptoFileUsers.php';
/*error_reporting(E_ALL);
ini_set('display_errors', '1');*/

class FileData {

    var $urlFile= "";
    var $dataset= "";

    public function __construct($url) {
        $this->urlFile = $url;
        $this->dataset = array();
    }
 
    function readFileUsers() {
        $data = file_get_contents($this->urlFile);
        //desencriptar la información
        $decryptedData = CryptoFileUsers::decrypt($data);
        $_datacrypt = json_decode($decryptedData);
        $this->dataset = $_datacrypt;
    }

    public function getDataset(){
        self::readFileUsers();
        return $this->dataset;
    }

    function writeFileUsers($newData){
        $dataUsers = json_encode($newData);
        //encriptar la información
        $encryptData = CryptoFileUsers::encrypt($dataUsers);
        $result = file_put_contents($this->urlFile, $encryptData);
        return $result;
    }
 
 }
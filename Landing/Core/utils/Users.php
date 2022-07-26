<?php

include_once("FileData.php");

class Users {

    var $arrUsers;
    var $urlFile= "";

    public function __construct($urlFile) {
        $this->arrUsers = new FileData($urlFile);
    }
 
    public function getListUsers() {        
        return ($this->arrUsers->getDataset());
    }

    public function setUpdateUsers($newData) {        
        $registerResponse = $this->arrUsers->writeFileUsers($newData);
        return($registerResponse);
    }
 
 }
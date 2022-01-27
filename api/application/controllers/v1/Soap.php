<?php

defined('BASEPATH') OR exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions

class Soap extends MY_Controller {

    function __construct()
    {
        // Construct the parent class
        parent::__construct();


    }
    
    function index_get()
    {
        $this->response(NULL,404);
    }
 
    function index_post()
    {
        //var_dump();
        $data = $this->get_data('post','data');

        if( $data == null){
            

            //$sData = json_decode(json_decode(file_get_contents('php://input'),true));
            $sData = json_decode(file_get_contents('php://input'),true);
            //$aData = get_object_vars($sData);
        

            if(isset($sData,$sData["data"])){
                $data = $sData["data"];
                
            }
        };

        $res=$this->filtrosServicios($data);
        if (!$res) {
            $res=$this->curl($data);
        }

        $this->return_data($res);
    }

    function filtrosServicios($data){

        if ($this->metodo=="retrievePlans") {
            return $this->retrievePlans($data);
        }else{
            return false;
        }

    }

    function retrievePlans($data){
        if (intval($data["LineOfBusiness"])==2) {

            if (file_exists(APPPATH."views/Constante/".$this->metodo.".php")){

                $resJSON=json_decode($this->load->view("Constante/".$this->metodo,$data,true));

                return array("error"=>0,"response"=>$resJSON,"secs"=>0);

            }else{
                return false;
            }

        }else{
            return false;
        }
    }

 
    function index_put()
    {
        $this->response(NULL,404);
    }
    
 
    function index_delete()
    {
        $this->response(NULL,404);
    }
    
    

}

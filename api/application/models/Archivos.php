<?php

defined('BASEPATH') OR exit('No direct script access allowed');

// This can be removed if you use __autoload() in config.php OR use Modular Extensions

class Archivos extends REST_Controller {

    function __construct()
    {
        // Construct the parent class
        parent::__construct();
        
        //Modelos
        $this->load->model('archivo_model');
        $this->load->library('encrypt');
    }
    
    function index_get()
    {
        
        $tokenEncriptado = $this->get('a');
        if ($tokenEncriptado!=NULL) {
            $id=substr($tokenEncriptado, 0, count($tokenEncriptado)-37);
            
            $query=array("id"=>$id);
            $archivo = $this->archivo_model
                                ->as_object()
                                ->where($query)
                                ->get();
                                
            if ($archivo) {
                $urlArchivo=$this->encrypt->decode($archivo->token);
                
                if ($archivo->es_imagen) {
                    header('Content-Type: image/x-png'); //or whatever
                    readfile('./../web/files/subidos/'.$urlArchivo);
                    die();
                }else{
                    header("Content-Disposition:attachment;filename='".$tokenEncriptado."'");
                    readfile('./../web/files/subidos/'.$urlArchivo);
                    die();
                }
                
            }else{
                $this->response(array('error' => '1','response'=>$this->lang->line("error_archivo")));
            }
        }else{
            header('Content-Type: image/x-png'); //or whatever
            readfile('./../web/files/subidos/default.jpg');
            die();
        }
        
    }
 
    function index_post()
    {
        if (!empty($_FILES)) {
            
            if (!file_exists('./../web/files/subidos/')) {
                mkdir('./../web/files/subidos/', 0777, true);
            }
            
            $config=array(
                        "upload_path"=>"./../web/files/subidos/",
                        "max_size"=>"0",
                        "max_filename"=>"0",
                        "allowed_types"=>"*",
                        "encrypt_name"=>true,
                        "detect_mime"=>true
                        );
            
            $this->load->library('upload', $config);
        
            $errors=array();
            $files=array();
            
            foreach($_FILES as $field => $file)
            {
                if($file['error'] == 0 )
                {
                    if ($this->upload->do_upload($field))
                    {
                        $data=$this->upload->data();
                        array_push($files, $data);
                    }
                    else
                    {
                        array_push($errors, $this->upload->display_errors());
                    }
                }
            }
           
           if (count($errors)>0)
        	{
                $this->response(array('error' => '1','status'=>$errors));
        	}
        	else
        	{
        	    
        	    $ids=array();
        	    foreach($files as $file){
        	        
        	        $nuevo["es_imagen"]=$file["is_image"];
        	        $nuevo["token"]=$this->encrypt->encode($file["file_name"]);
        	        
        	        $id=$this->archivo_model->insert($nuevo);
        	        
        	        if ($id>0) {
        	            $res=array("file"=>$id.md5($id).'.dps');
                        $this->response(array('error' => '0','response'=>$res));
                    }else{
                        $this->response(array('error' => '1','response'=>$this->lang->line("error_insertar")));
                    }
                
        	    }
        	    
                
        	}
        }else{
            $this->response(array('error' => '1','response'=>$this->lang->line("error_nofile")));
        }
    }
 
    function index_put()
    {
        $this->response(NULL,404);
    }
    
 
    function index_delete()
    {
        
        $id = $this->query('id');
        if ($id !=NULL) {
            $data=array("eliminado"=>"1");
            $this->medico_model->update($data,$id);
            $this->response(array('error' => '0','response'=>$this->lang->line("ok_eliminar")));
        }else{
            $this->response(array('error' => '1','response'=>$this->lang->line("error_nodata")));
        }
    }
    
    
    //MOVIL
    
    function movil_get()
    {
        $this->index_get();
    }
 
    function movil_post()
    {
        $this->index_post();
    }
 
    function movil_put()
    {
        $this->response(NULL,404);
    }
    
 
    function movil_delete()
    {
        $this->response(NULL,404);
    }
    
    

}

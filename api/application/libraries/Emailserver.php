<?php
if ( ! defined('BASEPATH') ) exit( 'No direct script access allowed' );

class Emailserver
{
    
    public function send($data){  
        
       $ci =& get_instance();
       
        $config = Array(
            'protocol' => 'sendmail',
            'smtp_host'  => 'ssl://pro.turbo-smtp.com', 
            'smtp_port'  => '25025', 
            'smtp_user'  => 'mvargas@wigilabs.com', 
            'smtp_pass'  => 'OpPbSgya', 
            'mailtype'  => 'html', 
        );
       
/*       $config = Array(
            'protocol' => 'sendmail',
            'mailtype'  => 'html', 
        );*/
        
        $ci->load->library("email"); 
        $ci->email->initialize($config);
        
        $message=str_replace('á', '&aacute;', $data["message"]);
        $message=str_replace('é', '&eacute;', $message);
        $message=str_replace('í', '&iacute;', $message);
        $message=str_replace('ó', '&oacute;', $message);
        $message=str_replace('ú', '&uacute;', $message);
        
        $message=str_replace('Á', '&Aacute;', $message);
        $message=str_replace('É', '&Eacute;', $message);
        $message=str_replace('Í', '&Iacute;', $message);
        $message=str_replace('Ó', '&Oacute;', $message);
        $message=str_replace('Ú', '&Uacute;', $message);
        
        
        $ci->email->from('jgonzalez@tfcopen.com', 'Tennis For Champions Open');
        $ci->email->to($data["to"]); 
        $ci->email->subject($data["subject"]);
        $ci->email->message($message);
        
        
        if ($ci->email->send()){
            return "ok";
        }
        else{
            return "error";
        }
    }

}
?>
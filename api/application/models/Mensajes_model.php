<?php
class Mensajes_model extends MY_Model {
    
    
    function __construct()
    {
        // Call the Model constructor
        $this->table = 'mensajes';
        $this->primary_key = 'id_mensaje';
        $this->return_as = 'object' | 'array';
        $this->protected = array('id_mensaje');
        
		//$this->has_one['ciudad'] = array('CiudadesWifi_model','idCiudad','idCiudad');
		
        $this->delete_cache_on_save = TRUE;
        $this->timestamps = FALSE;
        parent::__construct();
    }

}
?>
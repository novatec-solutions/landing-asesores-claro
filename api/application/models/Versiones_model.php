<?php
class Versiones_model extends MY_Model {
    
    
    function __construct()
    {
        // Call the Model constructor
        $this->table = 'versiones';
        $this->primary_key = 'id_version';
        $this->return_as = 'object' | 'array';
        $this->protected = array('id_version');
        
		//$this->has_one['ciudad'] = array('CiudadesWifi_model','idCiudad','idCiudad');
		
        $this->delete_cache_on_save = TRUE;
        $this->timestamps = FALSE;
        parent::__construct();
    }

}
?>
<?php
class Productos_model extends MY_Model {
    
    
    function __construct()
    {
        // Call the Model constructor
        $this->table = 'repos_productos';
        $this->primary_key = 'productoID';
        $this->return_as = 'object' | 'array';
        $this->protected = array('productoID');
        
		//$this->has_one['ciudad'] = array('CiudadesWifi_model','idCiudad','idCiudad');
		
        $this->delete_cache_on_save = TRUE;
        $this->timestamps = FALSE;
        parent::__construct();
    }

}
?>
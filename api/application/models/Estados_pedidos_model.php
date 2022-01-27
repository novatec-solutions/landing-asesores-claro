<?php
class Estados_pedidos_model extends MY_Model {
    
    
    function __construct()
    {
        // Call the Model constructor
        $this->table = 'repos_historialCambios_estado';
        $this->primary_key = 'id';
        $this->return_as = 'object' | 'array';
        $this->protected = array('id');
        
		//$this->has_one['ciudad'] = array('CiudadesWifi_model','idCiudad','idCiudad');
		
        $this->delete_cache_on_save = TRUE;
        $this->timestamps = FALSE;
        parent::__construct();
    }

}
?>
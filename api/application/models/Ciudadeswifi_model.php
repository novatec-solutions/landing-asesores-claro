<?php
class Ciudadeswifi_model extends MY_Model {
    
    
    function __construct()
    {
        // Call the Model constructor
        $this->table = 'ZonaWifi_Ciudades';
        $this->primary_key = 'idCiudad';
        $this->return_as = 'object' | 'array';
        $this->protected = array('idCiudad');
        
        $this->delete_cache_on_save = TRUE;
        $this->timestamps = FALSE;
        parent::__construct();
    }

}
?>
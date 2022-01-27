<?php
class TiposVisitas_model extends MY_Model {
    
    
    function __construct()
    {
        // Call the Model constructor
        $this->table = 'gps_TiposVisitas';
        $this->primary_key = 'idTipoVisita';
        $this->return_as = 'object' | 'array';
        $this->protected = array('idTipoVisita');
        
        $this->delete_cache_on_save = TRUE;
        $this->timestamps = FALSE;
        parent::__construct();
    }

}
?>
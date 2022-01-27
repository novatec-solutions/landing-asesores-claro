<?php
class GpsCalificaciones_model extends MY_Model {
    
    
    function __construct()
    {
        // Call the Model constructor
        $this->table = 'gps_Calificaciones';
        $this->primary_key = 'idCalificacion';
        $this->return_as = 'object' | 'array';
        $this->protected = array('idCalificacion');
        
        $this->delete_cache_on_save = TRUE;
        $this->timestamps = FALSE;
        parent::__construct();
    }

}
?>
<?php
class Archivo_model extends MY_Model {
    
    
    function __construct()
    {
        // Call the Model constructor
        $this->table = 'Archivos';
        $this->primary_key = 'id';
        $this->return_as = 'object' | 'array';
        $this->protected = array('id');
        
        $this->delete_cache_on_save = TRUE;
        $this->timestamps = FALSE;
        parent::__construct();
    }

}
?>
<?php
class Admins_model extends MY_Model {
    
    
    function __construct()
    {
        // Call the Model constructor
        $this->table = 'Admins';
        $this->primary_key = 'idAdmin';
        $this->return_as = 'object' | 'array';
        $this->protected = array('idAdmin');
        
        $this->delete_cache_on_save = TRUE;
        $this->timestamps = FALSE;
        parent::__construct();
    }

}
?>
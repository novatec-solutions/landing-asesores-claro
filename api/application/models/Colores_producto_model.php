<?php
class Colores_producto_model extends MY_Model {
    
    
    function __construct()
    {
        // Call the Model constructor
        $this->table = 'repos_ProductoColor';
        $this->primary_key = 'productoColorID';
        $this->return_as = 'object' | 'array';
        $this->protected = array('productoColorID');
		
        $this->delete_cache_on_save = TRUE;
        $this->timestamps = FALSE;
        parent::__construct();
    }

}
?>
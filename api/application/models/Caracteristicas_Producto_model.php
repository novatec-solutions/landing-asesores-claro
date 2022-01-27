<?php
class Caracteristicas_Producto_model extends MY_Model {
    
    
    function __construct()
    {
        // Call the Model constructor
        $this->table = 'repos_ProductoCaracteristicas';
        $this->primary_key = 'productoCaracteristicaID';
        $this->return_as = 'object' | 'array';
        $this->protected = array('productoCaracteristicaID');
		
        $this->delete_cache_on_save = TRUE;
        $this->timestamps = FALSE;
        parent::__construct();
    }

}
?>
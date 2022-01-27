<?php
class Producto_inventario_model extends MY_Model {
    
    
    function __construct()
    {
        // Call the Model constructor
        $this->table = 'repos_ProductoInventario';
        $this->primary_key = 'productoInventarioID';
        $this->return_as = 'object' | 'array';
        $this->protected = array('productoInventarioID');
		
        $this->delete_cache_on_save = TRUE;
        $this->timestamps = FALSE;
        parent::__construct();
    }

}
?>
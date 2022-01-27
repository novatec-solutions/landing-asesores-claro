<?php
class Pedidos_model extends MY_Model {
    
    
    function __construct()
    {
        // Call the Model constructor
        $this->table = 'repos_pedidoProducto_v1';
        $this->primary_key = 'pedidoProductoID';
        $this->return_as = 'object' | 'array';
        $this->protected = array('pedidoProductoID');
        
		//$this->has_one['ciudad'] = array('CiudadesWifi_model','idCiudad','idCiudad');
		
        $this->delete_cache_on_save = TRUE;
        $this->timestamps = FALSE;
        parent::__construct();
    }

}
?>
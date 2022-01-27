<?php
class PuntosWifi_model extends MY_Model {
    
    
    function __construct()
    {
        // Call the Model constructor
        $this->table = 'ZonaWifi_Puntos';
        $this->primary_key = 'idPunto';
        $this->return_as = 'object' | 'array';
        $this->protected = array('idPunto');
        
		$this->has_one['categoria'] = array('CategoriasWifi_model','idCategoria','idCategoria');
		$this->has_one['ciudad'] = array('CiudadesWifi_model','idCiudad','idCiudad');
		
        $this->delete_cache_on_save = TRUE;
        $this->timestamps = FALSE;
        parent::__construct();
    }

}
?>
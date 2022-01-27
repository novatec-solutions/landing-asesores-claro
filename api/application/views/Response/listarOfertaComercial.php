<?php
$response["error"]= 1;
$response["response"] = array();
if(isset($ResponseConsultaOferta) && isset($ResponseConsultaOferta["codigo"]) && $ResponseConsultaOferta["codigo"] == "0"){
    if(isset($ResponseConsultaOferta["arregloPaquetes"]) && isset($ResponseConsultaOferta["arregloPaquetes"]["paquete"]) && count($ResponseConsultaOferta["arregloPaquetes"]["paquete"]) > 0 ){
        $response["error"]= 0;
        $response["response"] = $ResponseConsultaOferta["arregloPaquetes"]["paquete"];
    }
}

echo json_encode($response);
?>
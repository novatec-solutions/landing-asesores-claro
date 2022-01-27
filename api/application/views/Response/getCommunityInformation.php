<?php
    $response["resServ"] =  $tnscommunity_info;
    if($tnsacknowledgment["tnsindicator"]=="SUCCESS"){
        
        $comunidades = array();

        $userFull=false;
        if(sizeof($controller->getArray($tnscommunity_info)) > 1){
            foreach ($tnscommunity_info as &$comunidad) {
                if(intval($req["type"]) == intval($comunidad["tnscommunity_type"])){
                    //$miComunidad=$comunidad;
                    array_push($comunidades,$comunidad);
                    $userFull=true;
                }
            }
        }

        
        
        

        if($userFull){            
            if(sizeof($comunidades) == 1){
                $tnscommunity_info=$comunidades[0];
            }else{                
                foreach ($comunidades as &$value) {
                    $members = $controller->getArray($value["tnsmembers"]["tnsmember"]);
                    foreach ($members as &$value2) {
                        if($req["AccountId"] == $value2->tnsmsisdn && $controller->arrayToString($value2->tnsmember_type) == "ADMIN"){
                            $tnscommunity_info = $value;
                        }
                    }
                }
            }
        }

        //$userFull?


        $comunityName = ((intval($req["type"])==1)?"Datos Compartidos":"Familia y Amigos");
        
        if (intval($req["type"])==intval($tnscommunity_info["tnscommunity_type"])) {

            $response["error"]=0;
            $miembros=$controller->getArray($tnscommunity_info["tnsmembers"]["tnsmember"]);

            $response["response"]=$miembros;

            
            $listMiembros=array();
            
            $userAdmin = false;
            foreach ($miembros as $item) {
                $nuevo=array(
                    "msisdn"=>$item->tnsmsisdn,
                    "member_type"=>$controller->arrayToString($item->tnsmember_type),
                    "state"=>$controller->arrayToString($item->tnsstate)
                );

                
                if($req["AccountId"] == $item->tnsmsisdn && $controller->arrayToString($item->tnsmember_type) == "ADMIN"){
                    $userAdmin = true;
                }
                

                array_push($listMiembros,$nuevo);
            }

            //Solo aplica para datos compartidos
            //$userAdmin=intval($req["type"])==2?true:$userAdmin;

            if($userAdmin){
                $fecha=$tnscommunity_info["tnscreation_date"];

                if (isset($tnscommunity_info["tnscreation_date"])) {
                    $fecha = explode("T", $tnscommunity_info["tnscreation_date"]);
                    if (count($fecha)>0) {
                        $fecha=$fecha[0];
                    }
                }

                $total_quota = $controller->arrayToString($tnscommunity_info["tnstotal_quota"]);
                $total_quota = isset($total_quota)?intval($total_quota):0;
                if(isset($req["SO"]) && $req["SO"]=="android"){
                    //$total_quota = $total_quota/1024;
                }

                if(intval($req["type"])==1){
                    $controller->load->library('curl');
                    $data=array("AccountId"=>$req["AccountId"],"community_id"=>$tnscommunity_info["tnscommunity_id"]);
                    $res=$controller->curl->simple_post('https://'.$_SERVER['HTTP_HOST'].'/back/api/index.php/v1/soap/getCommunityConsumption.json', array("data"=>$data));
                    $res=json_decode($res);

                    if(isset($res->error,$res->response) && $res->error == 0){
                        $respConsumo = $res->response;
                    }
                }


                $response["response"]=array(
                    "members"=>$listMiembros,
                    "type"=>$tnscommunity_info["tnscommunity_type"],
                    "id"=>$tnscommunity_info["tnscommunity_id"],
                    "creation_date"=>$fecha,
                    "state"=>$tnscommunity_info["tnsstate"],
                    "members_current"=>$controller->arrayToString($tnscommunity_info["tnscount_members_current"]),
                    "members_allowed"=>$controller->arrayToString($tnscommunity_info["tnscount_members_allowed"]),
                    "total_quota"=>$total_quota,
                    "offerTerms"=>array(
                        "service_id"=>$tnscommunity_info["tnsofferTerms"]["tnsservice_id"],
                        "service_type"=>$tnscommunity_info["tnsofferTerms"]["tnsservice_type"],
                        "name"=>$controller->arrayToString($tnscommunity_info["tnsofferTerms"]["tnsname"]),
                        "description"=>$controller->arrayToString($tnscommunity_info["tnsofferTerms"]["tnsdescription"])
                    )
                );

                
                if(isset($respConsumo)){
                    $totales = array();
                    $response["response"]["consumoComunidad"]=$respConsumo;
                    if(isset($respConsumo->total_quota,$respConsumo->community_consumption)){
                        
                        $BToGB = 1024*1024*1024;

                        $total = $respConsumo->total_quota/$BToGB;
                        $consumido = $respConsumo->community_consumption/$BToGB;
                        $restante = $total-$consumido;
                        

                        $miConsumo = 0;
                        foreach($respConsumo->members as $m){
                            if($req["AccountId"] == $m->msisdn){
                                $miConsumo = $m->member_consumption/$BToGB;
                                //$miConsumo = floatval(substr($m->member_consumption,0,1).".".substr($m->member_consumption,1,strlen($m->member_consumption)-1));
                                //$miConsumo = floatval(substr($m->member_consumption,0,1).".".substr($m->member_consumption,1,strlen($m->member_consumption)-1))
                            }
                        }


                        $consumido = $consumido - $miConsumo;

                        //$totalTxt = str_replace(".",",",number_format($total))." GB";
                        $totalTxt = number_format($total, 2, ',', '')." GB";
                        $consumidoTxt = number_format($consumido, 2, ',', '')." GB";
                        $restanteTxt = number_format($restante, 2, ',', '')." GB";
                        $miConsumoTxt = number_format($miConsumo, 2, ',', '')." GB";
                        


                        $totales = array(
                            "total"=>$total,
                            "totalTxt"=>$totalTxt,
                            "consumoComunidad"=>$consumido,
                            "consumoComunidadTxt"=>$consumidoTxt,
                            "consumoPersonal"=>$miConsumo,
                            "consumoPersonalTxt"=>$miConsumoTxt,
                            "restante"=>$restante,
                            "restanteTxt"=>$restanteTxt
                        );
                    }
                    $response["response"]["totales"] = $totales;
                }else if(intval($req["type"])!=2){
                    $response["error"]=1;
                    $response["response"]="No cuentas con consumo de datos compartidos.";
                }
            }else{
                $response["error"]=1;
                $response["response"]="Actualmente perteneces a una comunidad de ".$comunityName.", si deseas gestionar algún cambio comunícate con la línea administradoras.";
            }
        }else{
            $response["error"]=1;
            $response["response"]="La línea que intentas consultar no pertenece al servicio de ".$comunityName.".";
            //$response["response"]="La línea que intentas consultar no pertenece al servicio de ".((intval($req["type"])==1)?"Datos Compartidos.":"Familia y Amigos.")."XXX:".intval($tnscommunity_info["tnscommunity_type"]);
        }
        
        
    }else{
        $response["error"]=1;

        $resTx=$tnsacknowledgment["tnsmessage"];

        
        $resTx=$tnsacknowledgment["tnsmessage"];
        if(isset($resTx) && $resTx == 'Pending transaction'){
            $resTx = "La transacción se encuentra pendiente";
        }else if(isset($resTx) && $resTx == 'The member not complies with the conditions'){
            $resTx = "Ésta línea no cumple con las condiciones";
        }else if(isset($resTx) && $resTx == 'The member is already associated with another community'){
            $resTx = "Ésta línea ya se encuentra asociada a otra comunidad";
        }else if(isset($resTx) && $resTx == 'Member not found'){
            $resTx = "La línea no se encuentra en esta comunidad";
        }else if(isset($resTx) && $resTx == 'Quota Invalid'){
            $resTx = "Cuota invalida";
        }else if(isset($resTx) && $resTx == 'Community not found'){
            $resTx = "La línea que intentas consultar no pertenece al servicio de ".((intval($req["type"])==1)?"Datos Compartidos.":"Familia y Amigos.");
        }else if(isset($resTx) && $resTx == 'The member not complies with the conditions'){
            $resTx = "La línea no cumple las condiciones necesarias para agregarla a la comunidad";
        }
        $response["response"]=$resTx;
    }

    echo json_encode($response);
?>
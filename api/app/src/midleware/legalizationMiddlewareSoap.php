 <?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
require_once(__DIR__ . '/../libs/nusoap/nusoap.php');

$mmValidateActiveSoap = function  (Request $request, Response $response, $next) {
    if(!filter_var($_ENV['SOAPACTIVE'], FILTER_VALIDATE_BOOLEAN)){
        return $response->withStatus(403);
    }else{  
        $response = $next($request, $response);
        return $response;
    }
};

$mwCustomerLegalization = function (Request $request, Response $response, $next) {
    try {
        $session = $request->getAttribute('ejecutar');
        if(is_null($session) || $session=="legalize"){            
            $wsdl = $_ENV['LG_WSDL'];            
            $client = new nusoap_client($wsdl, true);
            $client->soap_defencoding = 'UTF-8';
            $client->decode_utf8 = false;    
            $operation = "CustomerLegalization";
            $method = "CustomerLegalizationRequest";
            
            $json = json_decode($request->getBody());
            $data = $json->data;

            $minPrepago = (string)$data->prepaidMin;                       
            $flow = (isset($data->flow) ? $data->flow : $_ENV['LG_FLOW_LEGALIZATION'])  ; 
            $greetings = $data->greetings;
            $doctype = $data->docType;
            $numDoc = $data->docNum;
            $birthDate = $data->birthDate;
            $direction = $data->address;
            $codPostal = (isset($data->postalCode) ? (is_null($data->postalCode) ? "1" : ($data->postalCode =="" ? "1" : $data->postalCode)) : "1");
            $apartment = $data->department;
            $city = $data->city;
            $neighborhood = $data->neighborhood;
            $mail = $data->mail;
            $indicativemin = $data->indicativeMin;
            $phone = $data->phone;
            $event = (isset($data->event) ? $data->event : $data->indicativeMin);
            $expeditionDates = $data->expeditionDate;
            $surnameOne = (isset($data->surnameOne) ? $data->surnameOne : "");
            $surnameTwo = (isset($data->surnameTwo) ? $data->surnameTwo : "");
            $nameOne = (isset($data->nameOne) ? $data->nameOne : "");
            $nameTwo = (isset($data->nameTwo) ? $data->nameTwo : "");
            $idTransaccion = (isset($data->idTransaccion) ? $data->idTransaccion : getTransacciontId());
            $channel = $data->channel;
            $municipality = $data->municipality;              
            $lastName = $data->lastName;
            $name = $data->name;      

            if(isset($data->lastName)){
                $lastNames = explode(" ", $lastName);
                if(sizeof($lastNames)>1){
                    $surnameOne = $lastNames[0];
                    $array_num = count($lastNames);
                    for ($i = 1; $i < $array_num; ++$i){
                        $surnameTwo .= $lastNames[$i];
                    }
                }else{
                    $surnameOne = $lastNames[0];
                }
            }
            if (isset($data->name)){
                $names = explode(" ", $name);
                if(sizeof($names)>1){
                    $nameOne = $names[0];
                    $array_num = count($names);
                    for ($i = 1; $i < $array_num; ++$i){
                        $join = '';
                        if($i>1)
                            $join = ' ';                
                        $nameTwo .= $join.$names[$i];
                    }
                }else{
                    $nameOne = $names[0];
                }
            }
            $datosA= array(
                'min'=>$minPrepago,
                'flow'=>$flow,
                'greetings'=>$greetings,
                'doctype'=>$doctype,
                'numDoc'=>$numDoc,
                'birthDate'=>$birthDate,
                'direction'=>$direction,
                'codPostal'=>$codPostal,
                'apartment'=>$apartment,
                'city'=>$city,
                'neighborhood'=>$neighborhood,
                'mail'=>$mail,
                'indicativemin'=>$indicativemin,
                'phone'=>$phone,
                'event'=>$event,
                'expeditionDates'=>$expeditionDates,
                'surnameOne'=>$surnameOne,
                'surnameTwo'=>$surnameTwo,
                'nameOne'=>$nameOne,
                'nameTwo'=>$nameTwo,
                'idTransaccion'=>$idTransaccion,
                'channel'=>$channel,
                'municipality'=>$municipality
            );

            $datos = null;
            // valida el flag de respuestas dummy
            if(!filter_var($_ENV['ACTIVEDUMMY'], FILTER_VALIDATE_BOOLEAN)){
                //invocacion al servicio soap
                $datos = $client->call($operation, array($method=>$datosA));
                if(!$datos){
                    $errorsend = $client->getError();
                    throw new Exception($errorsend);
                }
            }
            else{
                /////datos dumy
                $datos["code"] = "0";
                $datos["description"] = "Exitoso legalizacion - Respuesta Dummy";        
                /////datos dumy   
            }

            //registro del request y response
            if(validateLogInfo())
            $this->get('logger')->info("Info",['File'=> __FILE__, 'Uri' => (string) $request->getUri(), 
            'Method' => $request->getMethod(), 
            'Wsdl' => $wsdl, 
            'SoapOperation' => $operation, 
            'SoapMethod' => $method,
            'RequestSoap' => $datosA,
            'RequestJson' => $request->getParsedBody(),             
            'ResponseJson' => $datos]);         
            
            $respuesta["response"] = $datos;                
            $response = $response->withJson($respuesta)->withHeader('Content-type', 'application/json');
        }
        $response = $next($request, $response);
        $jsonResponse = json_decode($response->getBody());
        return $response->withJson($jsonResponse)->withHeader('Content-type', 'application/json');
    }

    catch (Throwable $e) {
        if(validateLog())
            $this->get('logger')->error("Error",['File'=> __FILE__, 'Uri' => (string) $request->getUri(), 'Method' => $request->getMethod(),'Exception' => utf8_encode(exceptionLog($e->getMessage())),'Request' => $request->getParsedBody()]);
        $respuesta["error"] = 1;
        $respuesta["response"] = "Ha ocurrido un error en la llamada al servicio, reintente mas tarde";
        return $response->withJson($respuesta, 500)->withHeader('Content-type', 'application/json');
    }
};

$mwCustomerUpgrade = function (Request $request, Response $response, $next) {    
    try {

        $session = $request->getAttribute('ejecutar');
        if(is_null($session) || $session=="upgrate"){

        $wsdl = $_ENV['LG_WSDL'];
        $client = new nusoap_client($wsdl, true);
        $client->soap_defencoding = 'UTF-8';
        $client->decode_utf8 = false;    
        $operation = "CustomerUpgrade";
        $method = "CustomerUpgradeRequest";
        $json = json_decode($request->getBody());
        $data = $json->data;

        $minPrepago = (string)$data->prepaidMin;
        $flow = (isset($data->flow) ? $data->flow : $request->getAttribute('flow'));
        $greetings = $data->greetings;
        $names = $data->name;
        $surnames = $data->lastName;
        $doctype = $data->docType;
        $numDoc = $data->docNum;
        $birthDate = $data->birthDate;
        $direction = $data->address;
        $codPostal = (isset($data->postalCode) ? ($data->postalCode != "" ? $data->postalCode : "1") : "1");
        $depto = $data->department;
        $city = $data->city;
        $neighborhood = $data->neighborhood;
        $mail = $data->mail;
        $indicative = $data->indicativeMin;
        $phone = $data->phone;
        $channel = $data->channel;          

        $datosA= array(
            'msisdn'=>$minPrepago,
            'flow'=>$flow,
            'greetings'=>$greetings,
            'names'=>$names,
            'surnames'=>$surnames,
            'doctype'=>$doctype,
            'numDoc'=>$numDoc,
            'birthDate'=>$birthDate,
            'direction'=>$direction,
            'codPostal'=>$codPostal,
            'depto'=>$depto,
            'city'=>$city,
            'neighborhood'=>$neighborhood,
            'mail'=>$mail,
            'indicative'=>$indicative,
            'phone'=>$phone,
            'channel'=>$channel
        );
        $datos = null;
        // valida el flag de respuestas dummy
        if(!filter_var($_ENV['ACTIVEDUMMY'], FILTER_VALIDATE_BOOLEAN)){
            //invocacion al servicio soap
            $datos = $client->call($operation, array($method=>$datosA));
            if(!$datos){
                $errorsend = $client->getError();
                throw new Exception($errorsend);
            }
        }
        else{
            /////datos dumy
            $datos["code"] = "0";
            $datos["description"] = "Exitoso upgrade - Respuesta Dummy";        
            /////datos dumy   
        }
        //registro del request y response
        if(validateLogInfo())
        $this->get('logger')->info("Info",['File'=> __FILE__, 'Uri' => (string) $request->getUri(), 
        'Method' => $request->getMethod(), 
        'Wsdl' => $wsdl, 
        'SoapOperation' => $operation, 
        'SoapMethod' => $method,
        'RequestSoap' => $datosA,
        'RequestJson' => $request->getParsedBody(),             
        'ResponseJson' => $datos]);           

        $respuesta["response"] = $datos;    
        $response = $response->withJson($respuesta)->withHeader('Content-type', 'application/json');
    }
        $response = $next($request, $response);
        $jsonResponse = json_decode($response->getBody(), true);
        return $response->withJson($jsonResponse)->withHeader('Content-type', 'application/json');
    } catch (Throwable $e) {
        if(validateLog())
            $this->get('logger')->error("Error",['File'=> __FILE__, 'Uri' => (string) $request->getUri(), 'Method' => $request->getMethod(),'Exception' => utf8_encode(exceptionLog($e->getMessage())),'Request' => $request->getParsedBody()]);
        $respuesta["error"] = 1;
        $respuesta["response"] = "Ha ocurrido un error en la llamada al servicio, reintente mas tarde";
        return $response->withJson($respuesta, 500)->withHeader('Content-type', 'application/json');
    }
};

$mwValidateLegalization =  function (Request $request, Response $response, $next) {    
    try {

        $wsdl = $_ENV['LG_WSDL'];
        $client = new nusoap_client($wsdl, true);
        $client->soap_defencoding = 'UTF-8';
        $client->decode_utf8 = false;    
        $operation = "ValidateLegalizationData";
        $method = "ValidateLegalizationDataRequest";

        $json = json_decode($request->getBody());
        $data = $json->data;
        $lastName=$data->lastName;
        if(isset($data->lastName)){
            $lastNames = explode(" ", $lastName);
            if(sizeof($lastNames)>1){
                $surnameOne = $lastNames[0];
                $array_num = count($lastNames);
                for ($i = 1; $i < $array_num; ++$i){
                    $surnameTwo .= $lastNames[$i];
                }
            }else{
                $surnameOne = $lastNames[0];
            }
        }

        $minPrepago = $data->prepaidMin; 
        $typeDoc= $data->docType;
        $numDoc= $data->docNum;
        $expeditionDate= $data->expeditionDate;
        $lastName= $surnameOne;
        $idTransaccion= getTransacciontId();
        $channel= $data->channel;

       
        $datosA= array(
            'minPrepago'=> $minPrepago,
            'typeDoc'=>$typeDoc,
            'numDoc'=>$numDoc,
            'expeditionDate'=>$expeditionDate,
            'lastName'=>$lastName,
            'idTransaccion'=>$idTransaccion,
            'channel'=>$channel
        );
        
        $datos = null;
        // valida el flag de respuestas dummy
        //if(!filter_var($_ENV['ACTIVEDUMMY'], FILTER_VALIDATE_BOOLEAN)){
            //invocacion al servicio soap
            $datos = $client->call($operation, array($method=>$datosA));
            if(!$datos){
                $errorsend = $client->getError();
                throw new Exception($errorsend);
            }
        /*}
        else{
            /////datos dumy
            $datos["code"] = "0";
            $datos["description"] = "Exitoso Dummy";
            $datos["names"] = "BRIGITTE";
            $datos["surname"] = "RUEDA GALVIS";
            $datos["direction"] = "CL 152D 102B 17 TO1AP 504 EL PINAR";
            $datos["city"] = "BOGOTA";
            $datos["department"] = "CUNDINAMARCA";
            $datos["minL"] = "1";
            $datos["commpnum"] = "1";
            $datos["susp"] = "0";
            $datos["changeSimcard"] = "0";
            $datos["plcode"] = "0";
            $datos["pldesc"] = "OK";
            $datos["message"] = "OK";
             ///datos dumy
        }    */    
        //registro del request y response
        if(validateLogInfo())
        $this->get('logger')->info("Info",['File'=> __FILE__, 'Uri' => (string) $request->getUri(), 
        'Method' => $request->getMethod(), 
        'Wsdl' => $wsdl, 
        'SoapOperation' => $operation, 
        'SoapMethod' => $method,
        'RequestSoap' => $datosA,
        'RequestJson' => $request->getParsedBody(),             
        'ResponseJson' => $datos]);      

        $respuesta["response"] = $datos;    
        $response = $response->withJson($respuesta)->withHeader('Content-type', 'application/json');
        $response = $next($request, $response);
        $jsonResponse = json_decode($response->getBody());
        return $response->withJson($jsonResponse)->withHeader('Content-type', 'application/json');

    } catch (Throwable $e) {
        if(validateLog())
            $this->get('logger')->error("Error",['File'=> __FILE__, 'Uri' => (string) $request->getUri(), 'Method' => $request->getMethod(),'Exception' => utf8_encode(exceptionLog($e->getMessage())),'Request' => $request->getParsedBody()]);
        $respuesta["error"] = 1;
        $respuesta["response"] = "Ha ocurrido un error en la llamada al servicio, reintente mas tarde";
        return $response->withJson($respuesta, 500)->withHeader('Content-type', 'application/json');
    }
};

$mwcustomerTickler = function (Request $request, Response $response, $next){   
    try {

        $wsdl = $_ENV['LG_WSDL'];
        $client = new nusoap_client($wsdl, true);
        $client->soap_defencoding = 'UTF-8';
        $client->decode_utf8 = false;    
        $operation = "CustomerTickler";
        $method = "CustomerTicklerRequest";
        $json = json_decode($request->getBody());
        $data = $json->data;
        
        $prepaidMin = $data->prepaidMin; 
        $customerId = "";
        $codeTicklet = $_ENV['LG_CODETICKLET'];
        $shortDescription = $_ENV['LG_SHORTDESCRIPTION'];
        $channel= $data->channel;

        $datosA= array(
            'min'=> $prepaidMin,
            'customerId'=>$customerId,
            'codeTickler'=>$codeTicklet,
            'shortDescription'=>$shortDescription,
            'channel'=>$channel
        );
        $datos = null;
        //invocacion al servicio soap
        $datos = $client->call($operation, array($method=>$datosA));
        if(!$datos){
            $errorsend = $client->getError();
            throw new Exception($errorsend);
        }
        // respuesta dummy
        //$datos["codeError"] = "-83";
        //$datos["description"] = "No se encontro datos relacionados con la consulta realizada-Dummy";

        if(validateLogInfo())
        //registro del request y response
        $this->get('logger')->info("Info",['File'=> __FILE__, 'Uri' => (string) $request->getUri(), 
        'Method' => $request->getMethod(), 
        'Wsdl' => $wsdl, 
        'SoapOperation' => $operation, 
        'SoapMethod' => $method,
        'RequestSoap' => $datosA,
        'RequestJson' => $request->getParsedBody(),             
        'ResponseJson' => $datos]);   
        $respuesta["response"] = $datos;    

        $response = $response->withJson($respuesta)->withHeader('Content-type', 'application/json');
        $response = $next($request, $response);
        $jsonResponse = json_decode($response->getBody());
        return $response->withJson($jsonResponse)->withHeader('Content-type', 'application/json');            
    } catch (Throwable $e) {
        if(validateLog())
            $this->get('logger')->error("Error",['File'=> __FILE__, 'Uri' => (string) $request->getUri(), 'Method' => $request->getMethod(),'Exception' => utf8_encode(exceptionLog($e->getMessage())),'Request' => $request->getParsedBody(), 'Response' => $datos]);
        $respuesta["error"] = 1;
        $respuesta["response"] = "Ha ocurrido un error en la llamada al servicio, reintente mas tarde";
        return $response;//->withJson($respuesta, 500)->withHeader('Content-type', 'application/json');
    }    
};


$mwGeneratePin =  function (Request $request, Response $response, $next) {
    try {

        // informacion del WSDL, debe ser parametrizada desde base de datos
        $wsdl = $_ENV['PG_WSDL'];
        $operation = "GeneratePin";
        $method = "generatePinRequest";
        
        // instanciacion del cliente soap
        $client = new nusoap_client($wsdl, true);
        $client->soap_defencoding = 'UTF-8';
        $client->decode_utf8 = false;    
        
        // datos para el body                        
        $json = json_decode($request->getBody());
        $data = $json->data;
        $clienteId = $data->docNum; 
        $vchDatoMedioEnvio= $data->prepaidMin;
        $channel= $data->channel;

        //definicion de namespace
        $namespaces = array('v1'=> "http://www.amx.com/CO/Schema/ClaroHeaders/v1",
        'v3'=>"http://www.amx.com/Schema/Operation/GeneratePin/V3.0");

        // datos para el encabezado
        $systemHeader=$_ENV['PG_SYSTEMHEADER'];
        $header = '<v1:headerRequest><v1:system>' . $systemHeader . '</v1:system><v1:requestDate>' . date("Y-m-d\TH:i:s") . '</v1:requestDate></v1:headerRequest>';
        // datos para el body deben ser parametrizados
        $nombrePropiedad1 =$_ENV['PG_PROP1_NAME'];
        $valorPropiedad1=$_ENV['PG_PROP1_VALUE'];
        $nombrePropiedad2 =$_ENV['PG_PROP2_NAME'];
        $valorPropiedad2=$_ENV['PG_PROP2_VALUE'];
        $medioEnvioId = $_ENV['PG_IDSENDMEDIUM'];
        $operacionId=$_ENV['PG_IDOPERTAION'];
        $sistemaId=$_ENV['PG_IDSISTEM'];
        $vchUsuario=$_ENV['PG_VCHUSER'];

        //armado del body en xml
        $soapBodyRequest = '<v3:generatePinRequest><v3:cliente><v3:clienteId>'.$clienteId.'</v3:clienteId><v3:listadoPropiedades><v3:propiedad><v3:nombre>'.$nombrePropiedad1.'</v3:nombre><v3:valor>'.$valorPropiedad1.'</v3:valor></v3:propiedad><v3:propiedad><v3:nombre>'.$nombrePropiedad2.'</v3:nombre><v3:valor>'.$valorPropiedad2.'</v3:valor></v3:propiedad></v3:listadoPropiedades><v3:medioEnvioId>'.$medioEnvioId.'</v3:medioEnvioId><v3:operacionId>'.$operacionId.'</v3:operacionId><v3:sistemaId>'.$sistemaId.'</v3:sistemaId><v3:vchDatoMedioEnvio>'.$vchDatoMedioEnvio.'</v3:vchDatoMedioEnvio><v3:vchUsuario>'.$vchUsuario.'</v3:vchUsuario></v3:cliente></v3:generatePinRequest>';
        //serializacion del request en xml
        $soapMsg=$client->serializeEnvelope($soapBodyRequest, $header,$namespaces);
        // respuestas datos dummy
        if(!filter_var($_ENV['ACTIVEDUMMYOTP'], FILTER_VALIDATE_BOOLEAN)){
            // consumo del soap
            $datos = $client->send($soapMsg,$wsdl);
            if(!$datos){
                $errorsend = $client->getError();
                throw new Exception($errorsend);
            }         
        }
        else{     
            /////datos dumy
            $datos["esValido"] = "true";
            $datos["mensajeResult"] = "Pin Generado satisfactoriamente - Dummy";            
            /////datos dumy   
        }
        if(validateLogInfo())
        //registro del request y response
        $this->get('logger')->info("Info",['File'=> __FILE__, 'Uri' => (string) $request->getUri(), 
        'Method' => $request->getMethod(), 
        'Wsdl' => $wsdl, 
        'SoapOperation' => $operation, 
        'SoapMethod' => $method,
        'RequestSoap'=> $soapMsg,
        'RequestJson' => $request->getParsedBody(), 
        'ResponseJson' => $datos]);       
        $respuesta["response"] = $datos;         

        $response = $response->withJson($respuesta)->withHeader('Content-type', 'application/json');
        $response = $next($request, $response);
        $jsonResponse = json_decode($response->getBody());
        return $response->withJson($jsonResponse)->withHeader('Content-type', 'application/json');  

    } catch (Throwable $e) {
        if(validateLog())
            $this->get('logger')->error("Error",['File'=> __FILE__, 'Uri' => (string) $request->getUri(), 'Method' => $request->getMethod(),'Exception' => utf8_encode(exceptionLog($e->getMessage())),'Request' => $request->getParsedBody()]);
        $respuesta["error"] = 1;
        $respuesta["response"] = "Ha ocurrido un error en la llamada al servicio, reintente mas tarde";
        return $response->withJson($respuesta, 500)->withHeader('Content-type', 'application/json');
    }
};

$mwValidatePin =  function (Request $request, Response $response, $next) {

    try {
            // informacion del WSDL, debe ser parametrizada desde base de datos
        $wsdl = $_ENV['PG_WSDL'];
        $operation = "ValidatePin";
        $method = "validatePinRequest";
        
        // instanciacion del cliente soap
        $client = new nusoap_client($wsdl, true);
        $client->soap_defencoding = 'UTF-8';
        $client->decode_utf8 = false;    
        
        // datos para el body                        
        $json = json_decode($request->getBody());
        $data = $json->data;
        $clienteId = $data->docNum;             
        $pin = $data->pin; 
        $channel= $data->channel;

        //definicion de namespace
        $namespaces = array('v1'=> "http://www.amx.com/CO/Schema/ClaroHeaders/v1",
        'v3'=>"http://www.amx.com/Schema/Operation/ValidatePin/V3.0");

        // datos para el encabezado
        $systemHeader=$_ENV['PG_SYSTEMHEADER'];
        $header = '<v1:headerRequest><v1:system>' . $systemHeader . '</v1:system><v1:requestDate>' . date("Y-m-d\TH:i:s") . '</v1:requestDate></v1:headerRequest>';
        // datos para el body deben ser parametrizados
        $nombrePropiedad1 =$_ENV['PG_PROP1_NAME'];
        $valorPropiedad1=$_ENV['PG_PROP1_VALUE'];
        $nombrePropiedad2 =$_ENV['PG_PROP2_NAME'];
        $valorPropiedad2=$_ENV['PG_PROP2_VALUE'];
        $operacionId=$_ENV['PG_IDOPERTAION'];
        $sistemaId=$_ENV['PG_IDSISTEM'];
        $vchUsuario=$_ENV['PG_VCHUSER'];
        //armado del body en xml
        $soapBodyRequest = '<v3:validatePinRequest><v3:clienteValidarPin><v3:clienteId>'.$clienteId.'</v3:clienteId><v3:listadoPropiedades><v3:propiedad><v3:nombre>'.$nombrePropiedad1.'</v3:nombre><v3:valor>'.$valorPropiedad1.'</v3:valor></v3:propiedad><v3:propiedad><v3:nombre>'.$nombrePropiedad2.'</v3:nombre><v3:valor>'.$valorPropiedad2.'</v3:valor></v3:propiedad></v3:listadoPropiedades><v3:operacionId>'.$operacionId.'</v3:operacionId><v3:pin>'.$pin.'</v3:pin><v3:sistemaId>'.$sistemaId.'</v3:sistemaId><v3:vchUsuario>'.$vchUsuario.'</v3:vchUsuario></v3:clienteValidarPin></v3:validatePinRequest>';
        //serializacion del request en xml
        $soapMsg=$client->serializeEnvelope($soapBodyRequest, $header,$namespaces);
        // respuestas datos dummy
        if(!filter_var($_ENV['ACTIVEDUMMYOTP'], FILTER_VALIDATE_BOOLEAN)){
            // consumo del soap
            $datos = $client->send($soapMsg,$wsdl);
            if(!$datos){
                $errorsend = $client->getError();
                throw new Exception($errorsend);
            }             
        }
        else{        
            /////datos dumy
            $datos["esValido"] = "true";
            $datos["mensajeResult"] = "PIN validado satisfactoriamente - Dummy";
            /////datos dumy       
        }
        if(validateLogInfo())
        //registro del request y response        
        $this->get('logger')->info("Info",['File'=> __FILE__, 'Uri' => (string) $request->getUri(), 
        'Method' => $request->getMethod(), 
        'Wsdl' => $wsdl, 
        'SoapOperation' => $operation, 
        'SoapMethod' => $method,
        'RequestSoap'=> $soapMsg,
        'RequestJson' => $request->getParsedBody(), 
        'ResponseJson' => $datos]);        

        $respuesta["response"] = $datos;
        $response = $response->withJson($respuesta)->withHeader('Content-type', 'application/json');
        $response = $next($request, $response);
        $jsonResponse = json_decode($response->getBody());
        return $response->withJson($jsonResponse)->withHeader('Content-type', 'application/json');

    } catch (Throwable $e) {
        if(validateLog())
        $this->get('logger')->error("Error",['File'=> __FILE__, 'Uri' => (string) $request->getUri(), 'Method' => $request->getMethod(),'Exception' => utf8_encode(exceptionLog($e->getMessage())),'Request' => $request->getParsedBody()]);
        $respuesta["error"] = 1;
        $respuesta["response"] = "Ha ocurrido un error en la llamada al servicio, reintente mas tarde";
        $response = $response->withJson($respuesta)->withHeader('Content-type', 'application/json');
        $response = $next($request, $response);
        $jsonResponse = json_decode($response->getBody());
        return $response->withJson($jsonResponse, 500)->withHeader('Content-type', 'application/json');
    }
};

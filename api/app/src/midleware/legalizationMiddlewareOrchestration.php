 <?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
require_once(__DIR__ . '/../libs/nusoap/nusoap.php');

$mvOrqvalidationLegalizationPin= function (Request $request, Response $response, $next) {
    try {
        $datos = json_decode($response->getBody(),true);   
        // valida la respuesta del llamado al servicio mwcustomerTickler
        if(multi_array_key_exists('codeError', $datos))
        // valida que el campo codeError exista 
        {
            $respuesta["error"] = 1;
            $respuesta["response"] = "Se encontro tickler, linea legalizada";   
            if(multi_array_keyvalue_exists('codeError', $datos,"-83")) {
                // error -83 indica que no tiene ticklet de legalizacion y que puede continuar con el proceso de legalizxacion
                //llamado al middleware para mwGeneratePin
                $response = $next($request, $response);
                $jsonResponse = json_decode($response->getBody(), true);
                if($jsonResponse!=null){
                    // validar respuesta del middleware mwGeneratePin
                    if (multi_array_key_exists('esValido', $jsonResponse)) {
                        if (multi_array_keyvalue_exists('esValido', $jsonResponse,"true")){
                            $respuesta["error"] = 0;
                            $respuesta["response"] = multi_array_key_exists_value('mensajeResult', $jsonResponse);
                        }
                        else 
                        {                                
                            $respuesta["error"] = 1;
                            $respuesta["response"] = multi_array_key_exists_value('mensajeResult', $jsonResponse);
                            if(multi_array_key_exists('listExceptionResult', $jsonResponse)){
                                $respuesta["response"] = $respuesta["response"]." - ".multi_array_key_exists_value('descripcion', $jsonResponse);
                            }        
                            $respuesta["response"] = $respuesta["response"].' - GeneratePin';
                        }            
                    }
                    elseif(multi_array_key_exists('faultstring',$jsonResponse)){      
                        $respuesta["error"] = 1;   
                        $respuesta["response"] = multi_array_key_exists_value('faultstring', $jsonResponse).' - '.multi_array_key_exists_value('categoryDescription', $jsonResponse).' - GeneratePin';
                    }
                    else{
                        $respuesta["error"] = 1;
                        $respuesta["response"] = "El servicio de generacion de pin no esta disponible.";            
                    }
                }
                else
                {
                    $respuesta["error"] = 1;
                    $respuesta["response"] = "Error al consumir el servicio de generacion de pin"; 
                }
            }                 
        }    
        // si no existe el campo codeError existe un error en el servicio
        else{
            $respuesta["error"] = 1;
            $respuesta["response"] = "El servicio validacion de ticklet no esta disponible.";
        };
        return $response->withJson($respuesta)->withHeader('Content-type', 'application/json');        
    } catch (Throwable $e) {
        if(validateLog())
            $this->get('logger')->error("",['File'=> __FILE__, 'Uri' => (string) $request->getUri(), 'Method' => $request->getMethod(),'Exception' => exceptionLog($e->getMessage()),'Request' => $request->getParsedBody(), 'Response' => $datos]);
        $respuesta["error"] = 1;
        $respuesta["response"] = "Ha ocurrido un error en la orquestacion de la operacion validationLegalizationPin";
        return $response->withJson($respuesta, 500)->withHeader('Content-type', 'application/json');            
    };
};


$mvOrqvalidatePinLegalization= function (Request $request, Response $response, $next) {
    try {
        $datos = json_decode($response->getBody(),true);   
        // valida la respuesta del llamado al servicio mwValidatePin
        $respuesta["error"] = null;
        $respuesta["isPinValid"]=false;
        $respuesta["response"] = null;
        $respuesta["data"] = null;        
        
        if(multi_array_key_exists('esValido', $datos))
        {
            if(multi_array_keyvalue_exists('esValido', $datos,"true")) {
                $respuesta["error"] = 0;
                $respuesta["isPinValid"]=filter_var(multi_array_key_exists_value('esValido', $datos),FILTER_VALIDATE_BOOLEAN);
                $respuesta["response"] = (int)multi_array_key_exists_value('mensajeResult', $datos);
                $respuesta["data"] = null;
                $response = $next($request, $response);
                // se valida la respuesta del llamado a ValidateLegalization
                $jsonResponse = json_decode($response->getBody(),true);
                if($jsonResponse!=null){            
                    //validar que exista el campo code.
                    if (multi_array_key_exists('code', $jsonResponse)) {         
                        //si code es difirente a cero genera error
                        if(multi_array_key_exists_value('code', $jsonResponse) == "0"){
                            // si plcode es cero es exitoso
                            if(multi_array_key_exists_value('plcode', $jsonResponse) == "0"){
                                // se valida que la linea no este suspendida
                                if(multi_array_key_exists_value('susp', $jsonResponse) == "0" ){
                                    // se valida que la linea no haya tenido cambio de sim
                                    if(multi_array_key_exists_value('changeSimcard', $jsonResponse) == "0" ){
                                        // retorna los valores del servicio
                                        $datosRequest = json_decode($request->getBody(),true);   
                                        $data = null;
                                        $data["names"] = multi_array_key_exists_value('names', $jsonResponse);
                                        $data["surname"] = multi_array_key_exists_value('surname', $jsonResponse);
                                        $data["docNum"] = multi_array_key_exists_value('docNum', $datosRequest);
                                        $data["docuemntType"] = multi_array_key_exists_value('docType', $datosRequest);
                                        $respuesta["data"] = $data;    
                                    }
                                    else{
                                        $respuesta["error"] = 1;
                                        $respuesta["response"] = "Linea con cambio de sim reciente";         
                                    }                                    
                                }
                                else{
                                    $respuesta["error"] = 1;
                                    $respuesta["response"] = "Linea suspendida";         
                                }      
                            }
                            else{
                                //validacion de errores plcode
                                $plcodeerrors = array("-1","-2","-3","-4");
                                if(in_array(multi_array_key_exists_value('plcode', $jsonResponse), $plcodeerrors)){ 
                                    $respuesta["error"] = 1;
                                    $respuesta["response"] = "Error en la Validación de la linea: ".multi_array_key_exists_value('pldesc', $jsonResponse);
                                }
                                else{
                                    $respuesta["error"] = 1;
                                    $respuesta["response"] = "Error desconocido en la Validación de la linea, respuesta plcode invalido";
                                }
                            }
                        }
                        else{
                            $respuesta["error"] = 1;
                            $respuesta["response"] = multi_array_key_exists_value('description', $jsonResponse); 
                        }
                    }
                    else{
                        $respuesta["error"] = 1;
                        $respuesta["response"] = "Error del servicio ValidateLegalization"; 
                    }
                }
                else{
                    $respuesta["error"] = 1;
                    $respuesta["response"] = "Error al consumir el servicio de ValidateLegalization"; 
                }          
            }      
            else{
                $respuesta["error"] = 1;
                $respuesta["isPinValid"]=multi_array_key_exists_value('esValido', $datos); 
                $respuesta["response"] = multi_array_key_exists_value('mensajeResult', $datos);
                if(multi_array_key_exists('listExceptionResult', $datos)){
                    $respuesta["response"] = $respuesta["response"]." - ".multi_array_key_exists_value('descripcion', $datos);
                }
            }           
        }                 
        // si no existe el campo codeError existe se valida error propio del servicio        
        elseif(multi_array_key_exists('faultstring', $datos)){
            $respuesta["error"] = 1;   
            $respuesta["isPinValid"]=false;
            $respuesta["response"] = multi_array_key_exists_value('faultstring', $datos).' - '.multi_array_key_exists_value('categoryDescription', $datos);
        }                
        //error no manejado por el servicio o indisponibilidad del servicio
        else{        
            $respuesta["error"] = 1;
            $respuesta["isPinValid"]=false;
            $respuesta["response"] = "El servicio de validacion de pin no esta disponible.";
        };
        return $response->withJson($respuesta)->withHeader('Content-type', 'application/json');
    } catch (Throwable $e) {
        if(validateLog())
            $this->get('logger')->error("Error",['File'=> __FILE__, 'Uri' => (string) $request->getUri(), 'Method' => $request->getMethod(),'Exception' => utf8_encode(exceptionLog($e->getMessage())),'Request' => $request->getParsedBody(), 'Response' => $datos]);
        $respuesta["error"] = 1;
        $respuesta["isPinValid"]=false;
        $respuesta["response"] = "Ha ocurrido un error en la llamada al servicio, operacion validatePinLegalization ";
        $respuesta["data"] = null;
        return $response->withJson($respuesta, 500)->withHeader('Content-type', 'application/json');            
    };
};


$mvOrqlegalizeMin= function (Request $request, Response $response, $next) {
    try {
        $datos = json_decode($response->getBody(),true);   
        $respuesta["error"] = null;
        $respuesta["response"] = null;

        // se valida la respuesta del llamado a ValidateLegalization
        $jsonResponse = json_decode($response->getBody(),true);
        if($jsonResponse!=null){            
            //validar que exista el campo code.
            if (multi_array_key_exists('code', $jsonResponse)) {         
                //si code es difirente a cero genera error
                if(multi_array_key_exists_value('code', $jsonResponse) == "0"){
                    // si plcode es cero es exitoso
                    if(multi_array_key_exists_value('plcode', $jsonResponse) == "0" ){
                        // se valida que la linea no este suspendida
                        if(multi_array_key_exists_value('susp', $jsonResponse) == "0" ){
                            // se valida que la linea no haya tenido cambio de sim
                            if(multi_array_key_exists_value('changeSimcard', $jsonResponse) == "0" ){
                                //se valida si la linea esta legalizada, 0 no esta legalizada se debe legalizar
                                if(multi_array_key_exists_value('minL', $jsonResponse) == "0" ){        
                                    $request = $request->withAttribute('ejecutar', "legalize");                                                                                                           
                                }
                                //se valida si la linea esta legalizada, 1 esta legalizada se debe actualizar
                                elseif(multi_array_key_exists_value('minL', $jsonResponse) == "1" ){
                                    // asigna el valor del flujo, si pertenece al mismo numero de documento se envia 2
                                    $flow = (multi_array_key_exists_value('commpnum', $jsonResponse)=="1" ? $_ENV['LG_FLOW_UPD_COMMPNUM'] : $_ENV['LG_FLOW_UPD_NOCOMMPNUM']);
                                    $request = $request->withAttribute('ejecutar', "upgrate");
                                    $request = $request->withAttribute('flow', $flow);                                        
                                }
                                $response = $next($request, $response);
                                // retorna los valores del servicio
                                $datosResponse = json_decode($response->getBody(),true);
                                // se valida la respuesta 
                                $flujo=$request->getAttribute('ejecutar');
                                if ($flujo == "legalize") {
                                    if (multi_array_key_exists('code', $jsonResponse)) {
                                        $respuesta["error"] = (int)multi_array_key_exists_value('code', $jsonResponse);
                                        $respuesta["response"] = multi_array_key_exists_value('description', $jsonResponse);
                                    }
                                    else{
                                        $respuesta["error"] = 1;
                                        $respuesta["response"] = "Error del servicio CustomerLegalization";
                                    }                               
                                }
                                elseif ($flujo == "upgrate") {
                                    if (multi_array_key_exists('code', $jsonResponse)) {
                                        $respuesta["error"] = (int)multi_array_key_exists_value('code', $jsonResponse);
                                        $respuesta["response"] = multi_array_key_exists_value('description', $jsonResponse);
                                    }
                                    else{
                                        $respuesta["error"] = 1;
                                        $respuesta["response"] = "Error del servicio CustomerUpgrade";
                                    } 
                                }
                            }
                            else{
                                $respuesta["error"] = 1;
                                $respuesta["response"] = "Linea con cambio de sim reciente";         
                            }                                    
                        }
                        else{
                            $respuesta["error"] = 1;
                            $respuesta["response"] = "Linea suspendida";         
                        }      
                    }
                    else{
                        //validacion de errores plcode
                        $plcodeerrors = array("-1","-2","-3","-4");
                        if(in_array(multi_array_key_exists_value('plcode', $jsonResponse), $plcodeerrors)){ 
                            $respuesta["error"] = 1;
                            $respuesta["response"] = "Error en la Validación de la linea: ".multi_array_key_exists_value('pldesc', $jsonResponse);
                        }
                        else{
                            $respuesta["error"] = 1;
                            $respuesta["response"] = "Error desconocido en la Validación de la linea, respuesta plcode invalido";
                        }
                    }
                }
                else{
                    $respuesta["error"] = 1;
                    $respuesta["response"] = multi_array_key_exists_value('description', $jsonResponse); 
                }
            }
            else{
                $respuesta["error"] = 1;
                $respuesta["response"] = "Error del servicio ValidateLegalization"; 
            }
        }
        else{
            $respuesta["error"] = 1;
            $respuesta["response"] = "Error al consumir el servicio de ValidateLegalization"; 
        }       
        return $response->withJson($respuesta)->withHeader('Content-type', 'application/json');      
    } catch (Throwable $e) {
        if(validateLog())
            $this->get('logger')->error("Error",['File'=> __FILE__, 'Uri' => (string) $request->getUri(), 'Method' => $request->getMethod(),'Exception' => utf8_encode(exceptionLog($e->getMessage())),'Request' => $request->getParsedBody(), 'Response' => $datos]);
        $respuesta["error"] = 1;
        $respuesta["response"] = "Ha ocurrido un error en la actualizacion o legalizacion de linea";        
        return $response->withJson($respuesta, 500)->withHeader('Content-type', 'application/json');            
    };
};
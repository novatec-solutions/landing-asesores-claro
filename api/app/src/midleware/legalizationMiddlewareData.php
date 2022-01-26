<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use App\Entity\{City2, Departament, Various, Populated};

/*
valida que este disponible el uso en produccion, si la valriable PRODUCTION es false
*/
$mmValidateProduction = function  (Request $request, Response $response, $next) {    
    if(!filter_var($_ENV['PRODUCTION'], FILTER_VALIDATE_BOOLEAN)){
        $response = $next($request, $response);
        return $response;
    }else{  
        return $response->withStatus(403);
    }
};
/*
Realiza la validacion del token
*/

$mwTokenValidation = function(Request $request, Response $response, $next){
    $validate_token = validateMessage($request->getHeaders(), json_decode($request->getBody()));
    if($validate_token != "Ok"){  
        $respuesta["error"] = 1;
        $respuesta["response"] = $validate_token;
        return $response->withJson($respuesta,401)->withHeader('Content-type', 'application/json');         
    }
    else{
        $response = $next($request, $response);        
    }
    return $response;
};

/*
Retorna la lista de departamentos
body del servicio
{
    "data": {
        "token": "Akw@pNr{A5?aV.zVwvTD%YX#:Nh2Lh"
    }
}
*/

$mwDepto = function (Request $request, Response $response, $next) {
    $department = null;
    try {
        $query = $this->get('em')->createQuery("SELECT d FROM App\Entity\Department d 
        WHERE d.status='A'");
        $department = $query->getResult();
    } catch (Throwable $e) {
        if(validateLog())
        $this->get('logger')->error("",['File'=> __FILE__, 'Uri' => (string) $request->getUri(),'Method' => $request->getMethod(),'Exception' => exceptionLog($e->getMessage()),'Request' => $request->getParsedBody()]);
        $respuesta["error"] = 1;
        $respuesta["response"] = "Ha ocurrido un error. Intenta de nuevo.";
        return $response->withJson($respuesta, 500)->withHeader('Content-type', 'application/json');
    }
    $respuesta["error"] = 0;
    $respuesta["response"] = $department;
    $response = $next($request, $response);
    return $response->withJson($respuesta)->withHeader('Content-type', 'application/json');
};

/*
Retorna la lista general de ciudades
body del servicio
{
    "data": {
        "token": "Akw@pNr{A5?aV.zVwvTD%YX#:Nh2Lh"
    }
}
*/
$mwCity = function (Request $request, Response $response, $next) {
    $city2 = null;
    try {
        $query = $this->get('em')->createQuery("SELECT c FROM App\Entity\City2 c WHERE c.status='A'");
        $city2 = $query->getResult();
    } catch (Throwable $e) {
        if(validateLog())
            $this->get('logger')->error("",['File'=> __FILE__, 'Uri' => (string) $request->getUri(), 'Method' => $request->getMethod(),'Exception' => exceptionLog($e->getMessage()),'Request' => $request->getParsedBody()]);
        $respuesta["error"] = 1;
        $respuesta["response"] = "Ha ocurrido un error. Intenta de nuevo.";
        return $response->withJson($respuesta, 500)->withHeader('Content-type', 'application/json');
    }
    $respuesta["error"] = 0;
    $respuesta["response"] = $city2;
    $response = $next($request, $response);
    return $response->withJson($respuesta)->withHeader('Content-type', 'application/json');
};

/*
Retorna la lista de los tipos
body del servicio
{
    "data": {
        "token": "Akw@pNr{A5?aV.zVwvTD%YX#:Nh2Lh"
    }
}
*/
$mwGetVarious =  function (Request $request, Response $response, $next) {
    $variouss = null;
    try {
        $query = $this->get('em')->createQuery("SELECT v FROM App\Entity\Various v WHERE v.status='A' AND v.type NOT IN ('TIMEOUT','FAIL1','FAIL2','FAIL3','FAIL4','FAIL5','FAIL6','FAIL7','FAIL8','FAIL9','FAIL10','FAIL11','FAIL12','REDIRECCION','TYC')");
        $variouss = $query->getResult();
    } catch (Throwable $e) {
        if(validateLog())
        $this->get('logger')->error("",['File'=> __FILE__, 'Uri' => (string) $request->getUri(),'Method' => $request->getMethod(),'Exception' => exceptionLog($e->getMessage()),'Request' => $request->getParsedBody()]);
        $respuesta["error"] = 1;
        $respuesta["response"] = $e->getMessage();//"Ha ocurrido un error. Intenta de nuevo.";
        return $response->withJson($respuesta, 500)->withHeader('Content-type', 'application/json');
    }
    $arrayVarious["various"] = $variouss;
    $respuesta["error"] = 0;
    $respuesta["response"] = $arrayVarious;
    $response = $next($request, $response);
    return $response->withJson($respuesta)->withHeader('Content-type', 'application/json');
};

/*
Retorna la lista inicial
body del servicio
{
    "data": {
        "token": "Akw@pNr{A5?aV.zVwvTD%YX#:Nh2Lh"
    }
}
*/
$mwGetInitialVarious =  function (Request $request, Response $response, $next) {
    $variouss = null;
    try {
        $query = $this->get('em')->createQuery("SELECT v FROM App\Entity\Various v WHERE v.status='A' AND v.type IN ('TIMEOUT','FAIL1','FAIL2','FAIL3','FAIL4','FAIL5','FAIL6','FAIL7','FAIL8','FAIL9','FAIL10','FAIL11','FAIL12','REDIRECCION')");
        $variouss = $query->getResult();
    } catch (Throwable $e) {
        if(validateLog())
        $this->get('logger')->error("",['File'=> __FILE__, 'Uri' => (string) $request->getUri(),
         'Method' => $request->getMethod(),'Exception' => exceptionLog($e->getMessage()),'Request' 
         => $request->getParsedBody()]);
        $respuesta["error"] = 1;
        $respuesta["response"] = $e->getMessage();//"Ha ocurrido un error. Intenta de nuevo.";
        return $response->withJson($respuesta, 500)->withHeader('Content-type', 'application/json');
    }
    $arrayVarious["various"] = $variouss;
    $respuesta["error"] = 0;
    $respuesta["response"] = $arrayVarious;
    return $response->withJson($respuesta)->withHeader('Content-type', 'application/json');
};

/*
Retorna la lista de los tipos
body del servicio
{
    "data": {
        "type" : "SALUDO"
    }
}
*/
$mwGetVariousByType = function (Request $request, Response $response, $next) {
    $json = json_decode($request->getBody());
    $data = $json->data;
    $type = $data->type;
    $variouss = null;
    try {
        $query = $this->get('em')->createQuery("SELECT v FROM App\Entity\Various v 
        WHERE v.status='A' AND v.type = :type");
        $query->setParameter('type',$type);
        $variouss = $query->getResult();
    } catch (Throwable $e) {
        if(validateLog())
        $this->get('logger')->error("",['File'=> __FILE__, 'Uri' => (string) $request->getUri(),'Method' => $request->getMethod(),'Exception' => exceptionLog($e->getMessage()),'Request' => $request->getParsedBody()]);
        $respuesta["error"] = 1;
        $respuesta["response"] = $e->getMessage();//"Ha ocurrido un error. Intenta de nuevo.";
        return $response->withJson($respuesta, 500)->withHeader('Content-type', 'application/json');
    }
    $respuesta["error"] = 0;
    $respuesta["response"] = $variouss;
    $response = $next($request, $response);
    return $response->withJson($respuesta)->withHeader('Content-type', 'application/json');
};

/*
Retorna un tipo por si id
body del servicio
{
    "data": {
        "variousId" : 5
    }
}
*/
$mwGetVariousById = function (Request $request, Response $response, $next) {
    $json = json_decode($request->getBody());
    $data = $json->data;
    $variousId = $data->variousId;
    $variouss = null;
    try {
        $query = $this->get('em')->createQuery("SELECT v FROM App\Entity\Various v 
        WHERE v.status='A' AND v.variousId = :variousId");
        $query->setParameter('variousId',$variousId);
        $variouss = $query->getResult();
    } catch (Throwable $e) {
        if(validateLog())
        $this->get('logger')->error("",['File'=> __FILE__, 'Uri' => (string) $request->getUri(),'Method' => $request->getMethod(),'Exception' => exceptionLog($e->getMessage()),'Request' => $request->getParsedBody()]);
        $respuesta["error"] = 1;
        $respuesta["response"] = $e->getMessage();//"Ha ocurrido un error. Intenta de nuevo.";
        return $response->withJson($respuesta, 500)->withHeader('Content-type', 'application/json');
    }
    $respuesta["error"] = 0;
    $respuesta["response"] = $variouss;
    $response = $next($request, $response);
    return $response->withJson($respuesta)->withHeader('Content-type', 'application/json');
};

/*
Registra un nuevo item
body del servicio
{
    "data": {
        "various": {
            "description" : "hola",
            "type" : "SALUDO",
            "order" : 4,
            "status" : "A",
            "externalId" : 4 
        }
    }
}
*/
$mwAddVarious = function (Request $request, Response $response, $next) {
    $json = json_decode($request->getBody());
    $data = $json->data;
    $various = $data->various;
    try {
        $var = new Various();
        $var->create($various->description,$various->type,$various->order,$various->status,$various->externalId);
        $query = $this->get('em')->persist($var); 
        $this->get('em')->flush();
        $variousId = $var->getVariousId();
    } catch (Throwable $e) {
        if(validateLog())
        $this->get('logger')->error("",['File'=> __FILE__, 'Uri' => (string) $request->getUri(),'Method' => $request->getMethod(),'Exception' => exceptionLog($e->getMessage()),'Request' => $request->getParsedBody()]);
        $respuesta["error"] = 1;
        $respuesta["response"] = $e->getMessage();//"Ha ocurrido un error. Intenta de nuevo.";
        return $response->withJson($respuesta, 500)->withHeader('Content-type', 'application/json');
    }
    $respuesta["error"] = 0;
    $respuesta["response"] = 'Registro ok con id '.strval($variousId);
    $response = $next($request, $response);
    return $response->withJson($respuesta)->withHeader('Content-type', 'application/json');
};

/*
Registra un nuevo item
body del servicio
{
    "data": {
        "various": {
            "variousId" : 33,
            "description" : "hola",
            "type" : "SALUDO",
            "order" : 4,
            "status" : "A",
            "externalId" : 4 
        }
    }
}
*/
$mwUpdateVarious = function (Request $request, Response $response, $next) {
    $json = json_decode($request->getBody());
    $data = $json->data;
    $various = $data->various;
    try {
        $var = $this->get('em')->getRepository('App\Entity\Various')->find($various->variousId);
        if(isset($various->description)){
            $var->setDescription($various->description);
        }
        if(isset($various->type)){
            $var->setType($various->type);
        }
        if(isset($various->order)){
            $var->setOrder($various->order);
        }            
        if(isset($various->status)){
            $var->setStatus($various->status);
        }            
        if(isset($various->externalId)){
            $var->setExternalId($various->externalId);
        }
        $this->get('em')->flush();

    } catch (Throwable $e) {
        if(validateLog())
        $this->get('logger')->error("",['File'=> __FILE__, 'Uri' => (string) $request->getUri(),'Method' => $request->getMethod(),'Exception' => exceptionLog($e->getMessage()),'Request' => $request->getParsedBody()]);
        $respuesta["error"] = 1;
        $respuesta["response"] = "Ha ocurrido un error. Intenta de nuevo."; return $response->withJson($respuesta, 500)->withHeader('Content-type','application/json');
    }
    $respuesta["error"] = 0;
    $respuesta["response"] = $var;
    $response = $next($request, $response);
    return $response->withJson($respuesta)->withHeader('Content-type', 'application/json');
};

/*
Retorna la lista de ciudades por un departamento
body del servicio
{
    "data": {
                "depto": {
                "departmentId":"30"
            }
    }
}
*/
$mwCitybydepto = function (Request $request, Response $response, $next) {
    $json = json_decode($request->getBody());
    $data = $json->data;
    $depto = $data->depto;
    $city2 = null;
    try {
        $query = $this->get('em')->createQuery("SELECT c
        FROM App\Entity\City2 c
            WHERE c.departmentId = :departmentId  AND c.status='A' 
            ORDER BY c.order ASC");
            $query->setParameter('departmentId', $depto->departmentId);
            $city2 = $query->getResult();
    } catch (Throwable $e) {
        if(validateLog())
            $this->get('logger')->error("",['File'=> __FILE__, 'Uri' => (string) $request->getUri(), 'Method' => $request->getMethod(),'Exception' => exceptionLog($e->getMessage()),'Request' => $request->getParsedBody()]);
        $respuesta["error"] = 1;
        $respuesta["response"] = "Ha ocurrido un error. Intenta de nuevo."; return $response->withJson($respuesta, 500)->withHeader('Content-type', 'application/json');
    }
    $respuesta["error"] = 0;
    $respuesta["response"] = $city2;
    $response = $next($request, $response);
    return $response->withJson($respuesta)->withHeader('Content-type', 'application/json');
};

/*
Retorna la lista de poblados por ciudad
body del servicio
{
    "data": {
                "city": {
                "cityId":"3"
            }
    }
}
*/
$mwPopulatedbycity =  function (Request $request, Response $response, $next) {
        $json = json_decode($request->getBody());
        $data = $json->data;
        $city = $data->city;
        $populated = null;
        try {
            $query = $this->get('em')->createQuery("SELECT p
            FROM App\Entity\Populated p
                WHERE p.cityId = :cityId  AND p.status='A' 
                ORDER BY p.order ASC");
                $query->setParameter('cityId', $city->cityId);
                $populated = $query->getResult();
        } catch (Throwable $e) {
            if(validateLog())
                $this->get('logger')->error("",['File'=> __FILE__, 'Uri' => (string) $request->getUri(), 'Method' => $request->getMethod(),'Exception' => exceptionLog($e->getMessage()),'Request' => $request->getParsedBody()]);
            $respuesta["error"] = 1;
            $respuesta["response"] = "Ha ocurrido un error. Intenta de nuevo.";
            return $response->withJson($respuesta, 500)->withHeader('Content-type', 'application/json');
        }
        $respuesta["error"] = 0;
        $respuesta["response"] = $populated;
        $response = $next($request, $response);
        return $response->withJson($respuesta)->withHeader('Content-type', 'application/json');
};

/*
Edita el estado de un departamento
body del servicio
{
    "data": {
            "depto": {
                "departamentId":"5",
                "order": 5,
                "status":"I"
            }
    }
}
*/
$mwEditstatusdepto = function (Request $request, Response $response, $next) {     
    $json = json_decode($request->getBody());
    $data = $json->data;
    $depto = $data->depto;
    $departament = null;
    try {
        $query = $this->get('em')->createQuery("UPDATE App\Entity\Departament d 
            SET d.status = :status, 
            d.order = :order
            WHERE d.departamentId = :departamentId");
            $query->setParameter('departamentId',$depto->departamentId);
            $query->setParameter('order', $depto->order);
            $query->setParameter('status', $depto->status);
            $departament = $query->getResult();
        $respuesta["error"] = 0;
        $respuesta["response"] = 'Edicion ok';
    } catch (Throwable $e) {
        if(validateLog())
        $this->get('logger')->error("",['File'=> __FILE__, 'Uri' => (string) $request->getUri(), 'Method' => $request->getMethod(),'Exception' => exceptionLog($e->getMessage()),'Request' => $request->getParsedBody()]);
        $respuesta["error"] = 1;
        $respuesta["response"] = "Ha ocurrido un error. Intenta de nuevo.";
        return $response->withJson($respuesta, 500)->withHeader('Content-type', 'application/json');
    }
    $response = $next($request, $response);
    return $response->withJson($respuesta)->withHeader('Content-type', 'application/json');
};

/*
Edita los atributos de una ciudad
body del servicio
{
    "data": {
           "cityrequest": {
                "cityId":"5",
                "status":"A",
                "order":5
            }
    }
}
*/
$mwEditcity = function (Request $request, Response $response, $next) {     
    $json = json_decode($request->getBody());
    $data = $json->data;
    $cityrequest = $data->cityrequest;
    $city = null;
    try {
        $query = $this->get('em')->createQuery("UPDATE App\Entity\City2 c
            SET c.status = :status,
            c.order = :order 
            WHERE c.cityId = :cityId");
            $query->setParameter('cityId', $cityrequest->cityId);
            $query->setParameter('status', $cityrequest->status);
            $query->setParameter('order', $cityrequest->order);
            $city2 = $query->getResult();
        $respuesta["error"] = 0;
        $respuesta["response"] = 'Edicion ok';
    } catch (Throwable $e) {
        if(validateLog())
        $this->get('logger')->error("",['File'=> __FILE__, 'Uri' => (string) $request->getUri(), 'Method' => $request->getMethod(),'Exception' => exceptionLog($e->getMessage()),'Request' => $request->getParsedBody()]);
        $respuesta["error"] = 1;
        $respuesta["response"] = "Ha ocurrido un error. Intenta de nuevo.";
        return $response->withJson($respuesta, 500)->withHeader('Content-type', 'application/json');
    }
    $response = $next($request, $response);
    return $response->withJson($respuesta)->withHeader('Content-type', 'application/json');
};

/*
Edita el estado de las ciudades pertenecientes a un departamento
*/
$mwEditstatuscitybydepto =  function (Request $request, Response $response, $next) {     
    $json = json_decode($request->getBody());
    $data = $json->data;
    $deptorequest = $data->deptorequest;
    $city = null;
    try {
        $query = $this->get('em')->createQuery("UPDATE App\Entity\City2 c
            SET c.status = :status 
            WHERE c.departamentId = :departamentId");
            $query->setParameter('departamentId', $deptorequest->departamentId);
            $query->setParameter('status', $deptorequest->status);
            $city2 = $query->getResult();
        $respuesta["error"] = 0;
        $respuesta["response"] = 'Edicion ok';
    } catch (Throwable $e) {
        if(validateLog())
        $this->get('logger')->error("",['File'=> __FILE__, 'Uri' => (string) $request->getUri(), 'Method' => $request->getMethod(),'Exception' => exceptionLog($e->getMessage()),'Request' => $request->getParsedBody()]);
        $respuesta["error"] = 1;
        $respuesta["response"] = "Ha ocurrido un error. Intenta de nuevo.";
        return $response->withJson($respuesta, 500)->withHeader('Content-type', 'application/json');
    }
    $response = $next($request, $response);
    return $response->withJson($respuesta)->withHeader('Content-type', 'application/json');
};

/*
Generar token
*/
$mwToken = function (Request $request, Response $response, $next) {
        $headers =$request->getHeaders();
        $respuesta["error"] = 0;
        $respuesta["response"] = encrypt($request->getBody(), $headers["HTTP_X_ORIGIN"][0]);
        $response = $next($request, $response);
        return $response->withJson($respuesta)->withHeader('Content-type', 'application/json');        
};

/*consultar log*/
$mwLog = function (Request $request, Response $response, $next) {
    if(filter_var($_ENV['ACTIVELOG'], FILTER_VALIDATE_BOOLEAN)){
        $file = $this->get('settings')['logger']['path'];
        $errores = array();
        if(file_exists($file)){
            $fp = fopen($file, "r");
            $linea = '';
            while (!feof($fp)){
                $linea .= fgets($fp)."||";
            }
            fclose($fp);
            
            $linea = explode("||", $linea);
            $cont = 1;
            $search = array(chr(13).chr(10), "\r\n", "\n", "\r", "\\\\");
            $replace = array("", "", "", "", "\\");
            foreach($linea as $row){
                $errores["log_".$cont] = str_ireplace($search, $replace, $row);
                $cont++;
            }
            $errores = array_filter($errores);
        }else{
            $respuesta["error"] = 1;
            $respuesta["response"] = "No se encontro archivo de log.";
            return $response->withJson($respuesta, 404)->withHeader('Content-type', 'application/json');
        }
        $respuesta["error"] = 0;
        $respuesta["response"] = $errores;
        return $response->withJson($respuesta)->withHeader('Content-type', 'application/json');
    }else{
        $respuesta["error"] = 1;
        $respuesta["response"] = "No esta habilitado el uso del log.";
        return $response->withJson($respuesta,401)->withHeader('Content-type', 'application/json');        
    }
};
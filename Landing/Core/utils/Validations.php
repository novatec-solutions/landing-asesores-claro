<?php

class AppValidations
{
   
    #region textConstructor
    function textContent($code)
    {
        global $textConstructor;

        if (!($textConstructor instanceof TextConstructor)) {
            return $code;
        }

        return $textConstructor->getString($code);
    }
    #endregion

    #region datosServicios
    public function arrayToString($val)
    {
        if (isset($val)) {
            $temp = json_encode($val, true);

            if (json_last_error() == JSON_ERROR_NONE) {
                $temp = json_decode($temp);

                if (is_array($temp)) {
                    return "";
                } else {
                    return trim($val);
                }
            } else {
                return trim($val);
            }
        } else {
            return "";
        }
    }

    public function repleceNamespace(array $arr, string $text)
    {
        $resp = [];
        if (count($arr) == 0) {
            $resp = "";
        }
        foreach ((array) $arr as $key => $value) {
            if ((is_object($value) || is_array($value)) && (!empty($value))) {
                $resp[str_replace($text, "", $key)] = repleceNamespace((array)$value, $text);
            } else {
                $resp[str_replace($text, "", $key)] = (string)$value;
            }
        }

        return $resp;
    }

    public function getArray($val)
    {

        $list = array();
        $temp = json_encode($val, true);
        $temp = json_decode($temp);

        if (is_array($temp)) {
            return $temp;
        } else {
            array_push($list, $temp);
            return $list;
        }
    }

    #endregion
}



//TODO: ELiminar las funciones para convertirlos en metodos de la clase

//for textConstructor
require_once __DIR__ . '/../../Libraries/TextConstructor/TextConstructor.php';


#region textConstructor
$includeList = get_included_files();
$serverRequest = $_SERVER['REQUEST_URI'];
$textConstructor = '';
if (sizeof($includeList)) {
    $textConstructor = new TextConstructor($includeList[0], $serverRequest);
}

function textContent($code)
{
    global $textConstructor;

    if (!($textConstructor instanceof TextConstructor)) {
        return $code;
    }

    return $textConstructor->getString($code);
}
#endregion

#region datosServicios
function repleceNamespace(array $arr, string $text)
{
    $resp = [];
    if (count($arr) == 0) {
        $resp = "";
    }
    foreach ((array) $arr as $key => $value) {
        if ((is_object($value) || is_array($value)) && (!empty($value))) {
            $resp[str_replace($text, "", $key)] = repleceNamespace((array)$value, $text);
        } else {
            $resp[str_replace($text, "", $key)] = (string)$value;
        }
    }

    return $resp;
}


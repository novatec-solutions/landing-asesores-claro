<?php
class Utils
{
    public function changeKeysResponse(array $array, string $var_removal, $voidOption="string")
    {
        if(sizeof($array)<=0){
            switch ($voidOption) {
                case 'string':
                    $value = "";
                    break;
                
                case 'object':
                    $value = (object) $array;
                    break;
                
                default:
                $value = $array;
                    break;
            }

            return $value;
        }

        $result = [];
        foreach ($array as $key => $value) {
           $texto =  preg_replace("/^$var_removal/", '', $key);
           $result[$texto] = (is_object($value) || is_array($value))?$this->changeKeysResponse((array)$value, $var_removal, $voidOption):$value;
        }
        
        return $result;
    }

    public function validateMultiArrayEver($array){
        $keys = array_keys((array) $array);
        if(is_string($keys[0])){
            return [$array];
        }
        return $array;
    }
    
    static function getHeadersX(){
        $headers = getallheaders();
        $header=[];
        if(count($headers)>0) {
            foreach (getallheaders() as $nombre => $valor) {
                if (strpos($nombre, 'X-') !== false) {
                    $header[] = "$nombre: $valor";
                }
            }
        }

        return $header;
    }
}

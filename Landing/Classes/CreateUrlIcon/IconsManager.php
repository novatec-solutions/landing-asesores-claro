<?php


namespace M3\Classes\CreateUrlIcon;


class IconsManager implements IconsManagerInterface
{
    protected $url='https://apiselfservice.co/archivos/catalogoIcons/';
    use UrlIcons;

    public function setUrl(string $url){
        if(fileExists($url)){
            $this->setUrl($url);
        }else{
            throw new Exception('No existe el folder, es necesario revisar la ubicación');
        }
    }

    public function getUrl(){
        return $this->url;
    }


}
<?php


namespace M3\Classes\CreateUrlIcon;

trait UrlIcons
{
    protected $aliasesIcons=[
        'facebook'=>['facebook','Facebook','FACEBOOK_A','FACEBOOK'],
        'whatsapp'=>['whatsapp','Whatsapp','WHATSAPP_A','WHATSAPP'],
        'twitter' =>['twitter','Twitter','TWITTER_A','TWITTER'],
        'instagram'=>['instagram','Instagram','INSTAGRAM_A','INSTAGRAM'],
        'youtube'=>['youtube','Youtube','YOUTUBE_A','YOUTUBE'],
        'waze' =>['waze','Waze','WAZE_A','WAZE'],
        'zoom' =>['ZOOM_A','ZOOM'],
        'teams' => ['TEAMS_A','TEAMS'],
        'webex' => ['WEBEX_A','WEBEX'],
        'netflix' => ['NETFLIX_A','NETFLIX REDES'],
        'tiktok' => ['TIKTOK_A','TIKTOK'],
        'uber' => ['UBER_A','UBER'],
        'zello' => ['ZELLO_A','ZELLO'],
        'cabify'=> ['CABIFY_A','CABIFY'],
        'clarovideo' => ['CLAROVIDEO_A','CLAROVIDEOCV'],
        'taxislibre' => ['TAXIS_LIBRES_A','TAXIS_LIBRES'],
        'pinterest'=> ['PINTEREST_A','PINTEREST']
    ];

    /**
     * @param array $aliases as MultiArray information ['imagen' => [Imagen, imagen_x, ... , imagen_] ]
     */
    public function setAlias(array $aliases){

        if(count($aliases)>0){
            foreach ($aliases as $key => $alias){
                if(!is_array($alias))
                {
                    throw new Exception("Se debe tener un arreglo Multidimensional, $key no tiene un arreglo interno");
                }
                if(isset($this->aliasesIcons[$key])){
                    $this->aliasesIcons[$key] = array_merge($this->aliasesIcons[$key], $alias);
                }else{
                    $this->aliasesIcons[$key] = $alias;
                }
            }
            return;
        }
        throw new Exception('Valor debe contener un array con información');
    }

    public function getUrlIcons(string $name){
        $icons = $this->aliasesIcons;
        $url = $this->getUrl();

        $lookedIcons =  array_filter($icons, function ($icon) use ($name){
            return in_array($name, $icon);
        });

        if(count($lookedIcons)>0)
        {
            return $url. array_keys($lookedIcons)[0].".png";
        }

        return $url."X.png";

    }

}
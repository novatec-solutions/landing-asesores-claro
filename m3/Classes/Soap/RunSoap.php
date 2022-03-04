<?php


namespace M3\Classes\Soap;


class RunSoap
{
    public $CONNECTTIMEOUT = 30;
    public $TIMEOUT = 30;
    public $RETURNTRANSFER = true;
    public $SSL_VERIFYPEER = false;
    public $SSL_VERIFYHOST = false;
    public $POST = true;
    public $POSTFIELDS = "";
    public $HTTPHEADER = array();
    public $HEADERS = array();
    private $txt_error="En este momento no podemos atender esta solicitud, intenta nuevamente.";
    private $isSoap=null;
    private $errorCurlText = '';
    private $errorCurlId = 0;
    private $isApiself = false;

    /**
     * @var string
     */
    private $url;
    /**
     * @var string
     */
    private $template;

    private $startTime;

    private $serverRes;

    private $curlInfo;

    private $textsToReplace=[
        " xmlns=\"http://services.cmPoller.sisges.telmex.com.co\"",
        " xmlns=\"https://services.cmPoller.sisges.telmex.com.co\"",
        " xmlns=\"Claro.SelfCareManagement.Services.Entities.Contracts\"",
        " xmlns=\"Claro.SelfCareManagement.Services.Exception.Contracts\"",
        "<![CDATA[",
        "]]>"
    ];

    private $textBlank=[];

    /**
     * RunSoap constructor.
     * @param string $url
     * @param string $template
     */
    public function __construct(string $url, string $template)
    {
        $this->url = $url;
        $this->template = $template;

        $this->startTime = microtime(true);
        $this->getBlankText();
    }

    /**
     * @param bool $isSoap
     */
    public function setIsSoap(bool $isSoap): void
    {
        $this->isSoap = $isSoap;
    }

    public function runSoap($tagBody="",$flag =0){
        if($flag===0){
            $res = $this->execSoap();
        }elseif($flag===1){
            $res = $this->execSoapForm();
        }else{
            $res = $this->execSoapPut();
        }

        $diff = microtime(true) - $this->startTime;
        $sec = intval($diff);
        $micro = $diff - $sec;
        $final = strftime('%T', mktime(0, 0, $sec)) . str_replace('0.', '.', sprintf('%.4f', $micro));
        $secs = $final;
        $tiempo=$this->timeToSeconds($secs);
        $response["secs"]=$secs;
        $response["tiempo"]=$tiempo;
        $response["responseServer"] = $this->serverRes;

        if(isset($res["error"]) && $res["error"] == 1){
            $res["secs"] = $response["secs"];
            $res["tiempo"] = $response["tiempo"];
            return $res;
        }

        $response["response"] = (is_null($this->isSoap)||$this->isSoap)?$res->$tagBody:(($this->isApiself())?$res['response']:$res);
        $response["error"] = 0;

        return $response;
    }

    protected function execSoap(){
        if(!is_null($this->isSoap)){
            if($this->isSoap){
                $contentType = "text/xml;charset=\"utf-8\"";
                $accept = "text/xml";
                $params = $this->template;
            }else{
                $params = $this->template;
                $contentType = "application/json;charset=\"utf-8\"";
                $accept = "application/json";
            }

            $this->HEADERS = array_merge($this->HEADERS, (count($this->HTTPHEADER)>0)?$this->HTTPHEADER:array(
                "Content-type: ".$contentType,
                "Accept: ".$accept,
                "Cache-Control: no-cache",
                "Pragma: no-cache",
                "Content-length: ".strlen($this->template)
            )) ;
        }

        $soap_do = curl_init();
        curl_setopt($soap_do, CURLOPT_URL,$this->url);
        curl_setopt($soap_do, CURLOPT_CONNECTTIMEOUT, $this->CONNECTTIMEOUT);
        curl_setopt($soap_do, CURLOPT_TIMEOUT,        $this->TIMEOUT);
        curl_setopt($soap_do, CURLOPT_RETURNTRANSFER, $this->RETURNTRANSFER );
        curl_setopt($soap_do, CURLOPT_SSL_VERIFYPEER, $this->SSL_VERIFYPEER);
        curl_setopt($soap_do, CURLOPT_SSL_VERIFYHOST, $this->SSL_VERIFYHOST);
        curl_setopt($soap_do, CURLOPT_POST,           $this->POST );
        curl_setopt($soap_do, CURLOPT_POSTFIELDS,     $this->template);
        curl_setopt($soap_do, CURLOPT_HTTPHEADER,     $this->HEADERS);
        $res = curl_exec($soap_do);
        $this->serverRes = $res;

        $this->curlInfo = curl_getinfo($soap_do);
        $this->errorCurlId=curl_errno($soap_do);
        $this->errorCurlText=curl_error($soap_do);

        curl_close($soap_do);
        if(!$res) {
            $response["response"] = $this->txt_error . "1" . " - RED";
            $response["error"] = 1;
            return $response;
        }

        if(!is_null($this->isSoap) && !$this->isSoap){
            var_dump($res);
            die;

            $r = json_decode($res);
            if (json_last_error() == JSON_ERROR_NONE){
                $response["response"] = $r;
                $response["error"] = 0;
                return $response;
            }else{
                $res_data = array("error"=>1,"response"=>$this->txt_error . "2","secs"=>$secs??0,"dataE"=>json_last_error());
                return $res_data;
            }

        }

        $res= str_replace("soap-env", "soapenv", $res);
        $res= str_replace("SOAP-ENV", "soapenv", $res);
        $res = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $res);
        $res = str_replace($this->textsToReplace, $this->textBlank, $res);
        try {
            libxml_use_internal_errors(true);
            $xml = new \SimpleXMLElement($res);
        } catch (Exception $e) {

            $response["response"] = $this->txt_error . "3";
            $response["error"] = 1;
            return $response;
        }
        return $xml;
    }

    public function execSoapPut(){
        if(!is_null($this->isSoap)){
            if($this->isSoap){
                $contentType = "text/xml;charset=\"utf-8\"";
                $accept = "text/xml";
                $params = $this->template;
            }else{
                $params = $this->template;
                $contentType = "application/json;charset=\"utf-8\"";
                $accept = "application/json";
            }

            $this->HEADERS = array_merge($this->HEADERS, array(
                "Content-type: ".$contentType,
                "Accept: ".$accept,
                "Cache-Control: no-cache",
                "Pragma: no-cache",
                "Content-length: ".strlen($this->template)
            )) ;
        }

        $soap_do = curl_init();
        curl_setopt($soap_do, CURLOPT_URL,$this->url);
        curl_setopt($soap_do, CURLOPT_CONNECTTIMEOUT, $this->CONNECTTIMEOUT);
        curl_setopt($soap_do, CURLOPT_TIMEOUT,        $this->TIMEOUT);
        curl_setopt($soap_do, CURLOPT_RETURNTRANSFER, $this->RETURNTRANSFER );
        curl_setopt($soap_do, CURLOPT_SSL_VERIFYPEER, $this->SSL_VERIFYPEER);
        curl_setopt($soap_do, CURLOPT_SSL_VERIFYHOST, $this->SSL_VERIFYHOST);
        curl_setopt($soap_do, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($soap_do, CURLOPT_POSTFIELDS,     $this->template);
        curl_setopt($soap_do, CURLOPT_HTTPHEADER,     $this->HEADERS);
        
        $res = curl_exec($soap_do);
        $this->serverRes = $res;

        $this->curlInfo = curl_getinfo($soap_do);
        $this->errorCurlId=curl_errno($soap_do);
        $this->errorCurlText=curl_error($soap_do);

        curl_close($soap_do);
        if(!$res) {
            $response["response"] = $this->txt_error . "4". " - RED";
            $response["error"] = 1;
            return $response;
        }

        if(!is_null($this->isSoap) && !$this->isSoap){

            /*
            $r = json_decode($res);
            if (json_last_error() == JSON_ERROR_NONE){
            */

            if(!is_null($res)){
                //$response["response"] = $r;
                $response["response"] = $res;
                $response["error"] = 0;
                return $response;
            }else{
                //$res_data = array("error"=>1,"response"=>$this->txt_error . "5","secs"=>$secs,"dataE"=>json_last_error());
                $res_data = array("error"=>1,"response"=>$this->txt_error . "5");
                return $res_data;
            }

        }

        $res= str_replace("soap-env", "soapenv", $res);
        $res= str_replace("SOAP-ENV", "soapenv", $res);
        $res = preg_replace("/(<\/?)(\w+):([^>]*>)/", "$1$2$3", $res);
        $res = str_replace($this->textsToReplace, $this->textBlank, $res);
        try {
            libxml_use_internal_errors(true);
            $xml = new \SimpleXMLElement($res);
        } catch (Exception $e) {

            $response["response"] = $this->txt_error . "7";
            $response["error"] = 1;
            return $response;
        }
        return $xml;
    }

    protected function execSoapForm(){

        $data = http_build_query(json_decode($this->template, true));
        $this->HEADERS = array_merge($this->HEADERS, array(
            "Content-type: application/x-www-form-urlencoded"
        )) ;

        $curl = curl_init($this->url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->HEADERS);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        $this->serverRes = $res = curl_exec($curl);

        $this->curlInfo = curl_getinfo($curl);
        $this->errorCurlId=curl_errno($curl);
        $this->errorCurlText=curl_error($curl);

        curl_close($curl);
        if(!$res) {
            $response["response"] = $this->txt_error . "8" . " - RED";
            $response["error"] = 1;
            return $response;
        }

        $r = json_decode($res);
        if (json_last_error() == JSON_ERROR_NONE){
            $response["response"] = $r;
            $response["error"] = 0;
            return $response;
        }else{
            $res_data = array("error"=>1,"response"=>$this->txt_error . "9","secs"=>$secs??0,"dataE"=>json_last_error());
            return $res_data;
        }
    }

    protected function timeToSeconds($time)
    {
        $timeExploded = explode(':', $time);
        if (isset($timeExploded[2])) {
            return $timeExploded[0] * 3600 + $timeExploded[1] * 60 + $timeExploded[2];
        }
        return $timeExploded[0] * 3600 + $timeExploded[1] * 60;
    }

    /**
     * @return void
     */
    private function getBlankText(): void
    {
        $blank = [];
        array_map(function () use (&$blank) {
            array_push($blank, "");
            return true;
        }, $this->textsToReplace);
        $this->textBlank = $blank;
    }

    /**
     * @param int $CONNECTTIMEOUT
     */
    public function setCONNECTTIMEOUT(int $CONNECTTIMEOUT): void
    {
        $this->CONNECTTIMEOUT = $CONNECTTIMEOUT;
    }

    /**
     * @param int $TIMEOUT
     */
    public function setTIMEOUT(int $TIMEOUT): void
    {
        $this->TIMEOUT = $TIMEOUT;
    }

    /**
     * @return string
     */
    public function getErrorCurlText(): string
    {
        return $this->errorCurlText;
    }

    /**
     * @return int
     */
    public function getErrorCurlId(): int
    {
        return $this->errorCurlId;
    }

    public function setHeader(array $header): void
    {
        $this->HEADERS = $header;
    }

    /**
     * @return bool
     */
    public function isApiself(): bool
    {
        return $this->isApiself;
    }

    /**
     * @param bool $isApiself
     */
    public function setIsApiself(bool $isApiself): void
    {
        $this->isApiself = $isApiself;
    }

    public function setBaseHeaders(array $header){
        $this->HTTPHEADER = $header;
    }

    public function getInfoCurl(){
        return $this->curlInfo;
    }

}
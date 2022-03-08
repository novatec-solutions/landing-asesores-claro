<?php
require __DIR__ . '/../../Core/vendor/autoload.php';
require_once __DIR__ . '/../../Core/config.php';


class TextConstructor
{
    private $nameService;
    private $arrayText = [];
    private $arrayTextCodes=[];
    private $filePath = __DIR__."\\dictionary\\texts.json";

    /**
     * TextConstructor constructor.
     * @param $nameService
     */
    public function __construct($nameService=null, $serverRequest=null)
    {
        $server_document = str_replace( '\\\\','/', $nameService);
        $nameService = str_replace('\\', '/', $server_document);

        $this->nameService = $nameService;
        //$this->getInformationText($serverRequest);

    }

    public function getString($code)
    {
        if (!isset($code) || strlen(trim($code))<=0 ){
            return 'El codigo de respuesta esta vacio';
        }

        if(sizeof($this->arrayTextCodes)<=0){
            return $code;
        }

        if(isset($this->arrayTextCodes[$code])){
            return $this->arrayTextCodes[$code];
        }

        return $code;
    }

    public function getInformationText($serverRequest=null)
    {       
        try{          

            if(!file_exists($this->filePath)){
                $this->extractTextFromDB();
            }

            $fileContent = json_decode(file_get_contents($this->filePath));

            $expireTime = new DateTime($fileContent->expired_info->date);
            $currentTime = new DateTime();

            if(($currentTime->diff($expireTime)->i)>10){
                $this->extractTextFromDB();
                $fileContent = json_decode(file_get_contents($this->filePath));
            }


            $this->arrayText = (array)$fileContent->text;

            $this->arrayText = array_filter($this->arrayText,function($record) use ($serverRequest){
                return $record->nameService == $serverRequest;
            });

            if($this->arrayText && count($this->arrayText)>0){
                foreach ($this->arrayText as $value){
                    $value = (array)$value;
                    $this->arrayTextCodes[$value['serviceCode']] = $value['serviceContent'];
                }
            }
        }catch(Exception $e){
            $this->arrayTextCodes = [];
            $this->arrayText = [];
        } 
        return;
    }

    private function extractTextFromDB(){
        require_once __DIR__."/vendor/autoload.php";
        require_once(__DIR__ . '/bootstrap.php');

        $dotenv = new \Dotenv\Dotenv(__DIR__.'/config');
        $dotenv->load();
        try {
            $entityManager = getEntityManager();

            $queryBuilder = $entityManager->createQueryBuilder();

            $result = $queryBuilder
                ->select([
                    'stext.id',
                    'stdet.serviceCode',
                    'stdet.serviceContent',
                    'stext.nameService',
                    'stext.pathService'])
                ->from(ServiceText::class, 'stext')
                ->join(ServiceTextDetail::class, 'stdet', 'WITH', 'stext.id = stdet.serviceText')
                //->where('stext.nameService = :param1')
                ->andWhere('stext.status = 1')
                ->andWhere('stdet.status = 1')
                //->setParameter('param1', $serverRequest)
                ->getQuery()
                ->setCacheable(true)
                ->setCacheMode(\Doctrine\ORM\Cache::MODE_NORMAL)
                ->setLifetime(3600)
                ->getResult();
            $result = new \Doctrine\Common\Collections\ArrayCollection($result);
            $infoFile = [
                'expired_info' => new DateTime(),
                'text' => $result->toArray()
            ];

            file_put_contents($this->filePath, json_encode($infoFile));
        }catch (PDOException $e) {
            $this->emptyFile();
        }catch (Exception $e){
            $this->emptyFile();
        }
        return;
    }

    private function emptyFile(): void
    {
        $this->arrayTextCodes = [];
        $this->arrayText = [];
        $infoFile = [
            'expired_info' => new DateTime(),
            'text' => []
        ];
        file_put_contents($this->filePath, json_encode($infoFile));
    }
}
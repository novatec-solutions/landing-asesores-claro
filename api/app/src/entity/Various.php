<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="LP_VARIOS")
 */
class Various implements \JsonSerializable
{
    /**
     * @ORM\Id
     * @ORM\Column(name="VARIOS_ID", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $variousId;

     /** 
     * @ORM\Column(name="DESCRIPCION", type="string") 
     */
    protected $description;

     /** 
     * @ORM\Column(name="TIPO", type="string") 
     */
    protected $type;    

    /** 
     * @ORM\Column(name="ORDEN", type="integer") 
     */
    protected $order;   
    
    /** 
     * @ORM\Column(name="ESTADO", type="string") 
     */
    protected $status;      

     /** 
     * @ORM\Column(name="IDEXTERNO", type="integer") 
     */
    protected $externalId;

    public function create(string $description,string $type,int $order,string $status,int $externalId){
        $this->description = $description;
        $this->type = $type;
        $this->order = $order;
        $this->status = $status;
        $this->externalId = $externalId;
    }

    public function __construct(){     
    }

    public function getArrayCopy()
    {
        return get_object_vars($this);
    }

    public function getVariousId()
    {
        return $this->variousId;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getType()
    {
        return $this->type;
    }
    
    public function getOrder()
    {
        return $this->order;
    }

    public function getStatus()
    {
        return $this->status;
    }    

    public function getExternalId()
    {
        return $this->externalId;
    }

    public function setDescription(string $description){
        $this->description = $description;
    }

    public function setType(string $type){
        $this->type = $type;
    }

    public function setOrder(int $order){
        $this->order = $order;
    }

    public function setStatus(string $status){
        $this->status = $status;
    }

    public function setExternalId(int $externalId){
        $this->externalId = $externalId;
    }
    
    public function jsonSerialize(): array
    {
        return [
            'variousId' => (int) $this->variousId,
            'description' => $this->description,
            'type' => $this->type,
            'order' => $this->order,
            'status' => $this->status,
            'externalId' => (int) $this->externalId
        ];
    }

}
<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="LP_POBLADO")
 */
class Populated implements \JsonSerializable
{
    /**
     * @ORM\Id
     * @ORM\Column(name="POBLADO_ID", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $populatedId;

    /** 
     * @ORM\Column(name="CIUDAD_ID", type="integer") 
     */
    private $cityId;

    /** 
     * @ORM\Column(name="NOMBRE", type="string") 
     */
    protected $name;

    /** 
     * @ORM\Column(name="ESTADO", type="string") 
     */
    protected $status;    

    /** 
     * @ORM\Column(name="ORDEN", type="integer") 
     */
    protected $order;      
    
    /** 
     * @ORM\Column(name="CODIGODANE", type="string") 
     */
    protected $danecode;



    
    public function getArrayCopy()
    {
        return get_object_vars($this);
    }

    
    public function getPopulatedIdId()
    {
        return $this->populatedId;
    }

    public function getCityId()
    {
        return $this->cityId;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function getOrder()
    {
        return $this->order;
    }

    public function getDanecode()
    {
        return $this->danecode;
    }    



    public function jsonSerialize(): array
    {
        return [
            'id' =>         strval($this->populatedId),            
            'text' =>           ucwords(mb_strtolower($this->name)),
            'cityId' =>    strval($this->cityId),
            'status' =>         $this->status,
            'order' =>          $this->order,
            'danecode' =>          $this->danecode
        ];
    }

}
<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity;
use Doctrine\ORM\Mapping as ORM;


/**
 * @ORM\Entity
 * @ORM\Table(name="LP_DEPARTAMENTO")
 */
class Department implements \JsonSerializable
{
    /**
     * @ORM\Id
     * @ORM\Column(name="DEPARTAMENTO_ID", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $departmentId;

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

    public function getDepartmentId()
    {
        return $this->departmentId;
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
            'id' => strval($this->departmentId),
            'text' => ucwords(mb_strtolower($this->name)),
            'status' => $this->status,
            'order' => $this->order,
            'danecode' => $this->danecode
        ];
    }
}
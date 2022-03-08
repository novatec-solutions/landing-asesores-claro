<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * ServiceText
 *
 * @ORM\Table(name="service_text")
 * @ORM\Entity
 */
class ServiceText
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name_service", type="string", length=255, nullable=false)
     */
    private $nameService;

    /**
     * @var string
     *
     * @ORM\Column(name="path_service", type="string", length=255, nullable=false)
     */
    private $pathService;

    /**
     * @var int;
     * 
     * @ORM\Column(name="status", type="integer", length = 1, nullable=false)
     */
    private $status;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_created", type="datetime", nullable=false)
     */
    private $dateCreated;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="date_updated", type="datetime", nullable=true, options={"default"="CURRENT_TIMESTAMP"})
     */
    private $dateUpdated = 'CURRENT_TIMESTAMP';

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getNameService()
    {
        return $this->nameService;
    }

    /**
     * @param string $nameService
     */
    public function setNameService($nameService)
    {
        $this->nameService = $nameService;
    }

    /**
     * @return string
     */
    public function getPathService()
    {
        return $this->pathService;
    }

    /**
     * @param string $pathService
     */
    public function setPathService($pathService)
    {
        $this->pathService = $pathService;
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param int $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return DateTime
     */
    public function getDateCreated()
    {
        return $this->dateCreated;
    }

    /**
     * @param DateTime $dateCreated
     */
    public function setDateCreated($dateCreated)
    {
        $this->dateCreated = $dateCreated;
    }

    /**
     * @return DateTime|null
     */
    public function getDateUpdated()
    {
        return $this->dateUpdated;
    }

    /**
     * @param DateTime|null $dateUpdated
     */
    public function setDateUpdated($dateUpdated)
    {
        $this->dateUpdated = $dateUpdated;
    }


}

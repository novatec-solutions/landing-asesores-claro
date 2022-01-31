<?php



use Doctrine\ORM\Mapping as ORM;

/**
 * ServiceTextDetail
 *
 * @ORM\Table(name="service_text_detail", uniqueConstraints={@ORM\UniqueConstraint(name="unique_values_1", columns={"service_text_id", "service_code"})}, indexes={@ORM\Index(name="fk_service_text_detail_service_texts1_idx", columns={"service_text_id"})})
 * @ORM\Entity
 */
class ServiceTextDetail
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="service_content", type="text", length=16777215, nullable=false)
     */
    private $serviceContent;

    /**
     * @var string|null
     *
     * @ORM\Column(name="service_code", type="string", length=255, nullable=true)
     */
    private $serviceCode;

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
     * @var \ServiceText
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="ServiceText")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="service_text_id", referencedColumnName="id")
     * })
     */
    private $serviceText;

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
    public function getServiceContent()
    {
        return $this->serviceContent;
    }

    /**
     * @param string $serviceContent
     */
    public function setServiceContent($serviceContent)
    {
        $this->serviceContent = $serviceContent;
    }

    /**
     * @return string|null
     */
    public function getServiceCode()
    {
        return $this->serviceCode;
    }

    /**
     * @param string|null $serviceCode
     */
    public function setServiceCode($serviceCode)
    {
        $this->serviceCode = $serviceCode;
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
     * @return ServiceText
     */
    public function getServiceText()
    {
        return $this->serviceText;
    }

    /**
     * @param ServiceText $serviceText
     */
    public function setServiceText($serviceText)
    {
        $this->serviceText = $serviceText;
    }


}

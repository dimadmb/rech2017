<?php

namespace CruiseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Pay
 *
 * @ORM\Table(name="pay")
 * @ORM\Entity(repositoryClass="CruiseBundle\Repository\PayRepository")
 * @ORM\HasLifecycleCallbacks  
 */
class Pay
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="comment", type="string", length=255, nullable=true)
     */
    private $comment;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime")
     */
    private $created;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="date", nullable=true)
     */
    private $date;

    /**
     * @var string
     *
     * @ORM\Column(name="amount", type="decimal", precision=10, scale=2)
     */
    private $amount;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_delete", type="boolean")
     */
    private $isDelete = false;
	
    /**
     * @var string
     *
     * @ORM\Column(name="request_starrus", type="text", nullable=true)
     */
    private $requestStarrus;	
	
		
    /**
     * @var string
     *
     * @ORM\Column(name="response_starrus", type="text", nullable=true)
     */
    private $responseStarrus;	
	
	
	/**
	 * @ORM\ManyToOne(targetEntity="Ordering", inversedBy="pays")
	 */
	private $order;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set comment
     *
     * @param string $comment
     *
     * @return Pay
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get comment
     *
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     *
     * @return Pay
	 * @ORM\PrePersist()	
     */
    public function setCreated()
    {
        $this->created = new \DateTime;

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set amount
     *
     * @param string $amount
     *
     * @return Pay
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Get amount
     *
     * @return string
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set isDelete
     *
     * @param boolean $isDelete
     *
     * @return Pay
     */
    public function setIsDelete($isDelete)
    {
        $this->isDelete = $isDelete;

        return $this;
    }

    /**
     * Get isDelete
     *
     * @return bool
     */
    public function getIsDelete()
    {
        return $this->isDelete;
    }

    /**
     * Set order
     *
     * @param \CruiseBundle\Entity\Ordering $order
     *
     * @return Pay
     */
    public function setOrder(\CruiseBundle\Entity\Ordering $order = null)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * Get order
     *
     * @return \CruiseBundle\Entity\Ordering
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Set requestStarrus
     *
     * @param string $requestStarrus
     *
     * @return Pay
     */
    public function setRequestStarrus($requestStarrus)
    {
        $this->requestStarrus = $requestStarrus;

        return $this;
    }

    /**
     * Get requestStarrus
     *
     * @return string
     */
    public function getRequestStarrus()
    {
        return $this->requestStarrus;
    }

    /**
     * Set responseStarrus
     *
     * @param string $responseStarrus
     *
     * @return Pay
     */
    public function setResponseStarrus($responseStarrus)
    {
        $this->responseStarrus = $responseStarrus;

        return $this;
    }

    /**
     * Get responseStarrus
     *
     * @return string
     */
    public function getResponseStarrus()
    {
        return $this->responseStarrus;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     *
     * @return Pay
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }
}

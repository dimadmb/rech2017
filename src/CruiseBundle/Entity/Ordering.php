<?php

namespace CruiseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Ordering
 *
 * @ORM\Table(name="ordering")
 * @ORM\Entity(repositoryClass="CruiseBundle\Repository\OrderingRepository")
 * @ORM\HasLifecycleCallbacks 
 */
class Ordering
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
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime")
     */
    private $created;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated", type="datetime", nullable=true)
     */
    private $updated;

    /**
     * @var string
     *
     * @ORM\Column(name="comment_manager", type="text", nullable=true)
     */
    private $commentManager;

    /**
     * @var string
     *
     * @ORM\Column(name="comment_user", type="text", nullable=true)
     */
    private $commentUser;

    /**
     * @var string
     *
     * @ORM\Column(name="fee", type="decimal", precision=10, scale=2, nullable=true)
     */
    private $fee;

    /**
     * @var string
     *
     * @ORM\Column(name="permanent_discount", type="decimal", precision=10, scale=2, nullable=true)
     */
    private $permanentDiscount;

    /**
     * @var bool
     *
     * @ORM\Column(name="permanent_request", type="boolean", nullable=true)
     */
    private $permanentRequest;

    /**
     * @var string
     *
     * @ORM\Column(name="seson_discount", type="decimal", precision=10, scale=2, nullable=true)
     */
    private $sesonDiscount;

    /**
     * @var bool
     *
     * @ORM\Column(name="active", type="boolean")
     */
    private $active = true;

    /**
     * @var bool
     *
     * @ORM\Column(name="paid", type="boolean")
     */
    private $paid= false;
	
	
	/**
	 * @ORM\OneToMany(targetEntity="OrderItem", mappedBy="ordering") 

	 */
	private $orderItems;
	
	
	/**
	 * @ORM\ManyToOne(targetEntity="BaseBundle\Entity\User", inversedBy="orders")
	 */
	private $user;	
	
	/**
	 * @ORM\ManyToOne(targetEntity="Buyer", inversedBy="orders")
	 */
	private $buyer;	
	
	/**
	 * @ORM\ManyToOne(targetEntity="Agency", inversedBy="orders")
	 */
	private $agency;
	
	/**
	 * @ORM\OneToMany(targetEntity="Pay", mappedBy="order")
	 */
	private $pays;
	
	
	private $summ;

    public function getSumm()
    {
		$summ = 0;

		foreach($this->getOrderItems() as $orderItem )
		{
			
			foreach($orderItem->getOrderItemPlaces() as $orderItemPlace)
			{
				$summ += $orderItemPlace->getPriceValue();
			}
		}

		$this->summ = $summ;
		
		return $summ;
    }	

    public function setSumm($summ)
    {
        $this->summ = $summ;

        return $this;
    }	
		
	
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
     * Set created
     *
     * @param \DateTime $created
     *
     * @return Ordering
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
     * Set updated
     *
     * @param \DateTime $updated
     *
     * @return Ordering
	 * @ORM\PreUpdate()	 
     */
    public function setUpdated()
    {
        $this->updated = new \DateTime;

        return $this;
    }

    /**
     * Get updated
     *
     * @return \DateTime
     */
    public function getUpdated()
    {
        return $this->updated;
    }
	

    /**
     * Set commentManager
     *
     * @param string $commentManager
     *
     * @return Ordering
     */
    public function setCommentManager($commentManager)
    {
        $this->commentManager = $commentManager;

        return $this;
    }

    /**
     * Get commentManager
     *
     * @return string
     */
    public function getCommentManager()
    {
        return $this->commentManager;
    }

    /**
     * Set commentUser
     *
     * @param string $commentUser
     *
     * @return Ordering
     */
    public function setCommentUser($commentUser)
    {
        $this->commentUser = $commentUser;

        return $this;
    }

    /**
     * Get commentUser
     *
     * @return string
     */
    public function getCommentUser()
    {
        return $this->commentUser;
    }

    /**
     * Set fee
     *
     * @param string $fee
     *
     * @return Ordering
     */
    public function setFee($fee)
    {
        $this->fee = $fee;

        return $this;
    }

    /**
     * Get fee
     *
     * @return string
     */
    public function getFee()
    {
        return $this->fee;
    }

    /**
     * Set permanentDiscount
     *
     * @param string $permanentDiscount
     *
     * @return Ordering
     */
    public function setPermanentDiscount($permanentDiscount)
    {
        $this->permanentDiscount = $permanentDiscount;

        return $this;
    }

    /**
     * Get permanentDiscount
     *
     * @return string
     */
    public function getPermanentDiscount()
    {
        return $this->permanentDiscount;
    }

    /**
     * Set permanentRequest
     *
     * @param bool $permanentRequest
     *
     * @return Ordering
     */
    public function setPermanentRequest($permanentRequest)
    {
        $this->permanentRequest = $permanentRequest;

        return $this;
    }

    /**
     * Get permanentRequest
     *
     * @return bool
     */
    public function getPermanentRequest()
    {
        return $this->permanentRequest;
    }

    /**
     * Set sesonDiscount
     *
     * @param string $sesonDiscount
     *
     * @return Ordering
     */
    public function setSesonDiscount($sesonDiscount)
    {
        $this->sesonDiscount = $sesonDiscount;

        return $this;
    }

    /**
     * Get sesonDiscount
     *
     * @return string
     */
    public function getSesonDiscount()
    {
        return $this->sesonDiscount;
    }

    /**
     * Set active
     *
     * @param boolean $active
     *
     * @return Ordering
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get active
     *
     * @return bool
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Set paid
     *
     * @param boolean $paid
     *
     * @return Ordering
     */
    public function setPaid($paid)
    {
        $this->paid = $paid;

        return $this;
    }

    /**
     * Get paid
     *
     * @return bool
     */
    public function getPaid()
    {
        return $this->paid;
    }
	
	/**
	 * @ORM\ManyToOne(targetEntity="Cruise")
	 */
	private $cruise;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->orderItems = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add orderItem
     *
     * @param \CruiseBundle\Entity\OrderItem $orderItem
     *
     * @return Ordering
     */
    public function addOrderItem(\CruiseBundle\Entity\OrderItem $orderItem)
    {
        $this->orderItems[] = $orderItem;

        return $this;
    }

    /**
     * Remove orderItem
     *
     * @param \CruiseBundle\Entity\OrderItem $orderItem
     */
    public function removeOrderItem(\CruiseBundle\Entity\OrderItem $orderItem)
    {
        $this->orderItems->removeElement($orderItem);
    }

    /**
     * Get orderItems
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getOrderItems()
    {
        return $this->orderItems;
    }

    /**
     * Set cruise
     *
     * @param \CruiseBundle\Entity\Cruise $cruise
     *
     * @return Ordering
     */
    public function setCruise(\CruiseBundle\Entity\Cruise $cruise = null)
    {
        $this->cruise = $cruise;

        return $this;
    }

    /**
     * Get cruise
     *
     * @return \CruiseBundle\Entity\Cruise
     */
    public function getCruise()
    {
        return $this->cruise;
    }

    /**
     * Set user
     *
     * @param \BaseBundle\Entity\User $user
     *
     * @return Ordering
     */
    public function setUser(\BaseBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \BaseBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set buyer
     *
     * @param \CruiseBundle\Entity\Buyer $buyer
     *
     * @return Ordering
     */
    public function setBuyer(\CruiseBundle\Entity\Buyer $buyer = null)
    {
        $this->buyer = $buyer;

        return $this;
    }

    /**
     * Get buyer
     *
     * @return \CruiseBundle\Entity\Buyer
     */
    public function getBuyer()
    {
        return $this->buyer;
    }

    /**
     * Set agency
     *
     * @param \CruiseBundle\Entity\Agency $agency
     *
     * @return Ordering
     */
    public function setAgency(\CruiseBundle\Entity\Agency $agency = null)
    {
        $this->agency = $agency;

        return $this;
    }

    /**
     * Get agency
     *
     * @return \CruiseBundle\Entity\Agency
     */
    public function getAgency()
    {
        return $this->agency;
    }

    /**
     * Add pay
     *
     * @param \CruiseBundle\Entity\Pay $pay
     *
     * @return Ordering
     */
    public function addPay(\CruiseBundle\Entity\Pay $pay)
    {
        $this->pays[] = $pay;

        return $this;
    }

    /**
     * Remove pay
     *
     * @param \CruiseBundle\Entity\Pay $pay
     */
    public function removePay(\CruiseBundle\Entity\Pay $pay)
    {
        $this->pays->removeElement($pay);
    }

    /**
     * Get pays
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPays()
    {
        return $this->pays;
    }
}

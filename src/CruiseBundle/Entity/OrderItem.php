<?php

namespace CruiseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * OrderItem
 *
 * @ORM\Table(name="order_item")
 * @ORM\Entity(repositoryClass="CruiseBundle\Repository\OrderItemRepository")
 */
class OrderItem
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
	 * @ORM\ManyToOne(targetEntity="Ordering", inversedBy="orderItems")
	 * @ORM\JoinColumn(name="ordering_id", referencedColumnName="id", onDelete="CASCADE")	 
	 */
	private $ordering;
	
	/**
	 * @ORM\ManyToOne(targetEntity="ShipRoom")
	 */
	private $room;
	
	/**
	 * @ORM\ManyToOne(targetEntity="ShipCabinPlace")
	 */
	private $place;
	
	/**
	 * @ORM\OneToMany(targetEntity="OrderItemPlace", mappedBy="orderItem")

	 */
	private $orderItemPlaces;
	
	
	/**
	 * @ORM\ManyToOne(targetEntity="TypeDiscount")
	 */
	private $typeDiscount;


	public function __toString()
	{
		return $this->room->getNumber();
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
     * Set ordering
     *
     * @param \CruiseBundle\Entity\Ordering $ordering
     *
     * @return OrderItem
     */
    public function setOrdering(\CruiseBundle\Entity\Ordering $ordering = null)
    {
        $this->ordering = $ordering;

        return $this;
    }

    /**
     * Get ordering
     *
     * @return \CruiseBundle\Entity\Ordering
     */
    public function getOrdering()
    {
        return $this->ordering;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->orderItemPlaces = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set room
     *
     * @param \CruiseBundle\Entity\ShipRoom $room
     *
     * @return OrderItem
     */
    public function setRoom(\CruiseBundle\Entity\ShipRoom $room = null)
    {
        $this->room = $room;

        return $this;
    }

    /**
     * Get room
     *
     * @return \CruiseBundle\Entity\ShipRoom
     */
    public function getRoom()
    {
        return $this->room;
    }

    /**
     * Add orderItemPlace
     *
     * @param \CruiseBundle\Entity\OrderItemPlace $orderItemPlace
     *
     * @return OrderItem
     */
    public function addOrderItemPlace(\CruiseBundle\Entity\OrderItemPlace $orderItemPlace)
    {
        $this->orderItemPlaces[] = $orderItemPlace;

        return $this;
    }

    /**
     * Remove orderItemPlace
     *
     * @param \CruiseBundle\Entity\OrderItemPlace $orderItemPlace
     */
    public function removeOrderItemPlace(\CruiseBundle\Entity\OrderItemPlace $orderItemPlace)
    {
        $this->orderItemPlaces->removeElement($orderItemPlace);
    }

    /**
     * Get orderItemPlaces
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getOrderItemPlaces()
    {
        return $this->orderItemPlaces;
    }

    /**
     * Set place
     *
     * @param \CruiseBundle\Entity\ShipCabinPlace $place
     *
     * @return OrderItem
     */
    public function setPlace(\CruiseBundle\Entity\ShipCabinPlace $place = null)
    {
        $this->place = $place;

        return $this;
    }

    /**
     * Get place
     *
     * @return \CruiseBundle\Entity\ShipCabinPlace
     */
    public function getPlace()
    {
        return $this->place;
    }

    /**
     * Set typeDiscount
     *
     * @param \CruiseBundle\Entity\TypeDiscount $typeDiscount
     *
     * @return OrderItem
     */
    public function setTypeDiscount(\CruiseBundle\Entity\TypeDiscount $typeDiscount = null)
    {
        $this->typeDiscount = $typeDiscount;

        return $this;
    }

    /**
     * Get typeDiscount
     *
     * @return \CruiseBundle\Entity\TypeDiscount
     */
    public function getTypeDiscount()
    {
        return $this->typeDiscount;
    }
}

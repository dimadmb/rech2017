<?php

namespace CruiseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * RoomDiscount
 *
 * @ORM\Table(name="room_discount")
 * @ORM\Entity(repositoryClass="CruiseBundle\Repository\RoomDiscountRepository")
 */
class RoomDiscount
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
	 * @ORM\ManyToOne(targetEntity="Cruise", inversedBy="roomDiscount")
	 */
	private $cruise;	
	
	/**
	 * @ORM\ManyToOne(targetEntity="ShipRoom", inversedBy="roomDiscount")
	 */
	private $room;
	
	


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
     * Set cruise
     *
     * @param \CruiseBundle\Entity\Cruise $cruise
     *
     * @return RoomDiscount
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
     * Set room
     *
     * @param \CruiseBundle\Entity\ShipRoom $room
     *
     * @return RoomDiscount
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
}

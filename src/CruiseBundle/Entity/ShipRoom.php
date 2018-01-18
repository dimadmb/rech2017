<?php

namespace CruiseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ShipRoom
 *
 * @ORM\Table(name="ship_room")
 * @ORM\Entity(repositoryClass="CruiseBundle\Repository\ShipRoomRepository")
 */
class ShipRoom
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
     * @ORM\Column(name="number", type="string", length=255)
     */
    private $number;

    /**
     * @ORM\ManyToOne(targetEntity="ShipCabin", inversedBy="rooms")
     * @ORM\JoinColumn( onDelete="CASCADE" )		 
     */
    private $cabin;
	
    /**
     * @var int
     *
     * @ORM\Column(name="count_pass", type="integer", nullable=true)
     */		
	private $countPass;
	
    /**
     * @var int
     *
     * @ORM\Column(name="count_pass_max", type="integer", nullable=true)
     */	
	private $countPassMax;

	
	/**
	 * @ORM\OneToMany(targetEntity="RoomDiscount", mappedBy="room")
	 */
	private $roomDiscount; 


	public function __toString()
	{
		return $this->number;
	}
	
	
    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set number
     *
     * @param string $number
     *
     * @return ShipRoom
     */
    public function setNumber($number)
    {
        $this->number = $number;

        return $this;
    }

    /**
     * Get number
     *
     * @return string
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * Set cabin
     *
     * @param \CruiseBundle\Entity\ShipCabin $cabin
     *
     * @return ShipRoom
     */
    public function setCabin(\CruiseBundle\Entity\ShipCabin $cabin = null)
    {
        $this->cabin = $cabin;

        return $this;
    }

    /**
     * Get cabin
     *
     * @return \CruiseBundle\Entity\ShipCabin
     */
    public function getCabin()
    {
        return $this->cabin;
    }

    /**
     * Set countPass
     *
     * @param integer $countPass
     *
     * @return ShipRoom
     */
    public function setCountPass($countPass)
    {
        $this->countPass = $countPass;

        return $this;
    }

    /**
     * Get countPass
     *
     * @return integer
     */
    public function getCountPass()
    {
        return $this->countPass;
    }

    /**
     * Set countPassMax
     *
     * @param integer $countPassMax
     *
     * @return ShipRoom
     */
    public function setCountPassMax($countPassMax)
    {
        $this->countPassMax = $countPassMax;

        return $this;
    }

    /**
     * Get countPassMax
     *
     * @return integer
     */
    public function getCountPassMax()
    {
        return $this->countPassMax;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->roomDiscount = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add roomDiscount
     *
     * @param \CruiseBundle\Entity\RoomDiscount $roomDiscount
     *
     * @return ShipRoom
     */
    public function addRoomDiscount(\CruiseBundle\Entity\RoomDiscount $roomDiscount)
    {
        $this->roomDiscount[] = $roomDiscount;

        return $this;
    }

    /**
     * Remove roomDiscount
     *
     * @param \CruiseBundle\Entity\RoomDiscount $roomDiscount
     */
    public function removeRoomDiscount(\CruiseBundle\Entity\RoomDiscount $roomDiscount)
    {
        $this->roomDiscount->removeElement($roomDiscount);
    }

    /**
     * Get roomDiscount
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getRoomDiscount()
    {
        return $this->roomDiscount;
    }
}

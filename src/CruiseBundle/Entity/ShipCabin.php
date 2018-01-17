<?php

namespace CruiseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ShipCabin
 *
 * @ORM\Table(name="ship_cabin")
 * @ORM\Entity(repositoryClass="CruiseBundle\Repository\ShipCabinRepository")
 */
class ShipCabin
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
     * @ORM\ManyToOne(targetEntity="Ship", inversedBy="cabin")
	 * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $ship;




	/**
	 * @ORM\OneToMany(targetEntity="ShipRoom", mappedBy="cabin")
	 */
	private $rooms;

	/**
	 * @ORM\OneToMany(targetEntity="Price", mappedBy="cabin")
	 */
	private $prices;
	

    /**
     * @ORM\ManyToOne(targetEntity="ShipCabinType")
     */
    private $type;		

    /**
     * @ORM\ManyToOne(targetEntity="ShipCabinPlace")
     */
    private $placeCount;	
	
	
	/**
	 * @ORM\ManyToOne(targetEntity="ShipDeck")
	 */
	private $deck;
	
	/**
	 * @ORM\ManyToMany(targetEntity="BaseBundle\Entity\Image")
	 * @ORM\OrderBy({"sort" = "ASC", "id" = "ASC"})
	 * @ORM\JoinTable(name="ship_cabin_image", joinColumns={@ORM\JoinColumn(name="ship_cabin_id",
		referencedColumnName="id", onDelete="CASCADE")},
	 * inverseJoinColumns={@ORM\JoinColumn(name="image_id",
	referencedColumnName="id", onDelete="CASCADE")})
	 */	
    private $images;
	

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->rooms = new \Doctrine\Common\Collections\ArrayCollection();
        $this->prices = new \Doctrine\Common\Collections\ArrayCollection();
        $this->images = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set ship
     *
     * @param \CruiseBundle\Entity\Ship $ship
     *
     * @return ShipCabin
     */
    public function setShip(\CruiseBundle\Entity\Ship $ship = null)
    {
        $this->ship = $ship;

        return $this;
    }

    /**
     * Get ship
     *
     * @return \CruiseBundle\Entity\Ship
     */
    public function getShip()
    {
        return $this->ship;
    }

    /**
     * Add room
     *
     * @param \CruiseBundle\Entity\ShipRoom $room
     *
     * @return ShipCabin
     */
    public function addRoom(\CruiseBundle\Entity\ShipRoom $room)
    {
        $this->rooms[] = $room;

        return $this;
    }

    /**
     * Remove room
     *
     * @param \CruiseBundle\Entity\ShipRoom $room
     */
    public function removeRoom(\CruiseBundle\Entity\ShipRoom $room)
    {
        $this->rooms->removeElement($room);
    }

    /**
     * Get rooms
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getRooms()
    {
        return $this->rooms;
    }

    /**
     * Add price
     *
     * @param \CruiseBundle\Entity\Price $price
     *
     * @return ShipCabin
     */
    public function addPrice(\CruiseBundle\Entity\Price $price)
    {
        $this->prices[] = $price;

        return $this;
    }

    /**
     * Remove price
     *
     * @param \CruiseBundle\Entity\Price $price
     */
    public function removePrice(\CruiseBundle\Entity\Price $price)
    {
        $this->prices->removeElement($price);
    }

    /**
     * Get prices
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPrices()
    {
        return $this->prices;
    }

    /**
     * Set type
     *
     * @param \CruiseBundle\Entity\ShipCabinType $type
     *
     * @return ShipCabin
     */
    public function setType(\CruiseBundle\Entity\ShipCabinType $type = null)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return \CruiseBundle\Entity\ShipCabinType
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set deck
     *
     * @param \CruiseBundle\Entity\ShipDeck $deck
     *
     * @return ShipCabin
     */
    public function setDeck(\CruiseBundle\Entity\ShipDeck $deck = null)
    {
        $this->deck = $deck;

        return $this;
    }

    /**
     * Get deck
     *
     * @return \CruiseBundle\Entity\ShipDeck
     */
    public function getDeck()
    {
        return $this->deck;
    }



    /**
     * Add image
     *
     * @param \BaseBundle\Entity\Image $image
     *
     * @return ShipCabin
     */
    public function addImage(\BaseBundle\Entity\Image $image)
    {
        $this->images[] = $image;

        return $this;
    }

    /**
     * Remove image
     *
     * @param \BaseBundle\Entity\Image $image
     */
    public function removeImage(\BaseBundle\Entity\Image $image)
    {
        $this->images->removeElement($image);
    }
    /**
     * Remove images
     *
     * @param \BaseBundle\Entity\Image $images
     */
    public function removeAllImages()
    {
        $this->images = [];
    }
    /**
     * Get images
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getImages()
    {
        return $this->images;
    }

    /**
     * Set placeCount
     *
     * @param \CruiseBundle\Entity\ShipCabinPlace $placeCount
     *
     * @return ShipCabin
     */
    public function setPlaceCount(\CruiseBundle\Entity\ShipCabinPlace $placeCount = null)
    {
        $this->placeCount = $placeCount;

        return $this;
    }

    /**
     * Get placeCount
     *
     * @return \CruiseBundle\Entity\ShipCabinPlace
     */
    public function getPlaceCount()
    {
        return $this->placeCount;
    }
}

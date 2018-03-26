<?php

namespace CruiseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Price
 *
 * @ORM\Table(name="price")
 * @ORM\Entity(repositoryClass="CruiseBundle\Repository\PriceRepository")
 */
class Price
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
     * @ORM\ManyToOne(targetEntity="ShipCabinPlace")
     */
    private $place;

    /**
     * @ORM\ManyToOne(targetEntity="ShipCabin", inversedBy="prices")
	 * @ORM\JoinColumn(onDelete="CASCADE")	 
     */
    private $cabin;

    /**
     * @var int
     *
     * @ORM\Column(name="cruise_id", type="integer")
     */
    private $cruiseId;

    /**
     * @ORM\ManyToOne(targetEntity="Tariff", inversedBy="price")
     */
    private $tariff;

    /**
     * @ORM\ManyToOne(targetEntity="Meals")
     */
    private $meals;

    /**
     * @var string
     *
     * @ORM\Column(name="price", type="decimal", precision=10, scale=2)
     */
    private $price;
	
	/**
	 * @ORM\ManyToOne(targetEntity="Cruise", inversedBy="prices")
     * @ORM\JoinColumn( onDelete="CASCADE" )	 
	 */
	private $cruise; 



	public function __toString()
	{
		return $this->tariff->getName()." ".$this->meals->getName()." " .number_format($this->getPrice() , 0 , " ", " ");
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
     * Set cruiseId
     *
     * @param integer $cruiseId
     *
     * @return Price
     */
    public function setCruiseId($cruiseId)
    {
        $this->cruiseId = $cruiseId;

        return $this;
    }

    /**
     * Get cruiseId
     *
     * @return integer
     */
    public function getCruiseId()
    {
        return $this->cruiseId;
    }

    /**
     * Set price
     *
     * @param string $price
     *
     * @return Price
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price
     *
     * @return string
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set place
     *
     * @param \CruiseBundle\Entity\ShipCabinPlace $place
     *
     * @return Price
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
     * Set cabin
     *
     * @param \CruiseBundle\Entity\ShipCabin $cabin
     *
     * @return Price
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
     * Set tariff
     *
     * @param \CruiseBundle\Entity\Tariff $tariff
     *
     * @return Price
     */
    public function setTariff(\CruiseBundle\Entity\Tariff $tariff = null)
    {
        $this->tariff = $tariff;

        return $this;
    }

    /**
     * Get tariff
     *
     * @return \CruiseBundle\Entity\Tariff
     */
    public function getTariff()
    {
        return $this->tariff;
    }

    /**
     * Set meals
     *
     * @param \CruiseBundle\Entity\Meals $meals
     *
     * @return Price
     */
    public function setMeals(\CruiseBundle\Entity\Meals $meals = null)
    {
        $this->meals = $meals;

        return $this;
    }

    /**
     * Get meals
     *
     * @return \CruiseBundle\Entity\Meals
     */
    public function getMeals()
    {
        return $this->meals;
    }

    /**
     * Set cruise
     *
     * @param \CruiseBundle\Entity\Cruise $cruise
     *
     * @return Price
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
}

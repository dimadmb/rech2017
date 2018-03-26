<?php

namespace CruiseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ShipCabinPlace
 *
 * @ORM\Table(name="ship_cabin_place")
 * @ORM\Entity(repositoryClass="CruiseBundle\Repository\ShipCabinPlaceRepository")
 */
class ShipCabinPlace
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
     * @var int
     *
     * @ORM\Column(name="rp_id", type="integer")
     */
    private $rpId;

    /**
     * @var string
     *
     * @ORM\Column(name="rp_name", type="string", length=255)
     */
    private $rpName;



	public function __toString()
	{
		return $this->rpName;
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
     * Set rpId
     *
     * @param integer $rpId
     *
     * @return ShipCabinPlace
     */
    public function setRpId($rpId)
    {
        $this->rpId = $rpId;

        return $this;
    }

    /**
     * Get rpId
     *
     * @return integer
     */
    public function getRpId()
    {
        return $this->rpId;
    }

    /**
     * Set rpName
     *
     * @param string $rpName
     *
     * @return ShipCabinPlace
     */
    public function setRpName($rpName)
    {
        $this->rpName = $rpName;

        return $this;
    }

    /**
     * Get rpName
     *
     * @return string
     */
    public function getRpName()
    {
        return $this->rpName;
    }
}

<?php

namespace CruiseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ShipCabinType
 *
 * @ORM\Table(name="ship_cabin_type")
 * @ORM\Entity(repositoryClass="CruiseBundle\Repository\ShipCabinTypeRepository")
 */
class ShipCabinType
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
     * @ORM\Column(name="rt_id", type="integer", nullable=true)
     */
    private $rtId;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="comment", type="string", length=255)
     */
    private $comment;

    /**
     * @var int
     *
     * @ORM\Column(name="place_count_max", type="integer", nullable=true)
     */
    private $placeCountMax;
	 




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
     * Set rtId
     *
     * @param integer $rtId
     *
     * @return ShipCabinType
     */
    public function setRtId($rtId)
    {
        $this->rtId = $rtId;

        return $this;
    }

    /**
     * Get rtId
     *
     * @return integer
     */
    public function getRtId()
    {
        return $this->rtId;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return ShipCabinType
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set comment
     *
     * @param string $comment
     *
     * @return ShipCabinType
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
     * Set placeCountMax
     *
     * @param integer $placeCountMax
     *
     * @return ShipCabinType
     */
    public function setPlaceCountMax($placeCountMax)
    {
        $this->placeCountMax = $placeCountMax;

        return $this;
    }

    /**
     * Get placeCountMax
     *
     * @return integer
     */
    public function getPlaceCountMax()
    {
        return $this->placeCountMax;
    }
}

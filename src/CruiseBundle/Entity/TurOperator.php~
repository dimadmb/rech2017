<?php

namespace CruiseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TurOperator
 *
 * @ORM\Table(name="tur_operator")
 * @ORM\Entity(repositoryClass="CruiseBundle\Repository\TurOperatorRepository")
 */
class TurOperator
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
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;
	
	
    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=255)
     */
    private $code;

    /**
     * @var bool
     *
     * @ORM\Column(name="inSale", type="boolean")
     */
    private $inSale;
	
	
	/**
	 * @ORM\OneToMany(targetEntity="Ship", mappedBy="turOperator")
	 */
	private $ships;



    /**
     * Constructor
     */
    public function __construct()
    {
        $this->ships = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set name
     *
     * @param string $name
     *
     * @return TurOperator
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
     * Set inSale
     *
     * @param boolean $inSale
     *
     * @return TurOperator
     */
    public function setInSale($inSale)
    {
        $this->inSale = $inSale;

        return $this;
    }

    /**
     * Get inSale
     *
     * @return boolean
     */
    public function getInSale()
    {
        return $this->inSale;
    }

    /**
     * Add ship
     *
     * @param \CruiseBundle\Entity\Ship $ship
     *
     * @return TurOperator
     */
    public function addShip(\CruiseBundle\Entity\Ship $ship)
    {
        $this->ships[] = $ship;

        return $this;
    }

    /**
     * Remove ship
     *
     * @param \CruiseBundle\Entity\Ship $ship
     */
    public function removeShip(\CruiseBundle\Entity\Ship $ship)
    {
        $this->ships->removeElement($ship);
    }

    /**
     * Get ships
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getShips()
    {
        return $this->ships;
    }

    /**
     * Set code
     *
     * @param string $code
     *
     * @return TurOperator
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }
}

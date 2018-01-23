<?php

namespace CruiseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * TypePlace
 *
 * @ORM\Table(name="type_place")
 * @ORM\Entity(repositoryClass="CruiseBundle\Repository\TypePlaceRepository")
 */
class TypePlace
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
     * @ORM\Column(name="nds", type="boolean")
     */
    private $nds;

    /**
     * @var bool
     *
     * @ORM\Column(name="price", type="boolean")
     */
    private $price;


	public function __toString()
	{
		return $this->name;
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
     * Set name
     *
     * @param string $name
     *
     * @return TypePlace
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
     * Set code
     *
     * @param string $code
     *
     * @return TypePlace
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

    /**
     * Set nds
     *
     * @param boolean $nds
     *
     * @return TypePlace
     */
    public function setNds($nds)
    {
        $this->nds = $nds;

        return $this;
    }

    /**
     * Get nds
     *
     * @return bool
     */
    public function getNds()
    {
        return $this->nds;
    }

    /**
     * Set price
     *
     * @param boolean $price
     *
     * @return TypePlace
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price
     *
     * @return bool
     */
    public function getPrice()
    {
        return $this->price;
    }
}

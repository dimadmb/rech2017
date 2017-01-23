<?php

namespace CruiseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Cruise
 *
 * @ORM\Table(name="cruise")
 * @ORM\Entity(repositoryClass="CruiseBundle\Repository\CruiseRepository")
 */
class Cruise
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $id;
	
	/**
	 * @ORM\ManyToMany(targetEntity="Category" )
	 */
	private $category;

    /**
     * @var int
     *
     * @ORM\Column(name="code", type="integer", unique=true)
     */
    private $code;

    /**
	 * @ORM\ManyToOne(targetEntity="Ship", inversedBy="cruises")
	 * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $ship;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var \Date
     *
     * @ORM\Column(name="startDate", type="date")
     */
    private $startDate;

    /**
     * @var \Date
     *
     * @ORM\Column(name="endDate", type="date")
     */
    private $endDate;

    /**
     * @var int
     *
     * @ORM\Column(name="dayCount", type="integer")
     */
    private $dayCount;
	
	
	/**
	 * @ORM\OneToMany(targetEntity="Price", mappedBy="cruise")
	 */
	private $prices;

	
	/**
	 * @ORM\OneToMany(targetEntity="ProgramItem", mappedBy="cruise")
	 */
	private $programs;
	
	/**
     * @ORM\ManyToOne(targetEntity="TurOperator")
     */
    private $turOperator;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->category = new \Doctrine\Common\Collections\ArrayCollection();
        $this->prices = new \Doctrine\Common\Collections\ArrayCollection();
        $this->programs = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set id
     *
     * @param integer $id
     *
     * @return Cruise
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
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
     * Set code
     *
     * @param integer $code
     *
     * @return Cruise
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return integer
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Cruise
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
     * Set startDate
     *
     * @param \DateTime $startDate
     *
     * @return Cruise
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;

        return $this;
    }

    /**
     * Get startDate
     *
     * @return \DateTime
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * Set endDate
     *
     * @param \DateTime $endDate
     *
     * @return Cruise
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;

        return $this;
    }

    /**
     * Get endDate
     *
     * @return \DateTime
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * Set dayCount
     *
     * @param integer $dayCount
     *
     * @return Cruise
     */
    public function setDayCount($dayCount)
    {
        $this->dayCount = $dayCount;

        return $this;
    }

    /**
     * Get dayCount
     *
     * @return integer
     */
    public function getDayCount()
    {
        return $this->dayCount;
    }

    /**
     * Add category
     *
     * @param \CruiseBundle\Entity\Category $category
     *
     * @return Cruise
     */
    public function addCategory(\CruiseBundle\Entity\Category $category)
    {
        $this->category[] = $category;

        return $this;
    }

    /**
     * Remove category
     *
     * @param \CruiseBundle\Entity\Category $category
     */
    public function removeCategory(\CruiseBundle\Entity\Category $category)
    {
        $this->category->removeElement($category);
    }

    /**
     * Get category
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Set ship
     *
     * @param \CruiseBundle\Entity\Ship $ship
     *
     * @return Cruise
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
     * Add price
     *
     * @param \CruiseBundle\Entity\Price $price
     *
     * @return Cruise
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
     * Add program
     *
     * @param \CruiseBundle\Entity\ProgramItem $program
     *
     * @return Cruise
     */
    public function addProgram(\CruiseBundle\Entity\ProgramItem $program)
    {
        $this->programs[] = $program;

        return $this;
    }

    /**
     * Remove program
     *
     * @param \CruiseBundle\Entity\ProgramItem $program
     */
    public function removeProgram(\CruiseBundle\Entity\ProgramItem $program)
    {
        $this->programs->removeElement($program);
    }

    /**
     * Get programs
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPrograms()
    {
        return $this->programs;
    }

    /**
     * Set turOperator
     *
     * @param \CruiseBundle\Entity\TurOperator $turOperator
     *
     * @return Cruise
     */
    public function setTurOperator(\CruiseBundle\Entity\TurOperator $turOperator = null)
    {
        $this->turOperator = $turOperator;

        return $this;
    }

    /**
     * Get turOperator
     *
     * @return \CruiseBundle\Entity\TurOperator
     */
    public function getTurOperator()
    {
        return $this->turOperator;
    }
}

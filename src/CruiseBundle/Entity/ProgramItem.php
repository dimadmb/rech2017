<?php

namespace CruiseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ProgramItem
 *
 * @ORM\Table(name="program_item")
 * @ORM\Entity(repositoryClass="CruiseBundle\Repository\ProgramItemRepository")
 */
class ProgramItem
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
	 * @ORM\ManyToOne(targetEntity="Cruise", inversedBy="programs")
	 * @ORM\JoinColumn(onDelete="CASCADE")	 
	 */
	private $cruise;	
	
	
	/**
	 * @ORM\ManyToOne(targetEntity="Place")
	 */
	private $place;
	

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_start", type="datetime")
     */
    private $dateStart;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_stop", type="datetime")
     */
    private $dateStop;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text")
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="place_title", type="string", length=255)
     */
    private $placeTitle;



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
     * Set dateStart
     *
     * @param \DateTime $dateStart
     *
     * @return ProgramItem
     */
    public function setDateStart($dateStart)
    {
        $this->dateStart = $dateStart;

        return $this;
    }

    /**
     * Get dateStart
     *
     * @return \DateTime
     */
    public function getDateStart()
    {
        return $this->dateStart;
    }

    /**
     * Set dateStop
     *
     * @param \DateTime $dateStop
     *
     * @return ProgramItem
     */
    public function setDateStop($dateStop)
    {
        $this->dateStop = $dateStop;

        return $this;
    }

    /**
     * Get dateStop
     *
     * @return \DateTime
     */
    public function getDateStop()
    {
        return $this->dateStop;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return ProgramItem
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set placeTitle
     *
     * @param string $placeTitle
     *
     * @return ProgramItem
     */
    public function setPlaceTitle($placeTitle)
    {
        $this->placeTitle = $placeTitle;

        return $this;
    }

    /**
     * Get placeTitle
     *
     * @return string
     */
    public function getPlaceTitle()
    {
        return $this->placeTitle;
    }

    /**
     * Set cruise
     *
     * @param \CruiseBundle\Entity\Cruise $cruise
     *
     * @return ProgramItem
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
     * Set place
     *
     * @param \CruiseBundle\Entity\Place $place
     *
     * @return ProgramItem
     */
    public function setPlace(\CruiseBundle\Entity\Place $place = null)
    {
        $this->place = $place;

        return $this;
    }

    /**
     * Get place
     *
     * @return \CruiseBundle\Entity\Place
     */
    public function getPlace()
    {
        return $this->place;
    }
}

<?php

namespace CruiseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ShipDeck
 *
 * @ORM\Table(name="ship_deck")
 * @ORM\Entity(repositoryClass="CruiseBundle\Repository\ShipDeckRepository")
 */
class ShipDeck
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
     * @var int
     *
     * @ORM\Column(name="deck_id", type="integer")
     */
    private $deckId;
	




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
     * @return ShipDeck
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
     * Set deckId
     *
     * @param integer $deckId
     *
     * @return ShipDeck
     */
    public function setDeckId($deckId)
    {
        $this->deckId = $deckId;

        return $this;
    }

    /**
     * Get deckId
     *
     * @return integer
     */
    public function getDeckId()
    {
        return $this->deckId;
    }
}

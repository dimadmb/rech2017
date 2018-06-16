<?php

namespace CruiseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Buyer
 *
 * @ORM\Table(name="buyer")
 * @ORM\Entity(repositoryClass="CruiseBundle\Repository\BuyerRepository")
 */
class Buyer
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
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="last_name", type="string", length=255, nullable=true)
     */
    private $lastName;

    /**
     * @var string
     *
     * @ORM\Column(name="father_name", type="string", length=255, nullable=true)
     */
    private $fatherName;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="birthday", type="date", nullable=true)
     */
    private $birthday;

    /**
     * @var string
     *
     * @ORM\Column(name="pass_seria", type="string", length=20, nullable=true)
     */
    private $passSeria;

    /**
     * @var string
     *
     * @ORM\Column(name="pass_num", type="string", length=20, nullable=true)
     */
    private $passNum;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="pass_date", type="date", nullable=true)
     */
    private $passDate;

    /**
     * @var string
     *
     * @ORM\Column(name="pass_who", type="string", length=255, nullable=true)
     */
    private $passWho;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime", nullable=true)
     */
    private $created;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated", type="datetime", nullable=true)
     */
    private $updated;

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=255, nullable=true)
     */
    private $phone;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255, nullable=true)
     */
    private $email;
	
	/**
	 * @ORM\OneToMany(targetEntity="Ordering", mappedBy="buyer")
	 */
	private $orders;
	
	
    /**
     * @var string
     *
     * @ORM\Column(name="address", type="string", length=255, nullable=true)
     */
    private $address;	
	

	
	public function __toString()
	{
		return $this->lastName.' '. $this->name.' '. $this->fatherName;
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
     * @return Buyer
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
     * Set lastName
     *
     * @param string $lastName
     *
     * @return Buyer
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * Get lastName
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * Set fatherName
     *
     * @param string $fatherName
     *
     * @return Buyer
     */
    public function setFatherName($fatherName)
    {
        $this->fatherName = $fatherName;

        return $this;
    }

    /**
     * Get fatherName
     *
     * @return string
     */
    public function getFatherName()
    {
        return $this->fatherName;
    }

    /**
     * Set birthday
     *
     * @param \DateTime $birthday
     *
     * @return Buyer
     */
    public function setBirthday($birthday)
    {
        $this->birthday = $birthday;

        return $this;
    }

    /**
     * Get birthday
     *
     * @return \DateTime
     */
    public function getBirthday()
    {
        return $this->birthday;
    }

    /**
     * Set passSeria
     *
     * @param string $passSeria
     *
     * @return Buyer
     */
    public function setPassSeria($passSeria)
    {
        $this->passSeria = $passSeria;

        return $this;
    }

    /**
     * Get passSeria
     *
     * @return string
     */
    public function getPassSeria()
    {
        return $this->passSeria;
    }

    /**
     * Set passNum
     *
     * @param string $passNum
     *
     * @return Buyer
     */
    public function setPassNum($passNum)
    {
        $this->passNum = $passNum;

        return $this;
    }

    /**
     * Get passNum
     *
     * @return string
     */
    public function getPassNum()
    {
        return $this->passNum;
    }

    /**
     * Set passDate
     *
     * @param \DateTime $passDate
     *
     * @return Buyer
     */
    public function setPassDate($passDate)
    {
        $this->passDate = $passDate;

        return $this;
    }

    /**
     * Get passDate
     *
     * @return \DateTime
     */
    public function getPassDate()
    {
        return $this->passDate;
    }

    /**
     * Set passWho
     *
     * @param string $passWho
     *
     * @return Buyer
     */
    public function setPassWho($passWho)
    {
        $this->passWho = $passWho;

        return $this;
    }

    /**
     * Get passWho
     *
     * @return string
     */
    public function getPassWho()
    {
        return $this->passWho;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     *
     * @return Buyer
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set updated
     *
     * @param \DateTime $updated
     *
     * @return Buyer
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Get updated
     *
     * @return \DateTime
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Set phone
     *
     * @param string $phone
     *
     * @return Buyer
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get phone
     *
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return Buyer
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->orders = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add order
     *
     * @param \CruiseBundle\Entity\Ordering $order
     *
     * @return Buyer
     */
    public function addOrder(\CruiseBundle\Entity\Ordering $order)
    {
        $this->orders[] = $order;

        return $this;
    }

    /**
     * Remove order
     *
     * @param \CruiseBundle\Entity\Ordering $order
     */
    public function removeOrder(\CruiseBundle\Entity\Ordering $order)
    {
        $this->orders->removeElement($order);
    }

    /**
     * Get orders
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getOrders()
    {
        return $this->orders;
    }

    /**
     * Set address
     *
     * @param string $address
     *
     * @return Buyer
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get address
     *
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }
}

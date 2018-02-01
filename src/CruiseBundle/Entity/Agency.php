<?php

namespace CruiseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Agency
 *
 * @ORM\Table(name="agency")
 * @ORM\Entity(repositoryClass="CruiseBundle\Repository\AgencyRepository")
 */
class Agency
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
     * @ORM\Column(name="bank_name", type="string", length=255, nullable=true)
     */
    private $bankName;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="rs", type="string", length=255, nullable=true)
     */
    private $rs;

    /**
     * @var string
     *
     * @ORM\Column(name="ks", type="string", length=255, nullable=true)
     */
    private $ks;

    /**
     * @var string
     *
     * @ORM\Column(name="bik", type="string", length=255, nullable=true)
     */
    private $bik;

    /**
     * @var string
     *
     * @ORM\Column(name="inn", type="string", length=255, nullable=true)
     */
    private $inn;

    /**
     * @var string
     *
     * @ORM\Column(name="kpp", type="string", length=255, nullable=true)
     */
    private $kpp;

    /**
     * @var string
     *
     * @ORM\Column(name="ur_address", type="string", length=255, nullable=true)
     */
    private $urAddress;

    /**
     * @var string
     *
     * @ORM\Column(name="fakt_address", type="string", length=255, nullable=true)
     */
    private $faktAddress;

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
     * @var string
     *
     * @ORM\Column(name="fee", type="decimal", precision=10, scale=2, nullable=true)
     */
    private $fee;

    /**
     * @var int
     *
     * @ORM\Column(name="num_dog", type="integer", nullable=true)
     */
    private $numDog;

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
     * @ORM\Column(name="short_name", type="string", length=255, nullable=true)
     */
    private $shortName;
	
	
	/**
	 * @ORM\OneToMany(targetEntity="BaseBundle\Entity\User", mappedBy="agency")
	 */
	private $users;
	
	
	/**
	 * @ORM\OneToMany(targetEntity="Ordering", mappedBy="agency")
	 */
	private $orders;
	
	
	/**
	 * @ORM\ManyToOne(targetEntity="Region")
	 */
	private $region;
	
	
	
    /**
     * @var string
     *
     * @ORM\Column(name="auth", type="string", length=255, nullable=true)
     */
    private $auth;


    /**
     * @var bool
     *
     * @ORM\Column(name="active", type="boolean")
     */
    private $active = true;
	
	
	
	
	public function __toString()
	{
		return (string)$this->id ." ". $this->name;
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
     * Set bankName
     *
     * @param string $bankName
     *
     * @return Agency
     */
    public function setBankName($bankName)
    {
        $this->bankName = $bankName;

        return $this;
    }

    /**
     * Get bankName
     *
     * @return string
     */
    public function getBankName()
    {
        return $this->bankName;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Agency
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
     * Set rs
     *
     * @param string $rs
     *
     * @return Agency
     */
    public function setRs($rs)
    {
        $this->rs = $rs;

        return $this;
    }

    /**
     * Get rs
     *
     * @return string
     */
    public function getRs()
    {
        return $this->rs;
    }

    /**
     * Set ks
     *
     * @param string $ks
     *
     * @return Agency
     */
    public function setKs($ks)
    {
        $this->ks = $ks;

        return $this;
    }

    /**
     * Get ks
     *
     * @return string
     */
    public function getKs()
    {
        return $this->ks;
    }

    /**
     * Set bik
     *
     * @param string $bik
     *
     * @return Agency
     */
    public function setBik($bik)
    {
        $this->bik = $bik;

        return $this;
    }

    /**
     * Get bik
     *
     * @return string
     */
    public function getBik()
    {
        return $this->bik;
    }

    /**
     * Set inn
     *
     * @param string $inn
     *
     * @return Agency
     */
    public function setInn($inn)
    {
        $this->inn = $inn;

        return $this;
    }

    /**
     * Get inn
     *
     * @return string
     */
    public function getInn()
    {
        return $this->inn;
    }

    /**
     * Set kpp
     *
     * @param string $kpp
     *
     * @return Agency
     */
    public function setKpp($kpp)
    {
        $this->kpp = $kpp;

        return $this;
    }

    /**
     * Get kpp
     *
     * @return string
     */
    public function getKpp()
    {
        return $this->kpp;
    }

    /**
     * Set urAddress
     *
     * @param string $urAddress
     *
     * @return Agency
     */
    public function setUrAddress($urAddress)
    {
        $this->urAddress = $urAddress;

        return $this;
    }

    /**
     * Get urAddress
     *
     * @return string
     */
    public function getUrAddress()
    {
        return $this->urAddress;
    }

    /**
     * Set faktAddress
     *
     * @param string $faktAddress
     *
     * @return Agency
     */
    public function setFaktAddress($faktAddress)
    {
        $this->faktAddress = $faktAddress;

        return $this;
    }

    /**
     * Get faktAddress
     *
     * @return string
     */
    public function getFaktAddress()
    {
        return $this->faktAddress;
    }

    /**
     * Set phone
     *
     * @param string $phone
     *
     * @return Agency
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
     * Set fee
     *
     * @param string $fee
     *
     * @return Agency
     */
    public function setFee($fee)
    {
        $this->fee = $fee;

        return $this;
    }

    /**
     * Get fee
     *
     * @return string
     */
    public function getFee()
    {
        return $this->fee;
    }

    /**
     * Set numDog
     *
     * @param integer $numDog
     *
     * @return Agency
     */
    public function setNumDog($numDog)
    {
        $this->numDog = $numDog;

        return $this;
    }

    /**
     * Get numDog
     *
     * @return int
     */
    public function getNumDog()
    {
        return $this->numDog;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     *
     * @return Agency
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
     * @return Agency
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
     * Set shortName
     *
     * @param string $shortName
     *
     * @return Agency
     */
    public function setShortName($shortName)
    {
        $this->shortName = $shortName;

        return $this;
    }

    /**
     * Get shortName
     *
     * @return string
     */
    public function getShortName()
    {
        return $this->shortName;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->users = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add user
     *
     * @param \BaseBundle\Entity\User $user
     *
     * @return Agency
     */
    public function addUser(\BaseBundle\Entity\User $user)
    {
        $this->users[] = $user;

        return $this;
    }

    /**
     * Remove user
     *
     * @param \BaseBundle\Entity\User $user
     */
    public function removeUser(\BaseBundle\Entity\User $user)
    {
        $this->users->removeElement($user);
    }

    /**
     * Get users
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * Add order
     *
     * @param \CruiseBundle\Entity\Ordering $order
     *
     * @return Agency
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
     * Set auth
     *
     * @param string $auth
     *
     * @return Agency
     */
    public function setAuth($auth)
    {
        $this->auth = $auth;

        return $this;
    }

    /**
     * Get auth
     *
     * @return string
     */
    public function getAuth()
    {
        return $this->auth;
    }

    /**
     * Set active
     *
     * @param boolean $active
     *
     * @return Agency
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get active
     *
     * @return boolean
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Set region
     *
     * @param \CruiseBundle\Entity\Region $region
     *
     * @return Agency
     */
    public function setRegion(\CruiseBundle\Entity\Region $region = null)
    {
        $this->region = $region;

        return $this;
    }

    /**
     * Get region
     *
     * @return \CruiseBundle\Entity\Region
     */
    public function getRegion()
    {
        return $this->region;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return Agency
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
}

<?php

namespace BaseBundle\Entity;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="user")
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
	
    /**
     * @var string
     *
     * @ORM\Column(name="first_name", type="string", length=255, nullable=true)
     */
    private $firstName;		
	
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
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=255, nullable=true)
     */
    private $phone;	
	
	
	/**
	 * @ORM\OneToMany(targetEntity="CruiseBundle\Entity\Ordering", mappedBy="user")
	 */
	private $orders;
	
	
	/**
	 * @ORM\ManyToOne(targetEntity="CruiseBundle\Entity\Agency", inversedBy="users")
	 */
	private $agency;
	
	

    public function __construct()
    {
        parent::__construct();
        // your own logic
		
		$this->salt = "";
    }
	


    /**
     * Set firstName
     *
     * @param string $firstName
     *
     * @return User
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * Get firstName
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Set lastName
     *
     * @param string $lastName
     *
     * @return User
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
     * @return User
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
     * Add order
     *
     * @param \CruiseBundle\Entity\Ordering $order
     *
     * @return User
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
     * Set agency
     *
     * @param \CruiseBundle\Entity\Agency $agency
     *
     * @return User
     */
    public function setAgency(\CruiseBundle\Entity\Agency $agency = null)
    {
        $this->agency = $agency;

        return $this;
    }

    /**
     * Get agency
     *
     * @return \CruiseBundle\Entity\Agency
     */
    public function getAgency()
    {
        return $this->agency;
    }

    /**
     * Set phone
     *
     * @param string $phone
     *
     * @return User
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
}

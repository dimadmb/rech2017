<?php

namespace CruiseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * OrderItemPlace
 *
 * @ORM\Table(name="order_item_place")
 * @ORM\Entity(repositoryClass="CruiseBundle\Repository\OrderItemPlaceRepository")
 * @ORM\HasLifecycleCallbacks  
 */
class OrderItemPlace
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
	 * @ORM\ManyToOne(targetEntity="OrderItem" , inversedBy="orderItemPlaces")
	 * @ORM\JoinColumn(name="order_item_id", referencedColumnName="id", onDelete="CASCADE")	 
	 */
	private $orderItem;
	
	
    /**
     * @var string
     *
     * @ORM\Column(name="price_value", type="decimal", precision=10, scale=2, nullable=true)
     */
    private $priceValue;
	
	/**
	 * @ORM\ManyToOne(targetEntity="Price")
	 */
	private $price;
	
		
	/**
	 * @ORM\ManyToOne(targetEntity="TypeDoc")
	 */
	private $typeDoc;
	
    /**
     * @var string
     *
     * @ORM\Column(name="surcharge", type="decimal", precision=10, scale=2, nullable=true)
     */	
	private $surcharge;
	
	/**
	 * @ORM\ManyToOne(targetEntity="Gender")
	 */
	private $gender;
	
	
	private $idHash;
	


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
     * @return OrderItemPlace
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
     * @return OrderItemPlace
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
     * @return OrderItemPlace
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
     * @return OrderItemPlace
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
     * @return OrderItemPlace
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
     * @return OrderItemPlace
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
     * @return OrderItemPlace
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
     * @return OrderItemPlace
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
     * @return OrderItemPlace
	 * @ORM\PrePersist()	
     */
    public function setCreated()
    {
        $this->created = new \DateTime;

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
     * Set orderItem
     *
     * @param \CruiseBundle\Entity\OrderItem $orderItem
     *
     * @return OrderItemPlace
     */
    public function setOrderItem(\CruiseBundle\Entity\OrderItem $orderItem = null)
    {
        $this->orderItem = $orderItem;

        return $this;
    }

    /**
     * Get orderItem
     *
     * @return \CruiseBundle\Entity\OrderItem
     */
    public function getOrderItem()
    {
        return $this->orderItem;
    }

    /**
     * Set updated
     *
     * @param \DateTime $updated
     *
     * @return OrderItemPlace
	 * @ORM\PreUpdate()	 
     */
    public function setUpdated()
    {
        $this->updated = new \DateTime;

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
     * Set priceValue
     *
     * @param string $priceValue
     *
     * @return OrderItemPlace
	 * @ORM\PrePersist()	
	 * @ORM\PreUpdate()		 
     */
    public function setPriceValue()
    {
		$priceValue  = 0;
		
		if(($this->orderItem->getTypeDiscount() !== null) && ($this->price !== null))
		{
			$priceValue  = $this->price->getPrice() * (100 - $this->orderItem->getTypeDiscount()->getValue() ) / 100 ;
		}
		elseif($this->price !== null)
		{
			$priceValue  = $this->price->getPrice();
		}

		$this->priceValue = $priceValue;

        return $this;
    }

    /**
     * Get priceValue
     *
     * @return string
     */
    public function getPriceValue()
    {
        return $this->priceValue;
    }

    /**
     * Set price
     *
     * @param \CruiseBundle\Entity\Price $price
     *
     * @return OrderItemPlace
     */
    public function setPrice(\CruiseBundle\Entity\Price $price = null)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price
     *
     * @return \CruiseBundle\Entity\Price
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set typeDoc
     *
     * @param \CruiseBundle\Entity\TypeDoc $typeDoc
     *
     * @return OrderItemPlace
     */
    public function setTypeDoc(\CruiseBundle\Entity\TypeDoc $typeDoc = null)
    {
        $this->typeDoc = $typeDoc;

        return $this;
    }

    /**
     * Get typeDoc
     *
     * @return \CruiseBundle\Entity\TypeDoc
     */
    public function getTypeDoc()
    {
        return $this->typeDoc;
    }
	
	
	public function getIdHash()
	{
		$place_salt = 'c56CC5enUbrGKWn8x3FhXkZbHaMk6Ms7Mm3KdTHY';
		$hashids_place = new \Hashids\Hashids($place_salt,32);
		return $hashids_place->encode($this->id);		
	}	
	

    /**
     * Set surcharge
     *
     * @param string $surcharge
     *
     * @return OrderItemPlace
     */
    public function setSurcharge($surcharge)
    {
        $this->surcharge = $surcharge;

        return $this;
    }

    /**
     * Get surcharge
     *
     * @return string
     */
    public function getSurcharge()
    {
        return $this->surcharge;
    }

    /**
     * Set gender
     *
     * @param \CruiseBundle\Entity\Gender $gender
     *
     * @return OrderItemPlace
     */
    public function setGender(\CruiseBundle\Entity\Gender $gender = null)
    {
        $this->gender = $gender;

        return $this;
    }

    /**
     * Get gender
     *
     * @return \CruiseBundle\Entity\Gender
     */
    public function getGender()
    {
        return $this->gender;
    }
}

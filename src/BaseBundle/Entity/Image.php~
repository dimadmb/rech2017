<?php

namespace BaseBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;
//use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;

//use Imagine\Gd;
//use Imagine\Image\Box;
//use Imagine\Image\BoxInterface;


/**
 * Image
 *
 * @ORM\Table(name="image")
 * @ORM\Entity(repositoryClass="BaseBundle\Repository\ImageRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Image
{
	//const SERVER_PATH_TO_IMAGE_FOLDER = $this->getParameter('upload_directory');
	const SERVER_PATH_TO_IMAGE_FOLDER = __DIR__.'/../../../web/files/';

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
     * @ORM\Column(name="title", type="string", length=255)
     */
    private $title;	
	
    /**
     * @var string
     *
     * @ORM\Column(name="filename", type="string", length=255)
     */
    private $filename;
	
    /**
     * @var int
     *
     * @ORM\Column(name="sort", type="integer")
     */
    private $sort;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated", type="datetime", nullable=true)
     */
    private $updated;


    
	

	
    /**
     * @Assert\File(maxSize="67108864")
     */
    private $file;
	
	/**
	 * Sets file.
	 *
	 * @param UploadedFile $file
	 */
	public function setFile(UploadedFile $file = null)
	{
		$this->file = $file;
		$this->upload();
	}


	public function __toString()
	{
		return $this->filename;
	}

	 


	
	/**
	 * Manages the copying of the file to the relevant place on the server
	 */
	public function upload()
	{
		// the file property can be empty if the field is not required
		if (null === $this->getFile()) {
			return;
		}

		// we use the original file name here but you should
		// sanitize it at least to avoid any security issues

		$newName = md5($this->getFile()->getClientOriginalName().(date("now"))).'.'.$this->getFile()->getClientOriginalExtension();
		
		// move takes the target directory and target filename as params
		$this->getFile()->move(
			Image::SERVER_PATH_TO_IMAGE_FOLDER,$newName
		);
		
		//$photo = Image::getImagine()->open($dir . $fileName);

		// set the path property to the filename where you've saved the file
		
		$title = $this->getFile()->getClientOriginalName();
		$pos = strrpos($title, ".");
		if ($pos !== false)
		{
			$title = substr($title,0,$pos);
		}
		$this->title = $title;
		$this->filename = $newName;

		// clean up the file property as you won't need it anymore
		$this->setFile(null);
	}

	/**
	 * Lifecycle callback to upload the file to the server
	 */
	/**
	 * @ORM\PrePersist
	 * @ORM\PreUpdate
	 */	 
	public function lifecycleFileUpload() {
		//$this->upload();
	}

	/**
	 * Updates the hash value to force the preUpdate and postUpdate events to fire
	 */
	public function refreshUpdated() {
		$this->setUpdated(new \DateTime("now"));
	}	
	
	/**
	 * Get file.
	 *
	 * @return UploadedFile
	 */
	public function getFile()
	{
		return $this->file;
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
     * Set filename
     *
     * @param string $filename
     *
     * @return Image
     */
    public function setFilename($filename)
    {
        $this->filename = $filename;

        return $this;
    }

    /**
     * Get filename
     *
     * @return string
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Set updated
	 * @ORM\PrePersist
	 * @ORM\PreUpdate
     * @param \DateTime $updated
     *
     * @return Image
     */
    public function setUpdated($updated)
    {
        $this->updated = new \DateTime('now');

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
     * Set title
     *
     * @param string $title
     *
     * @return Image
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }



    /**
     * Set sort
     *
     * @param integer $sort
     *
     * @return Image
     */
    public function setSort($sort)
    {
        $this->sort = $sort;

        return $this;
    }

    /**
     * Get sort
     *
     * @return integer
     */
    public function getSort()
    {
        return $this->sort;
    }
}

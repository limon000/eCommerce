<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;

/**
 * Class TimeStamp
 * @HasLifecycleCallbacks()
 */
Trait TimeStamp {

    /**
     * @var \DateTime
     * @ORM\Column(name="modified_at",type="datetime",nullable=true)
     */
    protected $modifiedAt;


    /**
     * @return \DateTime
     */
    public function getModifiedAt(){
        return $this->modifiedAt;
    }


    /**
     * @param \DateTime $modifiedAt
     * @return $this
     */
    public function setModifiedAt(\DateTime $modifiedAt){
        $this->modifiedAt = $modifiedAt;
        return $this;
    }

    /**
     * @ORM\PreUpdate()
     */
    public function onUpdate(){
        $this->modifiedAt = new \DateTime('NOW');
    }

}
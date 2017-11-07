<?php

namespace Roshyo\PlanningBundle\Calendar\Resources;

use Doctrine\Common\Collections\ArrayCollection;

class ConcreteResource extends Resource
{
    protected $id;
    
    public function __construct(int $id, $name = '', ArrayCollection $items = null)
    {
        parent::__construct($name, $items);
        $this->id = $id;
    }
    
    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }
    
    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }
}
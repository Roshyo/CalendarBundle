<?php

namespace Roshyo\PlanningBundle\Calendar\Items;

use Doctrine\Common\Collections\ArrayCollection;
use Roshyo\PlanningBundle\Utils\DateTime;

class ConcreteItem extends Item
{
	
	protected $id;
	
	public function __construct(int $id, DateTime $fromDate = null, DateTime $toDate = null, $type = '',
	                            ArrayCollection $excludedDays = null, Resource $resource = null)
	{
		parent::__construct($fromDate, $toDate, $type, $excludedDays, $resource);
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
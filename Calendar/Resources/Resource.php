<?php

namespace Roshyo\PlanningBundle\Calendar\Resources;

use Doctrine\Common\Collections\ArrayCollection;
use Roshyo\PlanningBundle\Calendar\Items\Item;

abstract class Resource
{
	/** @var string */
	protected $name;
	/** @var ArrayCollection|null|Item[] */
	protected $items;
	/** @var array */
	protected $itemsClasses = [];
	
	/**
	 * Resource constructor.
	 *
	 * @param string $name
	 * @param ArrayCollection|null $items
	 */
	public function __construct($name = '', ArrayCollection $items = null)
	{
		$this->name = $name;
		$this->items = $items !== null ? $items : new ArrayCollection();
	}
	
	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}
	
	/**
	 * @param string $name
	 *
	 * @return self
	 */
	public function setName($name)
	{
		$this->name = $name;
		
		return $this;
	}
	
	/**
	 * @return ArrayCollection|null
	 */
	public function getItems()
	{
		$items = new ArrayCollection();
		foreach($this->itemsClasses as $itemsClass){
			$levels = explode('.', $itemsClass);
			$retrievedItems = $this->{'get'.ucfirst($levels[0])}();
			foreach($retrievedItems as $retrievedItem){
				if(!empty($levels[1])){
					$retrievedItem = $retrievedItem->{'get'.ucfirst($levels[1])}();
				}
				$items->add($retrievedItem);
			}
		}
		$this->items = $items;
		
		return $this->items;
	}
	
	/**
	 * @param Item $item
	 *
	 * @return self
	 */
	public function addItem(Item $item)
	{
		//If the new item is in conflict with any other item, don't add it
		foreach($this->items as $actualItem){
			if($actualItem->conflictsWith($item)){
				return $this;
			}
		}
		$this->items->add($item);
		
		return $this;
	}
	
	/**
	 * @param Item $item
	 *
	 * @return self
	 */
	public function removeItem(Item $item)
	{
		$this->items->removeElement($item);
		
		return $this;
	}
	
	/**
	 * @return array
	 */
	public function getItemsClasses()
	{
		return $this->itemsClasses;
	}
	
	/**
	 * @param string $itemClass
	 *
	 * @return self
	 */
	public function addItemClass($itemClass)
	{
		if(array_search($itemClass, $this->itemsClasses) === false){
			$this->itemsClasses[] = $itemClass;
		}
		
		return $this;
	}
	
	/**
	 * @param string $itemClass
	 *
	 * @return self
	 */
	public function removeItemClass($itemClass)
	{
		$key = array_search($itemClass, $this->itemsClasses);
		
		if($key !== false)
			unset($this->itemsClasses[$key]);
		
		return $this;
	}
	
	/**
	 * Returns true if the resource is busy for the asked DateTime, false otherwise
	 *
	 * @param \DateTime $askedDate
	 *
	 * @return bool
	 */
	public function isBusy(\DateTime $askedDate)
	{
		//By default, resource is not busy
		$isBusy = false;
		
		//For each item
		/** @var Item $item */
		foreach($this->items as $item){
			//If the asked date is in those concerned by the item
			$isBusy = $item->concernsDate($askedDate);
			if($isBusy)
				return true;
		}
		
		return $isBusy;
	}
	
	/**
	 * @param \DateTime $askedDate
	 * @param bool $firstHalf
	 *
	 * @return bool
	 */
	public function isBusyHalf(\DateTime $askedDate, $firstHalf = true)
	{
		if($firstHalf)
			$askedDate->setTime(10, 0);
		else
			$askedDate->setTime(16, 0);
		
		//By default, resource is not busy
		$isBusy = false;
		
		//For each item
		/** @var Item $item */
		foreach($this->items as $item){
			//If the asked date is in those concerned by the item
			$isBusy = $item->concernsDate($askedDate);
			if($isBusy)
				return true;
		}
		
		return $isBusy;
	}
	
	/**
	 * Returns the list of items concerning this day
	 *
	 * @param \DateTime $askedDate
	 *
	 * @return array|Item[]
	 */
	public function getItemsThisDay(\DateTime $askedDate)
	{
		$items = [];
		
		foreach($this->items as $item){
			if($item->concernsDate($askedDate)){
				$items[] = $item;
			}
		}
		
		return $items;
	}
}
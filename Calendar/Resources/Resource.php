<?php

namespace Roshyo\PlanningBundle\Calendar\Resources;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Roshyo\PlanningBundle\Calendar\Items\Item;
use Roshyo\PlanningBundle\Utils\DateTime;

abstract class Resource
{
    const REWORK_TYPE_REMOVE = -1;
    const REWORK_TYPE_MODIFY_FROM_DATE = 1;
    const REWORK_TYPE_MODIFY_TO_DATE = 2;
    const REWORK_TYPE_SPLIT_ITEM = 0;
    const REWORK_TYPE_FALLBACK = -2;
    
    /** @var string */
    protected $name;
    /** @var ArrayCollection|null|Item[] */
    protected $items;
    /** @var array */
    protected $itemsClasses = [];
    
    
    /**
     * Resource constructor.
     *
     * @param string               $name
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
     * @param Item $item
     *
     * @return self
     */
    public function addItem(Item $item)
    {
        //If the new item is in conflict with any other item, don't add it
        if ($this->canAddItem($item))
            $this->items->add($item);

        return $this;
    }
    
    /**
     * @param Item $item
     *
     * @return bool
     */
    public function canAddItem(Item $item)
    {
        foreach($this->getItems() as $actualItem){
            if($item->conflictsWith($actualItem) || $actualItem->conflictsWith($item)){
                return false;
            }
        }
        return true;
    }
    
    /**
     * @return ArrayCollection|Item[]
     */
    public function getItems()
    {
        $items = new ArrayCollection();
        foreach($this->getItemsClasses() as $itemsClass){
            $retrievedItems = $this->{'get'.ucfirst($itemsClass)}();
            foreach($retrievedItems as $retrievedItem){
                $items->add($retrievedItem);
            }
        }
        $this->items = $items;
        
        return $this->items;
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
        if(array_search($itemClass, $this->getItemsClasses()) === false){
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
        $key = array_search($itemClass, $this->getItemsClasses());

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
        foreach($this->getItems() as $item){
            //If the asked date is in those concerned by the item
            $isBusy = $item->concernsDate($askedDate);
            if($isBusy)
                return true;
        }

        return $isBusy;
    }
    
    /**
     * @param \DateTime $askedDate
     * @param bool      $firstHalf
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
        foreach($this->getItems() as $item){
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

        foreach($this->getItems() as $item){
            if($item->concernsDate($askedDate)){
                $items[] = $item;
            }
        }

        return $items;
    }
    
    /**
     * @param Item $item
     *
     * @return ArrayCollection|Collection|Item[]
     */
    public function getConflictingItems(Item &$item)
    {
        $conflictingItems = new ArrayCollection();

        foreach ($this->getItems() as &$actualItem) {
            if ($item->conflictsWith($actualItem) || $actualItem->conflictsWith($item)) {
                $conflictingItems->add($actualItem);
            }
        }

        return $conflictingItems;
    }
    
    /**
     * @param Item $itemToRework
     * @param Item $itemInConflict
     *
     * @return array
     */
    public function reworkItemToRemoveConflicts(Item &$itemToRework, Item &$itemInConflict)
    {
        $response = [];

        $itemToReworkFromDate = $itemToRework->getFromDate();
        $itemToReworkToDate = $itemToRework->getToDate();
        $itemInConflictFromDate = clone $itemInConflict->getFromDate();
        $itemInConflictToDate = clone $itemInConflict->getToDate();

        //If the new Item starts before and ends after the item to rework, remove the item to rework
        if($itemInConflictFromDate <= $itemToReworkFromDate && $itemInConflictToDate >= $itemToReworkToDate){
            $this->removeItem($itemToRework);
            $response['type'] = self::REWORK_TYPE_REMOVE;
        }//If the new Item starts before and ends before the end of the item to rework, redefine the start date of the item to rework
        else if ($itemInConflictFromDate <= $itemToReworkFromDate && $itemInConflictToDate < $itemToReworkToDate) {
            $newFromDate = clone $itemInConflictToDate;
            DateTime::changeDateToAppropriateMoment($newFromDate, $itemInConflictToDate, true);
            $itemToRework->setFromDate($newFromDate);
            $response['type'] = self::REWORK_TYPE_MODIFY_FROM_DATE;
        }//If the new Item starts after and ends before, split the item to rework in two
        elseif($itemInConflictFromDate > $itemToReworkFromDate && $itemInConflictToDate < $itemToReworkToDate){
            $newItem = clone $itemToRework;

            $newToDate = clone $itemInConflictFromDate;
            DateTime::changeDateToAppropriateMoment($newToDate, $itemInConflictFromDate, false);
            $itemToRework->setToDate($newToDate);

            $newFromDate = clone $itemInConflictToDate;
            DateTime::changeDateToAppropriateMoment($newFromDate, $itemInConflictToDate, true);
            $newItem->setFromDate($newFromDate);

            $response['type'] = self::REWORK_TYPE_SPLIT_ITEM;
            $response['newItem'] = $newItem;
        }//If the new Item starts after and ends after, redefine the end date of the item to rework
        elseif($itemInConflictFromDate > $itemToRework && $itemInConflictToDate >= $itemToReworkToDate){
            $newToDate = clone $itemInConflictFromDate;
            DateTime::changeDateToAppropriateMoment($newToDate, $itemInConflictFromDate, false);
            $itemToRework->setToDate($newToDate);
            $response['type'] = self::REWORK_TYPE_MODIFY_TO_DATE;
        } else {
            $response['type'] = self::REWORK_TYPE_FALLBACK;
        }

        return $response;
    }

    /**
     * @param Item $item
     *
     * @return self
     */
    public function removeItem(Item $item)
    {
        $items = $this->getItems();
        $items->removeElement($item);
        $this->items = $items;

        return $this;
    }

    /**
     * @param \DateTime     $from
     * @param \DateTime     $to
     * @param string | null $class
     *
     * @return array
     */
    public function getItemsBetweenTwoDates(\DateTime $from, \DateTime $to, string $class = null)
    {
        $items = [];

        $from = clone $from;
        $from->setTime(0, 0);
        $to = clone $to;
        $to->setTime(23, 59, 59);

        foreach ($this->getItems() as $item) {
            if ($class !== null) {
                if (get_class($item) === $class && $this->isInThisInterval($item, $from, $to))
                    $items[] = $item;
            } else {
                if ($this->isInThisInterval($item, $from, $to))
                    $items[] = $item;
            }
        }
        return $items;
    }

    /**
     * @param Item      $item
     * @param \DateTime $from
     * @param \DateTime $to
     *
     * @return bool
     */
    protected function isInThisInterval(Item $item, \DateTime $from, \DateTime $to)
    {
        return ($item->getFromDate() >= $from && $item->getFromDate() <= $to ||
            $item->getToDate() >= $from && $item->getToDate() <= $to);
    }
}
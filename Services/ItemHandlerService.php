<?php

namespace Roshyo\PlanningBundle\Services;

use Roshyo\PlanningBundle\Calendar\Items\Item;
use Roshyo\PlanningBundle\Utils\DateTime;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class ItemHandlerService implements ContainerAwareInterface
{
    use ContainerAwareTrait;
    
    /** @var  Item */
    protected $item;
    
    /**
     * Returns true if the asked date is concerned by this item, false otherwise
     *
     * @param \DateTime $askedDate
     *
     * @return bool
     */
    public function concernsDate(\DateTime $askedDate): bool
    {
        $contains = false;
        
        $item = $this->getItem();
        
        if(DateTime::isDateBetween($askedDate, $item->getFromDate(), $item->getToDate())){
            $contains = true;
            foreach($item->getExcludedDays() as $excludedDay){
                if($askedDate->format('Y-m-d') === $excludedDay->format('Y-m-d'))
                    $contains = false;
            }
        }
        
        return $contains;
    }
    
    /**
     * @return Item
     */
    public function getItem(): Item
    {
        return $this->item;
    }
    
    /**
     * @param Item $item
     *
     * @return ItemHandlerService
     */
    public function setItem(Item $item): self
    {
        $this->item = $item;
        
        return $this;
    }
    
    /**
     * Returns true if an item is in conflict with an other
     * Conflict is defined as following:
     *      If two Items are concerning one or more identical dates without one excluding them, conflict.
     *
     * @param Item $item
     *
     * @return bool
     */
    public function conflictsWith(Item $item): bool
    {
        $thisItem = $this->getItem();
        
        //If the dates doesn't even concern the same days (or hours), no conflict
        if($thisItem->getToDate() < $item->getFromDate() || $item->getToDate() < $thisItem->getFromDate())
            return false;
        
        $concernedDaysByThis = DateTime::getDatesBetween($thisItem->getFromDate(), $thisItem->getToDate());
        $concernedDaysByItem = DateTime::getDatesBetween($item->getFromDate(), $item->getToDate());
        //Fetch the days concerned by the two items
        $concernedDaysUnion = [];
        foreach($concernedDaysByThis as $day){
            if(in_array($day, $concernedDaysByItem)){
                $concernedDaysUnion[] = $day;
            }
        }
        
        if(empty($concernedDaysUnion))
            return false;
        
        //For each day in the union
        foreach($concernedDaysUnion as $day){
            //If the day isn't excluded by at least 1 item, there is conflict
            if(!$thisItem->getExcludedDays()->exists(
                    function($key, $element) use ($day){
                        return $day->format('Y-m-d') === $element->format('Y-m-d');
                    }
                ) && !$item->getExcludedDays()->exists(function($key, $element) use ($day){
                    return $day->format('Y-m-d') === $element->format('Y-m-d');
                })
            ){
                return true;
            }
        }
        
        //Arrived here, no conflict
        return false;
    }
}
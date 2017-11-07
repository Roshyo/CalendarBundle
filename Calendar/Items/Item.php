<?php

namespace Roshyo\PlanningBundle\Calendar\Items;

use Doctrine\Common\Collections\ArrayCollection;
use Roshyo\PlanningBundle\Calendar\Resources\Resource;
use Roshyo\PlanningBundle\Utils\DateTime;
use Roshyo\PlanningBundle\Validator\Constraint as EmployeeValidator;
use SiteBundle\Entity\SubSiteEmployee;

/**
 * Class Item
 * @package Roshyo\PlanningBundle\Calendar\Items
 *
 * @EmployeeValidator\Item
 */
abstract class Item
{
    /** @var DateTime|null */
    protected $fromDate;
    /** @var DateTime|null */
    protected $toDate;
    /** @var ArrayCollection|DateTime[] */
    protected $excludedDays;
    /** @var Resource|null */
    protected $resource;
    /** @var  string */
    protected $type = 'item';

    /**
     * Item constructor.
     *
     * @param DateTime|null        $fromDate
     * @param DateTime|null        $toDate
     * @param string               $type
     * @param ArrayCollection|null $excludedDays
     * @param Resource|null        $resource
     */
    public function __construct(DateTime $fromDate = null, DateTime $toDate = null, $type = '',
                                ArrayCollection $excludedDays = null, Resource $resource = null)
    {
        $this->fromDate = $fromDate;
        $this->toDate = $toDate;
        $this->type = $type;
        $this->excludedDays = $excludedDays !== null ? $excludedDays : new ArrayCollection();
        $this->resource = $resource;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return get_class($this);
    }
    
    /**
     * @param DateTime $excludedDate
     *
     * @return self
     */
    public function addExcludedDay(DateTime $excludedDate)
    {
        $this->excludedDays->add($excludedDate);
        
        return $this;
    }
    
    /**
     * @param DateTime $excludedDate
     *
     * @return self
     */
    public function removeExcludedDay(DateTime $excludedDate)
    {
        $this->excludedDays->remove($excludedDate);
        
        return $this;
    }
    
    /**
     * @return null|Resource
     */
    public function getResource()
    {
        return $this->resource;
    }
    
    /**
     * @param null|Resource $resource
     *
     * @return self
     */
    public function setResource($resource)
    {
        $this->resource = $resource;
        
        return $this;
    }
    
    /**
     * Returns true if the asked date is concerned by this item, false otherwise
     *
     * @param \DateTime $askedDate
     *
     * @return bool
     */
    public function concernsDate(\DateTime $askedDate)
    {
        $contains = false;
        
        if(DateTime::isDateBetween($askedDate, $this->getFromDate(), $this->getToDate())){
            $contains = true;
            foreach($this->getExcludedDays() as $excludedDay){
                if($askedDate->format('Y-m-d') === $excludedDay->format('Y-m-d'))
                    $contains = false;
            }
        }
        
        return $contains;
    }
    
    /**
     * @return DateTime|null
     */
    public function getFromDate()
    {
        return $this->fromDate;
    }
    
    /**
     * @param DateTime|null $fromDate
     *
     * @return self
     */
    public function setFromDate($fromDate)
    {
        $this->fromDate = $fromDate;
        
        return $this;
    }
    
    /**
     * @return DateTime|null
     */
    public function getToDate()
    {
        return $this->toDate;
    }
    
    /**
     * @param DateTime|null $toDate
     *
     * @return self
     */
    public function setToDate($toDate)
    {
        $this->toDate = $toDate;
        
        return $this;
    }
    
    /**
     * @return ArrayCollection|DateTime[]
     */
    public function getExcludedDays(): ArrayCollection
    {
        if($this->excludedDays === null)
            $this->excludedDays = new ArrayCollection();
        return $this->excludedDays;
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
    public function conflictsWith(Item $item)
    {
        //If the dates doesn't even concern the same days (or hours), no conflict
        if($this->getToDate() < $item->getFromDate() || $item->getToDate() < $this->getFromDate())
            return false;
    
        $concernedDaysByThis = DateTime::getDatesBetween($this->getFromDate(), $this->getToDate());
        $concernedDaysByItem = DateTime::getDatesBetween($item->getFromDate(), $item->getToDate());
        //Fetch the days concerned by the two items
        $concernedDaysUnion = [];
        foreach($concernedDaysByThis as $day){
            if (in_array($day, $concernedDaysByItem)) {
                $concernedDaysUnion[] = $day;
            }
        }
        
        //For each day in the union
        foreach($concernedDaysUnion as $day){
            //If the day isn't excluded by at least 1 item, there is conflict
            if(!$this->getExcludedDays()->exists(
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
    
    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * check if at the $date, it is the first Item. this is to put the dragbar only on the first and last element.
     *
     * @param DateTime $date
     * @param bool     $first
     *
     * @return bool
     */
    public function isFirstOrLastElement(DateTime $date, bool $first = true, bool $firstHalf = true)
    {
        if (!$this instanceof SubSiteEmployee)
            return false;

        $fromDate = $this->getFromDate();
        $toDate = $this->getToDate();

        $fromHour = $fromDate->format('H');
        $toHour = $toDate->format('H');

        if ($first) {
            if ($fromDate->format('Y-m-d') === $date->format('Y-m-d')) {
                if ($firstHalf && $fromHour <= 12)
                    return true;
                else if (!$firstHalf && $fromHour > 12)
                    return true;
            }
        } else {
            if ($toDate->format('Y-m-d') === $date->format('Y-m-d')) {
                if ($firstHalf && $toHour <= 12)
                    return true;
                else if (!$firstHalf && $toHour > 12)
                    return true;
            }
        }
        return false;
    }
}
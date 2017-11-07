<?php

namespace Roshyo\PlanningBundle\Services;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Roshyo\PlanningBundle\Calendar\Items\Item;
use Roshyo\PlanningBundle\Calendar\Resources\Resource;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ResourceHandlerService implements ContainerAwareInterface
{
    use ContainerAwareTrait;
    
    /** @var  \Roshyo\PlanningBundle\Calendar\Resources\Resource */
    protected $resource;

    /**
     * ResourceHandlerService constructor.
     *
     * @param ContainerInterface $container
     */
    function __construct(ContainerInterface $container)
    {
        $this->setContainer($container);
    }

    /**
     * @param Item $item
     *
     * @return self
     */
    public function addItem(Item $item): self
    {
        //If the new item is in conflict with any other item, don't add it
        if($this->getResource()->canAddItem($item))
            $this->getResource()->getItems()->add($item);
        
        return $this;
    }
    
    /**
     * @return \Roshyo\PlanningBundle\Calendar\Resources\Resource
     */
    public function getResource(): \Roshyo\PlanningBundle\Calendar\Resources\Resource
    {
        return $this->resource;
    }
    
    /**
     * @param \Roshyo\PlanningBundle\Calendar\Resources\Resource $resource
     *
     * @return ResourceHandlerService
     */
    public function setResource(\Roshyo\PlanningBundle\Calendar\Resources\Resource $resource): self
    {
        $this->resource = $resource;
        
        return $this;
    }
    
    /**
     * @param Item $item
     *
     * @return bool
     */
    public function canAddItem(Item $item): bool
    {
        $resource = $this->getResource();
        /** @var Item $actualItem */
        foreach($resource->getItems() as $actualItem){
            if($item->conflictsWith($actualItem) || $actualItem->conflictsWith($item)){
                return false;
            }
        }
        return true;
    }
    
    /**
     * Returns true if the resource is busy for the asked DateTime, false otherwise
     *
     * @param \DateTime $askedDate
     *
     * @return bool
     */
    public function isBusy(\DateTime $askedDate): bool
    {
        //By default, resource is not busy
        $isBusy = false;
        
        //For each item
        foreach($this->getResource()->getItems() as $item){
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
    public function isBusyHalf(\DateTime $askedDate, $firstHalf = true): bool
    {
        $askedDate = clone $askedDate;
        
        if($firstHalf)
            $askedDate->setTime(10, 0);
        else
            $askedDate->setTime(16, 0);
        
        //By default, resource is not busy
        $isBusy = false;
        
        //For each item
        foreach($this->getResource()->getItems() as $item){
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
    public function getItemsThisDay(\DateTime $askedDate): array
    {
        $items = [];
        
        foreach($this->getResource()->getItems() as $item){
            if($item->concernsDate($askedDate)){
                $items[] = $item;
            }
        }
        
        return $items;
    }
    
    /**
     * @param Item $item
     *
     * @return Collection|Item[]
     */
    public function getConflictingItems(Item &$item): Collection
    {
        $conflictingItems = new ArrayCollection();
        
        foreach($this->getResource()->getItems() as &$actualItem){
            if($item->conflictsWith($actualItem) || $actualItem->conflictsWith($item)){
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
    public function reworkItemToRemoveConflicts(Item &$itemToRework, Item &$itemInConflict): array
    {
        $response = [];
        
        $resource = $this->getResource();
        $dateTimeModifier = $this->container->get('rosh_planning.datetime_modifier');
        
        $itemToReworkFromDate = $itemToRework->getFromDate();
        $itemToReworkToDate = $itemToRework->getToDate();
        $itemInConflictFromDate = clone $itemInConflict->getFromDate();
        $itemInConflictToDate = clone $itemInConflict->getToDate();
        
        //If the new Item starts before and ends after the item to rework, remove the item to rework
        if($itemInConflictFromDate <= $itemToReworkFromDate && $itemInConflictToDate >= $itemToReworkToDate){
            $resource->removeItem($itemToRework);
            $response['type'] = Resource::REWORK_TYPE_REMOVE;
        }//If the new Item starts before and ends before the item to rework, redefine the start date of the item to rework
        else if($itemInConflictFromDate <= $itemToReworkFromDate && $itemInConflictToDate < $itemToReworkToDate){
            $newFromDate = clone $itemInConflictToDate;
            $dateTimeModifier->changeDateToAppropriateMoment($newFromDate, $itemInConflictToDate, true);
            $itemToRework->setFromDate($newFromDate);
            $response['type'] = Resource::REWORK_TYPE_MODIFY_FROM_DATE;
        }//If the new Item starts after and ends before, split the item to rework in two
        else if ($itemInConflictFromDate > $itemToReworkFromDate && $itemInConflictToDate < $itemToReworkToDate) {
            $newItem = clone $itemToRework;
            
            $newToDate = clone $itemInConflictFromDate;
            $dateTimeModifier->changeDateToAppropriateMoment($newToDate, $itemInConflictFromDate, false);
            $itemToRework->setToDate($newToDate);
            
            $newFromDate = clone $itemInConflictToDate;
            $dateTimeModifier->changeDateToAppropriateMoment($newFromDate, $itemInConflictToDate, true);
            $newItem->setFromDate($newFromDate);
            
            $response['type'] = Resource::REWORK_TYPE_SPLIT_ITEM;
            $response['newItem'] = $newItem;
        }//If the new Item starts after and ends after, redefine the end date of the item to rework
        else if ($itemInConflictFromDate > $itemToReworkFromDate && $itemInConflictToDate >= $itemToReworkToDate) {
            $newToDate = clone $itemInConflictFromDate;
            $dateTimeModifier->changeDateToAppropriateMoment($newToDate, $itemInConflictFromDate, false);
            $itemToRework->setToDate($newToDate);
            $response['type'] = Resource::REWORK_TYPE_MODIFY_TO_DATE;
        }else{
            $response['type'] = Resource::REWORK_TYPE_FALLBACK;
        }
        
        return $response;
    }
}
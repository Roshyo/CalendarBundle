<?php

namespace Test\Roshyo\PlanningBundle\Calendar\Items;

use Roshyo\PlanningBundle\Calendar\Items\ConcreteItem;
use Roshyo\PlanningBundle\Utils\DateTime;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ItemTest extends WebTestCase
{
    public function testConcernsDayAction()
    {
        $client = static::createClient();
        $container = $client->getContainer();
    
        $itemHandler = $container->get('rosh_planning.item_handler');
        
        $askedDate = new DateTime('2017-05-26 13:00:00');
        
        $item = new ConcreteItem(1);
        $item
            ->setFromDate(new DateTime('2017-05-20 08:00:00'))
            ->setToDate(new DateTime('2017-05-26 12:00:00'));
    
        $itemHandler->setItem($item);
    
        $this->assertFalse($itemHandler->concernsDate($askedDate));
        
        $askedDate = new DateTime('2017-05-23 08:00:00');
    
        $this->assertTrue($itemHandler->concernsDate($askedDate));
        
        $excludedDay = new DateTime('2017-05-23');
        
        $item->addExcludedDay($excludedDay);
    
        $this->assertFalse($itemHandler->concernsDate($askedDate));
    }
    
    public function testConflictsWithAction()
    {
        $client = static::createClient();
        $container = $client->getContainer();
    
        $firstItemHandler = $container->get('rosh_planning.item_handler');
        $secondItemHandler = clone $firstItemHandler;
        
        $firstItem = new ConcreteItem(1);
        $firstItem
            ->setFromDate(new DateTime('2017-05-10 08:00:00'))
            ->setToDate(new DateTime('2017-05-16 12:00:00'));
        $firstItemHandler->setItem($firstItem);
        
        $secondItem = new ConcreteItem(2);
        $secondItem
            ->setFromDate(new DateTime('2017-05-20 08:00:00'))
            ->setToDate(new DateTime('2017-05-26 12:00:00'));
        $secondItemHandler->setItem($secondItem);
    
        $this->assertFalse($firstItemHandler->conflictsWith($secondItem));
        $this->assertFalse($secondItemHandler->conflictsWith($firstItem));
    }
    
    public function testConflictsWith2Action()
    {
        $client = static::createClient();
        $container = $client->getContainer();
    
        $firstItemHandler = $container->get('rosh_planning.item_handler');
        $secondItemHandler = clone $firstItemHandler;
        
        $firstItem = new ConcreteItem(1);
        $firstItem
            ->setFromDate(new DateTime('2017-05-10 08:00:00'))
            ->setToDate(new DateTime('2017-05-16 12:00:00'));
        $firstItemHandler->setItem($firstItem);
        
        $secondItem = new ConcreteItem(2);
        $secondItem
            ->setFromDate(new DateTime('2017-05-15 08:00:00'))
            ->setToDate(new DateTime('2017-05-26 12:00:00'));
        $secondItemHandler->setItem($secondItem);
    
        $this->assertTrue($firstItemHandler->conflictsWith($secondItem));
        $this->assertTrue($secondItemHandler->conflictsWith($firstItem));
    }
    
    public function testConflictsWith3Action()
    {
        $client = static::createClient();
        $container = $client->getContainer();
    
        $firstItemHandler = $container->get('rosh_planning.item_handler');
        $secondItemHandler = clone $firstItemHandler;
        
        $firstItem = new ConcreteItem(1);
        $firstItem
            ->setFromDate(new DateTime('2017-05-10 08:00:00'))
            ->setToDate(new DateTime('2017-05-16 12:00:00'));
        $firstItemHandler->setItem($firstItem);
        
        $secondItem = new ConcreteItem(2);
        $secondItem
            ->setFromDate(new DateTime('2017-05-15 08:00:00'))
            ->setToDate(new DateTime('2017-05-26 12:00:00'));
        $secondItemHandler->setItem($secondItem);
        
        $excludedDay = new DateTime('2017-05-15');
        $firstItem->addExcludedDay($excludedDay);
        
        $this->assertTrue($firstItem->conflictsWith($secondItem));
        $this->assertTrue($secondItem->conflictsWith($firstItem));
    }
    
    public function testConflictsWith4Action()
    {
        $client = static::createClient();
        $container = $client->getContainer();
    
        $firstItemHandler = $container->get('rosh_planning.item_handler');
        $secondItemHandler = clone $firstItemHandler;
        
        $firstItem = new ConcreteItem(1);
        $firstItem
            ->setFromDate(new DateTime('2017-05-10 08:00:00'))
            ->setToDate(new DateTime('2017-05-16 12:00:00'));
        $firstItemHandler->setItem($firstItem);
        
        $secondItem = new ConcreteItem(2);
        $secondItem
            ->setFromDate(new DateTime('2017-05-15 08:00:00'))
            ->setToDate(new DateTime('2017-05-26 12:00:00'));
        $secondItemHandler->setItem($secondItem);
        
        $excludedDay = new DateTime('2017-05-15');
        $firstItem->addExcludedDay($excludedDay);
        $excludedDay = new DateTime('2017-05-16');
        $firstItem->addExcludedDay($excludedDay);
        
        $this->assertFalse($firstItem->conflictsWith($secondItem));
        $this->assertFalse($secondItem->conflictsWith($firstItem));
    }
    
    public function testConflictsWith5Action()
    {
        $client = static::createClient();
        $container = $client->getContainer();
    
        $firstItemHandler = $container->get('rosh_planning.item_handler');
        $secondItemHandler = clone $firstItemHandler;
        
        $firstItem = new ConcreteItem(1);
        $firstItem
            ->setFromDate(new DateTime('2017-05-10 08:00:00'))
            ->setToDate(new DateTime('2017-05-16 12:00:00'));
        $firstItemHandler->setItem($firstItem);
        
        $secondItem = new ConcreteItem(2);
        $secondItem
            ->setFromDate(new DateTime('2017-05-09 08:00:00'))
            ->setToDate(new DateTime('2017-05-17 12:00:00'));
        $secondItemHandler->setItem($secondItem);
        
        $this->assertTrue($firstItem->conflictsWith($secondItem));
        $this->assertTrue($secondItem->conflictsWith($firstItem));
    }
    
    public function testConflictsWith6Action()
    {
        $client = static::createClient();
        $container = $client->getContainer();
    
        $firstItemHandler = $container->get('rosh_planning.item_handler');
        $secondItemHandler = clone $firstItemHandler;
        
        $firstItem = new ConcreteItem(1);
        $firstItem
            ->setFromDate(new DateTime('2017-05-10 08:00:00'))
            ->setToDate(new DateTime('2017-05-16 12:00:00'));
        $firstItemHandler->setItem($firstItem);
        
        $secondItem = new ConcreteItem(2);
        $secondItem
            ->setFromDate(new DateTime('2017-05-12 08:00:00'))
            ->setToDate(new DateTime('2017-05-14 12:00:00'));
        $secondItemHandler->setItem($secondItem);
        
        $this->assertTrue($firstItem->conflictsWith($secondItem));
        $this->assertTrue($secondItem->conflictsWith($firstItem));
    }
    
    public function testConflictsWith7Action()
    {
        $client = static::createClient();
        $container = $client->getContainer();
    
        $firstItemHandler = $container->get('rosh_planning.item_handler');
        $secondItemHandler = clone $firstItemHandler;
        
        $firstItem = new ConcreteItem(1);
        $firstItem
            ->setFromDate(new DateTime('2017-05-10 08:00:00'))
            ->setToDate(new DateTime('2017-05-16 12:00:00'));
        $firstItemHandler->setItem($firstItem);
        
        $secondItem = new ConcreteItem(2);
        $secondItem
            ->setFromDate(new DateTime('2017-05-16 13:00:00'))
            ->setToDate(new DateTime('2017-05-18 12:00:00'));
        $secondItemHandler->setItem($secondItem);
        
        $this->assertFalse($firstItem->conflictsWith($secondItem));
        $this->assertFalse($secondItem->conflictsWith($firstItem));
    }
    
    public function testConflictsWith8Action()
    {
        $client = static::createClient();
        $container = $client->getContainer();
    
        $firstItemHandler = $container->get('rosh_planning.item_handler');
        $secondItemHandler = clone $firstItemHandler;
        
        $firstItem = new ConcreteItem(1);
        $firstItem
            ->setFromDate(new DateTime('2017-05-10 08:00:00'))
            ->setToDate(new DateTime('2017-05-16 12:00:00'));
        $firstItemHandler->setItem($firstItem);
        
        $secondItem = new ConcreteItem(2);
        $secondItem
            ->setFromDate(new DateTime('2017-05-16 11:00:00'))
            ->setToDate(new DateTime('2017-05-18 12:00:00'));
        $secondItemHandler->setItem($secondItem);
        
        $this->assertTrue($firstItem->conflictsWith($secondItem));
        $this->assertTrue($secondItem->conflictsWith($firstItem));
    }
}
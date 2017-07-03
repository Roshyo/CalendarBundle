<?php

namespace Test\Roshyo\PlanningBundle\Calendar\Items;

use PHPUnit\Framework\TestCase;
use Roshyo\PlanningBundle\Calendar\Items\ConcreteItem;
use Roshyo\PlanningBundle\Utils\DateTime;

class ItemTest extends TestCase
{
	public function testConcernsDayAction()
	{
		$askedDate = new DateTime('2017-05-26 13:00:00');
		
		$item = new ConcreteItem();
		$item
			->setFromDate(new DateTime('2017-05-20 08:00:00'))
			->setToDate(new DateTime('2017-05-26 12:00:00'));
		
		$this->assertFalse($item->concernsDate($askedDate));
		
		$askedDate = new DateTime('2017-05-23 08:00:00');
		
		$this->assertTrue($item->concernsDate($askedDate));
		
		$excludedDay = new DateTime('2017-05-23');
		
		$item->addExcludedDay($excludedDay);
		
		$this->assertFalse($item->concernsDate($askedDate));
	}
	
	public function testConflictsWithAction()
	{
		$firstItem = new ConcreteItem();
		$firstItem
			->setFromDate(new DateTime('2017-05-10 08:00:00'))
			->setToDate(new DateTime('2017-05-16 12:00:00'));
		
		$secondItem = new ConcreteItem();
		$secondItem
			->setFromDate(new DateTime('2017-05-20 08:00:00'))
			->setToDate(new DateTime('2017-05-26 12:00:00'));
		
		$this->assertFalse($firstItem->conflictsWith($secondItem));
	}
	
	public function testConflictsWith2Action()
	{
		$firstItem = new ConcreteItem();
		$firstItem
			->setFromDate(new DateTime('2017-05-10 08:00:00'))
			->setToDate(new DateTime('2017-05-16 12:00:00'));
		
		$secondItem = new ConcreteItem();
		$secondItem
			->setFromDate(new DateTime('2017-05-15 08:00:00'))
			->setToDate(new DateTime('2017-05-26 12:00:00'));
		
		$this->assertTrue($firstItem->conflictsWith($secondItem));
	}
	
	public function testConflictsWith3Action()
	{
		$firstItem = new ConcreteItem();
		$firstItem
			->setFromDate(new DateTime('2017-05-10 08:00:00'))
			->setToDate(new DateTime('2017-05-16 12:00:00'));
		
		$secondItem = new ConcreteItem();
		$secondItem
			->setFromDate(new DateTime('2017-05-15 08:00:00'))
			->setToDate(new DateTime('2017-05-26 12:00:00'));
		
		$excludedDay = new DateTime('2017-05-15');
		$firstItem->addExcludedDay($excludedDay);
		
		$this->assertTrue($firstItem->conflictsWith($secondItem));
	}
	
	public function testConflictsWith4Action()
	{
		$firstItem = new ConcreteItem();
		$firstItem
			->setFromDate(new DateTime('2017-05-10 08:00:00'))
			->setToDate(new DateTime('2017-05-16 12:00:00'));
		
		$secondItem = new ConcreteItem();
		$secondItem
			->setFromDate(new DateTime('2017-05-15 08:00:00'))
			->setToDate(new DateTime('2017-05-26 12:00:00'));
		
		$excludedDay = new DateTime('2017-05-15');
		$firstItem->addExcludedDay($excludedDay);
		$excludedDay = new DateTime('2017-05-16');
		$firstItem->addExcludedDay($excludedDay);
		
		$this->assertFalse($firstItem->conflictsWith($secondItem));
	}
	
	public function testConflictsWith5Action()
	{
		$firstItem = new ConcreteItem();
		$firstItem
			->setFromDate(new DateTime('2017-05-10 08:00:00'))
			->setToDate(new DateTime('2017-05-16 12:00:00'));
		$secondItem = new ConcreteItem();
		$secondItem
			->setFromDate(new DateTime('2017-05-09 08:00:00'))
			->setToDate(new DateTime('2017-05-17 12:00:00'));
		
		$this->assertTrue($firstItem->conflictsWith($secondItem));
	}
	
	public function testConflictsWith6Action()
	{
		$firstItem = new ConcreteItem();
		$firstItem
			->setFromDate(new DateTime('2017-05-10 08:00:00'))
			->setToDate(new DateTime('2017-05-16 12:00:00'));
		$secondItem = new ConcreteItem();
		$secondItem
			->setFromDate(new DateTime('2017-05-12 08:00:00'))
			->setToDate(new DateTime('2017-05-14 12:00:00'));
		
		$this->assertTrue($firstItem->conflictsWith($secondItem));
	}
	
	public function testConflictsWith7Action()
	{
		$firstItem = new ConcreteItem();
		$firstItem
			->setFromDate(new DateTime('2017-05-10 08:00:00'))
			->setToDate(new DateTime('2017-05-16 12:00:00'));
		$secondItem = new ConcreteItem();
		$secondItem
			->setFromDate(new DateTime('2017-05-16 13:00:00'))
			->setToDate(new DateTime('2017-05-18 12:00:00'));
		
		$this->assertFalse($firstItem->conflictsWith($secondItem));
	}
	
	public function testConflictsWith8Action()
	{
		$firstItem = new ConcreteItem();
		$firstItem
			->setFromDate(new DateTime('2017-05-10 08:00:00'))
			->setToDate(new DateTime('2017-05-16 12:00:00'));
		$secondItem = new ConcreteItem();
		$secondItem
			->setFromDate(new DateTime('2017-05-16 11:00:00'))
			->setToDate(new DateTime('2017-05-18 12:00:00'));
		
		$this->assertTrue($firstItem->conflictsWith($secondItem));
	}
}
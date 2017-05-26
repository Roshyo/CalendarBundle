<?php

namespace Test\BHC\PlanningBundle\Calendar\Resources;

use AppBundle\Entity\DayOff;
use AppBundle\Entity\Employee;
use AppBundle\Entity\EmployeeDayOff;
use AppBundle\Entity\EmployeeTraining;
use BHC\PlanningBundle\Calendar\Items\ConcreteItem;
use BHC\PlanningBundle\Calendar\Resources\ConcreteResource;
use BHC\PlanningBundle\Utils\DateTime;
use PHPUnit\Framework\TestCase;

class ResourceTest extends TestCase
{
	public function testIsBusy()
	{
		$askedDate = new DateTime('2017-05-23 08:00:00');
		
		$resource = new ConcreteResource('test Resource');
		
		$firstItem = new ConcreteItem();
		$firstItem
			->setFromDate(new DateTime('2017-05-20 08:00:00'))
			->setToDate(new DateTime('2017-05-26 12:00:00'));
		
		$excludedDay = new DateTime('2017-05-23');
		$firstItem->addExcludedDay($excludedDay);
		$resource->addItem($firstItem);
		
		$secondItem = new ConcreteItem();
		$secondItem
			->setFromDate(new DateTime('2017-05-27 08:00:00'))
			->setToDate(new DateTime('2017-05-29 08:00:00'));
		$resource->addItem($secondItem);
		
		$this->assertFalse($resource->isBusy($askedDate));
	}
	
	public function testGetItems()
	{
		$resource = new Employee();
		$resource->addItemClass('dayOffs.dayOff');
		$resource->addItemClass('trainings');
		
		$dayOff = new DayOff();
		$dayOff->setFromDate(new \DateTime('2017-05-20 08:00:00'))
			->setToDate(new \DateTime('2017-05-26 08:00:00'))
			->setResource($resource);
		$employeeDayOff = new EmployeeDayOff();
		$employeeDayOff->setEmployee($resource)->setDayOff($dayOff);
		
		$resource->addDayOff($employeeDayOff);
		
		$this->assertEquals(1, $resource->getItems()->count());
		
		$training = new EmployeeTraining();
		$training->setEmployee($resource)->setFromDate(new DateTime('2017-05-21 08:00:00'))->setToDate(new DateTime('2017-05-22 08:00:00'));
		
		$resource->addTraining($training);
		
		$this->assertEquals(2, $resource->getItems()->count());
	}
}
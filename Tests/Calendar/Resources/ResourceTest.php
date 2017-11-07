<?php

namespace Test\Roshyo\PlanningBundle\Calendar\Resources;

use EmployeeBundle\Entity\DayOff;
use EmployeeBundle\Entity\Employee;
use EmployeeBundle\Entity\EmployeeTraining;
use Roshyo\PlanningBundle\Calendar\Items\ConcreteItem;
use Roshyo\PlanningBundle\Calendar\Resources\ConcreteResource;
use Roshyo\PlanningBundle\Utils\DateTime;
use SiteBundle\Entity\SubSite;
use SiteBundle\Entity\SubSiteEmployee;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ResourceTest extends WebTestCase
{
    public function testIsBusy()
    {
        $client = static::createClient();
        $container = $client->getContainer();
    
        $resourceHandler = $container->get('rosh_planning.resource_handler');
        
        $askedDate = new DateTime('2017-05-23 08:00:00');
        
        $resource = new ConcreteResource(1);
        $resourceHandler->setResource($resource);
        
        $firstItem = new ConcreteItem(1);
        $firstItem
            ->setFromDate(new DateTime('2017-05-20 08:00:00'))
            ->setToDate(new DateTime('2017-05-26 12:00:00'));
        
        $excludedDay = new DateTime('2017-05-23');
        $firstItem->addExcludedDay($excludedDay);
        $resource->addItem($firstItem);
        
        $secondItem = new ConcreteItem(2);
        $secondItem
            ->setFromDate(new DateTime('2017-05-27 08:00:00'))
            ->setToDate(new DateTime('2017-05-29 08:00:00'));
        $resource->addItem($secondItem);
    
        $this->assertFalse($resourceHandler->isBusy($askedDate));
    }
    
    public function testGetItems()
    {
        $client = static::createClient();
        $container = $client->getContainer();
    
        $resourceHandler = $container->get('rosh_planning.resource_handler');
        
        $resource = new Employee();
        $resourceHandler->setResource($resource);
        
        $dayOff = new DayOff();
        $dayOff->setFromDate(new \DateTime('2017-05-20 08:00:00'))
            ->setToDate(new \DateTime('2017-05-26 08:00:00'))
            ->setResource($resource);
        
        $resource->addDayOff($dayOff);
        
        $this->assertEquals(1, $resource->getItems()->count());
        
        $training = new EmployeeTraining();
        $training->setEmployee($resource)->setFromDate(new DateTime('2017-05-21 08:00:00'))->setToDate(new DateTime('2017-05-22 08:00:00'));

        $this->assertFalse($resource->canAddItem($training));
        $resource->addTraining($training);

        $this->assertEquals(1, $resource->getItems()->count());
    }
    
    public function testMultipleItems()
    {
        $client = static::createClient();
        $container = $client->getContainer();
    
        $resourceHandler = $container->get('rosh_planning.resource_handler');
        
        $resource = new Employee();
        $resourceHandler->setResource($resource);
        
        $subSite = new SubSite();
        
        //Affect it from 2017-10-01 to 2017-10-31 (non priority resource)
        $affectedSite = new SubSiteEmployee();
        $affectedSite->setSite($subSite)
            ->setEmployee($resource)
            ->setAffectedAt(new DateTime('2017-10-01'))
            ->setAffectedUntil(new DateTime('2017-10-31'));
        
        $this->assertTrue($resource->canAddItem($affectedSite));
        $resource->addAffectedSite($affectedSite);
        
        $this->assertEquals(1, $resource->getItems()->count());
        
        $secondAffected = new SubSiteEmployee();
        $secondAffected->setSite($subSite)
            ->setEmployee($resource)
            ->setAffectedAt(new DateTime('2017-10-15'))
            ->setAffectedUntil(new DateTime('2017-11-31'));
        
        $this->assertTrue($affectedSite->conflictsWith($secondAffected));
        $this->assertTrue($secondAffected->conflictsWith($affectedSite));
        $this->assertFalse($resource->canAddItem($secondAffected));
    }
    
    public function testSplitItem()
    {
        $client = static::createClient();
        $container = $client->getContainer();
    
        $resourceHandler = $container->get('rosh_planning.resource_handler');
        
        //Creating the resource
        $resource = new Employee();
        $resourceHandler->setResource($resource);
        
        $subSite = new SubSite();
        
        //Affect it from 2017-10-01 to 2017-10-31 (non priority resource)
        $affectedSite = new SubSiteEmployee();
        $affectedSite->setSite($subSite)
            ->setEmployee($resource)
            ->setAffectedAt(new DateTime('2017-10-01'))
            ->setAffectedUntil(new DateTime('2017-10-31'));
        
        $resource->addAffectedSite($affectedSite);
        
        //Expected 1 item for the resource
        $this->assertEquals(1, $resource->getItems()->count());
        
        //Try to add a new priority resource in the middle of the existing one
        $dayOff = new DayOff();
        $dayOff->setFromDate(new \DateTime('2017-10-15 08:00:00'))
            ->setToDate(new \DateTime('2017-10-16 12:00:00'))
            ->setResource($resource);
        
        $this->assertFalse($resource->canAddItem($dayOff));
        $this->assertEquals(1, $resource->getConflictingItems($dayOff)->count());

        $resource->addDayOff($dayOff);

        $this->assertEquals(2, $resource->getAffectedSites()->count());

        $this->assertEquals(1, $resource->getDayOffs()->count());

        // Expected 3 items because the priority resource must split the previous one in 2
        $this->assertEquals(3, $resource->getItems()->count());
    }
}
<?php

namespace Test\Roshyo\PlanningBundle\Utils;

use PHPUnit\Framework\TestCase;
use Roshyo\PlanningBundle\Utils\DateTime;

class DateTimeTest extends TestCase
{
    public function testIsDateBetween()
    {
        $subject = new DateTime('2017-05-26 08:00:00');
        $fromDate = new DateTime('2017-05-25 08:00:00');
        $toDate = new DateTime('2017-05-27 08:00:00');
        
        $isDateBetween = DateTime::isDateBetween($subject, $fromDate, $toDate);
        
        $this->assertTrue($isDateBetween);
        
        $subject = new DateTime('2017-05-26 08:00:00');
        $fromDate = new DateTime('2017-05-26 07:59:00');
        $toDate = new DateTime('2017-05-26 08:01:00');
        
        $isDateBetween = DateTime::isDateBetween($subject, $fromDate, $toDate);
        
        $this->assertTrue($isDateBetween);
        
        $subject = new DateTime('2017-05-26 08:00:00');
        $fromDate = new DateTime('2017-05-26 08:01:00');
        $toDate = new DateTime('2017-05-26 07:59:00');
        
        $isDateBetween = DateTime::isDateBetween($subject, $fromDate, $toDate);
        
        $this->assertFalse($isDateBetween);
    }
    
    public function testConcernedDays()
    {
        $fromDate = new \DateTime('2017-05-20 09:00:00');
        $toDate = new \DateTime('2017-05-25 08:00:00');
        
        $this->assertEquals(6, count(DateTime::getDatesBetween($fromDate, $toDate)));
    }
}
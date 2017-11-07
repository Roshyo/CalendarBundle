<?php

namespace Roshyo\PlanningBundle\Services;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

class DateTimeModifierService implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /** @var  int */
    protected $dateTimeMorningFromDate = 0;
    /** @var  int */
    protected $dateTimeAfternoonFromDate = 0;
    /** @var  int */
    protected $dateTimeMorningToDate = 0;
    /** @var  int */
    protected $dateTimeAfternoonToDate = 0;
    /** @var bool */
    protected $isSetup = false;

    /**
     * DateTimeModifierService constructor.
     *
     * @param ContainerInterface $container
     */
    function __construct(ContainerInterface $container)
    {
        $this->setContainer($container);
    }
    
    /**
     * Will set the from date and to date to default params
     *
     * If from date
     *
     * @param \DateTime $dateToChange
     * @param \DateTime $referenceDate
     * @param bool      $start If set to true, we'll look the begin date, false we'll look for the end date
     */
    public function changeDateToAppropriateMoment(\DateTime &$dateToChange, \DateTime $referenceDate, bool $start = false): void
    {
        if(!$this->isSetup)
            $this->setup();
        
        $referenceMinutes = (int)$referenceDate->format('H') * 60 + (int)$referenceDate->format('i');
        
        if($start) // I try to find the nearest start after this date.
        {
            if($referenceMinutes >= 14 * 60){
                $dateToChange->modify('+1 day');
                $dateToChange->setTime($this->dateTimeMorningFromDate, 0);
            }else if($referenceMinutes >= 8 * 60)
                $dateToChange->setTime($this->dateTimeAfternoonFromDate, 0);
            else
                $dateToChange->setTime($this->dateTimeMorningFromDate, 0);
            
        }else // I try to find the nearest end behind that date.
        {
            if($referenceMinutes < 12 * 60){
                $dateToChange->modify('-1 day');
                $dateToChange->setTime($this->dateTimeAfternoonToDate, 0);
            }else if($referenceMinutes < 18 * 60)
                $dateToChange->setTime($this->dateTimeMorningToDate, 0);
            else
                $dateToChange->setTime($this->dateTimeAfternoonToDate, 0);
        }
    }
    
    public function setup(): void
    {
        $this->isSetup = true;
        
        $this->dateTimeMorningFromDate = $this->container->getParameter('datetime_morning_fromdate');
        $this->dateTimeMorningToDate = $this->container->getParameter('datetime_morning_todate');
        $this->dateTimeAfternoonFromDate = $this->container->getParameter('datetime_afternoon_fromdate');
        $this->dateTimeAfternoonToDate = $this->container->getParameter('datetime_afternoon_todate');
    }
}
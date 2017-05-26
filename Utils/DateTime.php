<?php

namespace Roshyo\PlanningBundle\Utils;

class DateTime extends \DateTime
{
	/**
	 * Returns true if the subject is between those two dates, false otherwise
	 *
	 * @param \DateTime $subject
	 * @param \DateTime $fromDate
	 * @param \DateTime $toDate
	 *
	 * @return bool
	 */
	public static function isDateBetween(\DateTime $subject, \DateTime $fromDate, \DateTime $toDate)
	{
		if($fromDate <= $subject && $subject <= $toDate)
			return true;
		return false;
	}
	
	/**
	 * @param \DateTime $fromDate
	 * @param \DateTime $toDate
	 *
	 * @return array|\DateTime[]
	 */
	public static function getConcernedDays(\DateTime $fromDate, \DateTime $toDate)
	{
		$concernedDays = [];
		
		$fromDate->setTime(0, 0);
		$toDate->setTime(0, 0);
		
		while($fromDate <= $toDate){
			$concernedDays[] = clone $fromDate;
			$fromDate->modify('+1 days');
		}
		
		return $concernedDays;
	}
}
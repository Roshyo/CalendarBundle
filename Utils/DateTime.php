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
	 * Returns the number of the weeks that are included between those two dates
	 *
	 * If $toExcluded is set to true, the last day will not be included in the result (eg $toDate = monday, this week will not be included)
	 *
	 * @param \DateTime $fromDate
	 * @param \DateTime $toDate
	 * @param bool $toExcluded
	 *
	 * @return array|int[]
	 */
	public static function getWeeksBetween(\DateTime $fromDate, \DateTime $toDate, $toExcluded = false)
	{
		$weeks = [];
		
		$fromDate->setTime(0, 0);
		//If to date is excluded, set the time at 00:00:00, the comparison will exclude it
		if($toExcluded)
			$toDate->setTime(0, 0);
		//If to date is not excluded, set the time at least at 00:00:01, the comparison will include it
		else
			$toDate->setTime(0, 0, 1);
		
		$crawledDate = clone $fromDate;
		while($crawledDate < $toDate){
			if(!in_array((int)$crawledDate->format('W'), $weeks)){
				$weeks[] = (int)$crawledDate->format('W');
			}
			$crawledDate->modify('+1 days');
		}
		
		return $weeks;
	}
	
	/**
	 * Returns the dates between those two dates.
	 *
	 * If $toExcluded is set to true, the last day will not be included in the result
	 *
	 * @param \DateTime $fromDate
	 * @param \DateTime $toDate
	 * @param bool $toExcluded
	 *
	 * @return array|\DateTime[]
	 */
	public static function getDatesBetween(\DateTime $fromDate, \DateTime $toDate, $toExcluded = false)
	{
		$days = [];
		$fromDate->setTime(0, 0);
		//If to date is excluded, set the time at 00:00:00, the comparison will exclude it
		if($toExcluded)
			$toDate->setTime(0, 0);
		//If to date is not excluded, set the time at least at 00:00:01, the comparison will include it
		else
			$toDate->setTime(0, 0, 1);
		
		$crawledDate = clone $fromDate;
		while($crawledDate < $toDate){
			$days[] = clone $crawledDate;
			
			$crawledDate->modify('+1 days');
		}
		
		return $days;
	}
}
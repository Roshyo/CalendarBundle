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
     * If $toExcluded is set to true, the last day will not be included in the result (eg $toDate = monday, this week
     * will not be included)
     *
     * @param \DateTime $fromDate
     * @param \DateTime $toDate
     * @param bool      $toExcluded
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
     * @param bool      $toExcluded
     *
     * @return array|\DateTime[]
     */
    public static function getDatesBetween(\DateTime $fromDate, \DateTime $toDate, $toExcluded = false): array
    {
        $days = [];
        $fromDate = clone $fromDate;
        $toDate = clone $toDate;
        $fromDate->setTime(0, 0);
        //If to date is excluded, set the time at 00:00:00, the comparison will exclude it
        if($toExcluded)
            $toDate->setTime(0, 0);
        //If to date is not excluded, set the time at least at 00:00:01, the comparison will include it
        else
            $toDate->setTime(0, 0, 1);
        
        $crawledDate = clone $fromDate;
        while ($crawledDate <= $toDate) {
            $days[] = clone $crawledDate;
            
            $crawledDate->modify('+1 days');
        }
        
        return $days;
    }

    /**
     * @param \DateTime $dateToChange
     * @param \DateTime $referenceDate
     * @param bool $up
     */
    public static function changeDateToAppropriateMoment(\DateTime &$dateToChange, \DateTime $referenceDate, bool $up = false)
    {
        $referenceMinutes = (int)$referenceDate->format('H') * 60 + (int)$referenceDate->format('i');

        if ($up) // I try to find the nearest start after this date.
        {
            if ($referenceMinutes >= 14 * 60) {
                $dateToChange->modify('+1 day');
                $dateToChange->setTime(8, 0);
            } else if ($referenceMinutes >= 8)
                $dateToChange->setTime(14, 0);
            else
                $dateToChange->setTime(8, 0);

        } else // I try to find the nearest end behind that date.
        {
            if ($referenceMinutes < 12 * 60) {
                $dateToChange->modify('-1 day');
                $dateToChange->setTime(18, 0);
            } else if ($referenceMinutes < 18 * 60)
                $dateToChange->setTime(12, 0);
            else
                $dateToChange->setTime(18, 0);
        }
    }

    /**
     * get the hours between two dates.
     *
     * @param \DateTime $date1
     * @param \DateTime $date2
     *
     * @return int
     */
    public static function getHoursBetween(\DateTime $date1, \DateTime $date2)
    {
        $diff = $date2->diff($date1);

        $duration = (int)($diff->days * 24); // there is 24 hours in a day.
        $duration += (int)$diff->h; //get the hours of the day. 10am30 => 10.

        return $duration;
    }
}
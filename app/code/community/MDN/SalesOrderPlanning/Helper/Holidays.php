<?php

class MDN_SalesOrderPlanning_Helper_Holidays extends Mage_Core_Helper_Abstract
{
	/**
	 * return true the days in apramater is non worked
	 *
	 * @param timestamp $dateTimestamp
	 * @param int $storeId
	 */
	public function isHolyDay($dateTimestamp,$storeId)
	{
		$day = date('d', $dateTimestamp);
		$month = date('m', $dateTimestamp);
		$dayId = date('w', $dateTimestamp);

		//WEEK END
		$weekendDay = Mage::getStoreConfig('general/locale/weekend');	//,$storeId : removed cause magento crashes if store deleted :(
		$pos = strpos($weekendDay, $dayId);
		if (!($pos === false))
			return true;

		//YEARLY NON6WORKING DAYS
		$nonWorkingDays = $this->getConfTextAreaAsArray('planning/consider/non_working_days',',');
		if(in_array($day.'-'.$month,$nonWorkingDays))
			return true;
		
		return false;
	}

	public function getConfTextAreaAsArray($confPath, $separator, $defaultValue = '')
	{
		$confValues = Mage::getStoreConfig($confPath);
		if (strlen($confValues)>0) {
			$confEntries = explode($separator, $confValues);
			foreach ($confEntries as $index => $value) {
				$confEntries[$index] = trim($value);
			}
			return $confEntries;
		}
		return $defaultValue;
	}
	
	/**
	 * Return next day that is not holy day
	 *
	 * @param timestamp $dateTimestamp
	 * @param int $storeId
	 */
	public function getNextDayThatIsNotHolyday($dateTimestamp,$storeId)
	{
		$daysInSecond = 3600 * 24;
		$dateTimestamp += $daysInSecond;
		while($this->isHolyDay($dateTimestamp,$storeId)){
			$dateTimestamp += $daysInSecond;
		}
		return $dateTimestamp;
	}
	
	/**
	 * Return date + days avoiding holy days
	 * Caution : return timestamp
	 *
	 * @param timestamp $dateTimestamp
	 * @param int $dayCount
	 * @param int $storeId
	 */
	public function addDaysWithoutHolyDays($fromDateTimestamp, $dayCount,$storeId)
	{
		for($i=1;$i<=$dayCount;$i++){
			$fromDateTimestamp = $this->getNextDayThatIsNotHolyday($fromDateTimestamp,$storeId);
		}
		return $fromDateTimestamp;
	}
}
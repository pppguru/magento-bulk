<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright  Copyright (c) 2009 Maison du Logiciel (http://www.maisondulogiciel.com)
 * @author : Olivier ZIMMERMANN
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MDN_Purchase_Helper_RemainingSupplyQuantities extends Mage_Core_Helper_Abstract
{
	/**
	 * Return color to illustrate purchase order late
	 *
	 * @param unknown_type $date
	 */
	public function getColorForDate($date)
	{
		$now = time();
		$poDate = strtotime($date);
		
		$diff = ($now - $poDate) / (60 * 60 * 24);
		
		$daySeverityLow = mage::getStoreConfig('purchase/remaining_supply_qties/severity_low');
		$daySeverityMedium = mage::getStoreConfig('purchase/remaining_supply_qties/severity_medium');
		
		if ($diff < $daySeverityLow)
			return 'green';
		if ($diff < $daySeverityMedium)
			return 'orange';
		return 'red';
	}
	
}
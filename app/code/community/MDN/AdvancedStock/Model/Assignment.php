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
class MDN_AdvancedStock_Model_Assignment extends Mage_Core_Model_Abstract
{
	const _assignmentSales = 'sales';
	const _assignmentProductReturn = 'product_return';
	const _assignmentLeads = 'leads';
	const _assignmentNone = 'none';
	const _assignmentLostBroken = 'lost_broken';
	const _assignmentOrderPreparation = 'order_preparation';
	const _assignmentRmaReservation = 'rma_reservation';
	
	public function _construct()
	{
		parent::_construct();
		$this->_init('AdvancedStock/Assignment');
	}	

	/**
	 * Return assignments array
	 *
	 * @return unknown
	 */
	public function getAssignments()
	{
		
		$retour = array();
		$retour[] = self::_assignmentSales ;
		$retour[] = self::_assignmentProductReturn ;
		//$retour[] = self::_assignmentLeads ;
		//$retour[] = self::_assignmentLostBroken ;
		$retour[] = self::_assignmentOrderPreparation ;
		$retour[] = self::_assignmentRmaReservation ;
		return $retour;
	}


	/**
	 * return considered websites ids for stock/assignment
	 *
	 * @param unknown_type $stockId
	 * @param unknown_type $assignmentType
	 */
	public function getWebsitesForWarehouseAssignment($warehouseId, $assignmentType)
	{
		$retour = array();
		
		$collection = mage::getModel('AdvancedStock/Assignment')
							->getCollection()
							->addFieldToFilter('csa_assignment', $assignmentType)
							->addFieldToFilter('csa_stock_id', $warehouseId);
		foreach ($collection as $item)
		{
			$retour[] = $item->getcsa_website_id();
		}
		
		return $retour;
	}

}
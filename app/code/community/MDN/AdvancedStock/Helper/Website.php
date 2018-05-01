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
class MDN_AdvancedStock_Helper_Website extends Mage_Core_Helper_Abstract
{
	/**
	 * Return available qty for sale for website for product
	 *
	 * @param unknown_type $websiteId
	 * @param unknown_type $productId
	 */
	public function getAvailableQtyForSale($websiteId, $productId)
	{
		$availableQty = 0;
		$stocks = mage::helper('AdvancedStock/Product_Base')->getStocksForWebsiteAssignment($websiteId, MDN_AdvancedStock_Model_Assignment::_assignmentSales, $productId);
		foreach ($stocks as $stock)
			$availableQty += $stock->getAvailableQty();	
		return $availableQty;
	}
}
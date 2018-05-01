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
class MDN_AdvancedStock_Helper_Product_Cost extends Mage_Core_Helper_Abstract
{
	public $debug = '';

	/**
	 * Return product cost
	 *
	 * @param unknown_type $product
	 * @param unknown_type $date
	 */
	public function getProductCostAtDate($product, $date, $qty, $warehouse)
	{
		return $product->getcost();
	}
}
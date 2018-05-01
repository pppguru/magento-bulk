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
class MDN_Purchase_Block_Widget_Column_Renderer_OrderProduct_SalesHistoryGlobal
	extends MDN_Purchase_Block_Widget_Column_Renderer_OrderProduct_Abstract {

    public function render(Varien_Object $row)
	{
		$salesHistoryByWarehouse = array();
		$salesHistoryByWarehouse[1] = 0;
		$salesHistoryByWarehouse[2] = 0;
		$salesHistoryByWarehouse[3] = 0;

		$productId = $row->getentity_id();

		if ($row->getpop_product_id() > 0) {
			$productId = $row->getpop_product_id();
		}

		if($productId > 0) {
			$collection = mage::helper('AdvancedStock/Product_Base')->getStocksToDisplay($productId);
			foreach ($collection as $stockItem) {
				if ($stockItem->ManageStock()) {

					$helper = mage::helper('AdvancedStock/Sales_History');
					$salesHistory = $helper->getForOneProduct($productId, (int)$stockItem->getstock_id());
					$ranges = $helper->getRanges();
					$rangeCount = 0;
					foreach ($ranges as $range) {
						$period = 'getsh_period_' . ++$rangeCount;
						$salesHistoryByWarehouse[$rangeCount] += (int)$salesHistory->$period();
					}
				}
			}

		}

        return implode(' / ', $salesHistoryByWarehouse);
    }

}
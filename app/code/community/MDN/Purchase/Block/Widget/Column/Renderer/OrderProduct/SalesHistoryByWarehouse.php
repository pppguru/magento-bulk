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
class MDN_Purchase_Block_Widget_Column_Renderer_OrderProduct_SalesHistoryByWarehouse
	extends MDN_Purchase_Block_Widget_Column_Renderer_OrderProduct_Abstract {

    public function render(Varien_Object $row)
	{
		$salesHistoryByWarehouse = array();

		$productId = $row->getentity_id();

		if ($row->getpop_product_id() > 0) {
			$productId = $row->getpop_product_id();
		}

		if($productId>0) {
			$dataToDisplay = array();
			if ($this->getCurrentWarehouse()) {
				$this->getStockHistoryData($productId, $this->getCurrentWarehouse(), $dataToDisplay);
				$salesHistoryByWarehouse[] = implode('/', $dataToDisplay);
			}else{
				$collection = mage::helper('AdvancedStock/Product_Base')->getStocksToDisplay($productId);
				foreach ($collection as $stockItem) {
					if ($stockItem->ManageStock()) {
						$warehouseId = (int)$stockItem->getstock_id();
						$warehouseName = $this->getWarehouseName($stockItem);
						$this->getStockHistoryData($productId, $warehouseId, $dataToDisplay);
						$salesHistoryByWarehouse[] = $warehouseName . ' : ' . implode('/', $dataToDisplay);
					}
				}
			}
		}

        return  implode('<br/>', $salesHistoryByWarehouse);
    }
	public function getStockHistoryData($productId, $warehouseId, &$dataToDisplay){
		$salesHistory = mage::helper('AdvancedStock/Sales_History')->getForOneProduct($productId, $warehouseId);
		$ranges = mage::helper('AdvancedStock/Sales_History')->getRanges();
		$rangeCount = 0;
		foreach ($ranges as $range) {
			$period = 'getsh_period_' . ++$rangeCount;
			$dataToDisplay[] = (int)$salesHistory->$period();
		}
	}

	public function getWarehouseName($stock){
		return ($stock->getstock_code()) ? $stock->getstock_code() : $stock->getstock_name();
	}

	public function getCurrentWarehouse(){
		return Mage::getSingleton('adminhtml/session')->getData('supply_needs_warehouse');
	}

}
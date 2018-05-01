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
class MDN_Purchase_Block_Widget_Column_Renderer_OrderProduct_SalesHistory extends MDN_Purchase_Block_Widget_Column_Renderer_OrderProduct_Abstract {

    public function render(Varien_Object $row)
	{
		$html = '';

		if (Mage::getSingleton('admin/session')->isAllowed('admin/erp/products/view/sales_history/sales_history')) {

			$dataToDisplay = array();

			$productId = $row->getId();
			if ($row->getpop_product_id() > 0) {
				$productId = $row->getpop_product_id();
			}

			//sales History
			$ranges = mage::helper('AdvancedStock/Sales_History')->getRanges();
			$salesHistory = mage::getModel('AdvancedStock/SalesHistory')->load($productId, 'sh_product_id');
			$rangeCount = 0;
			foreach($ranges as $range){
				$period = 'getsh_period_'.++$rangeCount;
				$dataToDisplay[] = (int)$salesHistory->$period();
			}
			$html = implode('/',$dataToDisplay);
		}

        return $html;
    }

}
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
class MDN_Purchase_Block_Product_Widget_Column_Renderer_OrdererQty extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $value = $row->getpop_qty();

        //convert units if packagings enabled in sales unit
        if (mage::helper('purchase/Product_Packaging')->isEnabled())
        {
            $productId = $row->getpop_product_id();
            $value = mage::helper('purchase/Product_Packaging')->convertToSalesUnit($productId, $value);
        }

    	return $value.'';
    }
}
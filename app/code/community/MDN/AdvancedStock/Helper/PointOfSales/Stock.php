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
class MDN_AdvancedStock_Helper_PointOfSales_Stock extends MDN_PointOfSales_Helper_Stock
{
    public function getProductStockInfo($product)
    {
        $websiteId = $this->getWebsiteId();

        $stocks = mage::helper('AdvancedStock/Product_Base')->getStocksForWebsiteAssignment($websiteId, MDN_AdvancedStock_Model_Assignment::_assignmentSales, $product->getId());
        $retour = '';
        foreach ($stocks as $stock)
        {
            $retour .= '<b>'.$stock->getstock_name().'</b> : '.((int)$stock->getqty()).'<br>';
        }

        $retour = '<div class="nowrap">'.$retour.'</div>';

        return $retour;
    }


    public function getAvailableQuantityForSale($product, $websiteId = 0)
    {
        $stocks = mage::helper('AdvancedStock/Product_Base')->getStocksForWebsiteAssignment($websiteId, MDN_AdvancedStock_Model_Assignment::_assignmentSales, $product->getId());
        $value = 0;
        foreach ($stocks as $stock)
        {
            $value += $stock->getAvailableQty();
        }
        return $value;
    }

}

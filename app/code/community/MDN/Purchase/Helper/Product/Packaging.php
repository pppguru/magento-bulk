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
class MDN_Purchase_Helper_Product_Packaging extends Mage_Core_Helper_Abstract
{
	
	/**
	 * return true if packaging feature is enabled
	 *
	 * @return unknown
	 */
	public function isEnabled()
	{
		return (mage::getStoreConfig('purchase/packaging/enable') == 1);
	}

	/**
	 * Return packagings for 1 product
	 *
	 * @param unknown_type $productId
	 * @return unknown
	 */
	public function getPackagingForProduct($productId)
	{
		$collection = mage::getModel('Purchase/Packaging')
									->getCollection()
									->addFieldToFilter('pp_product_id', $productId);
									
		return $collection;
	}
	
	/**
	 * Return default sales packaging for 1 product
	 *
	 * @param unknown_type $productId
	 * @return unknown
	 */
	public function getDefaultSalesPackaging($productId)
	{
		return mage::getModel('Purchase/Packaging')
									->getCollection()
									->addFieldToFilter('pp_product_id', $productId)
									->addFieldToFilter('pp_is_default_sales', 1)
									->getFirstItem();		
	}

	/**
	 * Return default purchase packaging for 1 product
	 *
	 * @param unknown_type $productId
	 * @return unknown
	 */
	public function getDefaultPurchasePackaging($productId)
	{
		return mage::getModel('Purchase/Packaging')
									->getCollection()
									->addFieldToFilter('pp_product_id', $productId)
									->addFieldToFilter('pp_is_default', 1)
									->getFirstItem();		
	}
	
	public function convertToPurchaseUnit($productId, $qty)
	{
            $packaging = $this->getDefaultPurchasePackaging($productId);
            if ($packaging && $packaging->getId())
                $qty = ceil($qty / $packaging->getpp_qty());
            return $qty;
	}

	public function convertSalesToUnit($productId, $qty)
	{
            $packaging = $this->getDefaultSalesPackaging($productId);
            if ($packaging && $packaging->getId())
                $qty = $qty * $packaging->getpp_qty();
            return $qty;
	}

        /**
         * Convert qty to sales unit
         * @param <type> $productId
         * @param <type> $qty
         * @return <type>
         */
	public function convertToSalesUnit($productId, $qty)
	{
            $packaging = $this->getDefaultSalesPackaging($productId);
            if ($packaging && $packaging->getId())
                $qty = (int)($qty / $packaging->getpp_qty());

            return $qty;
	}
	
	public function convertPurchaseToSalesUnit($productId, $qty)
	{
            //todo : implement
            die('to implement');
	}

        /**
         * Create combo box with available packagings for product
         * @param <type> $productId
         * @param <type> $name
         * @param <type> $defaultValue
         * @param <type> $onchange
         * @return string
         */
	public function getPackagingPurchaseCombobox($productId, $name, $defaultValue, $onchange, $selectDefaultPurchase = false)
	{
		$html = '<select name="'.$name.'" id="'.$name.'" onchange="'.$onchange.'">';		
		$html .= '<option value="-1">'.$this->__('None').'</option>';
		
		$collection = $this->getPackagingForProduct($productId);
		foreach ($collection as $item)
		{
                    $selected = '';
                    if ($item->getpp_id() == $defaultValue)
                        $selected = ' selected ';
                    else
                    {
                        if ($selectDefaultPurchase && $item->getpp_is_default())
                            $selected = ' selected ';
                    }
                    $label = $item->getpp_name().' ('.$item->getpp_qty().'x)';
                    $html .= '<option value="'.$item->getpp_id().'"'.$selected.'>'.$label.'</option>';
		}
		
		$html .= '</select>';
		return $html;
	}
}
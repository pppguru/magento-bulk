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
class MDN_Purchase_Block_Widget_Column_Renderer_OrderProduct_SupplyNeeds extends MDN_Purchase_Block_Widget_Column_Renderer_OrderProduct_Abstract {

    public function render(Varien_Object $product) {
        
		$supplyNeeds = mage::getResourceModel('Purchase/SupplyNeeds_NewGlobalCollection')->addFieldToFilter('entity_id' , $product->getId())->getFirstItem();
    	$idealStockLevel = mage::helper('AdvancedStock/Product_PreferedStockLevel')->getIdealStockLevelForAllStocks($product->getId());

    	$retour = '<span style="white-space: nowrap;">';
    	$retour .= '<b>'.$this->__('Min : %s', (int)$supplyNeeds->getqty_min()).'</b>';
    	$retour .= ' - '.$this->__('Max : %s', (int)$supplyNeeds->getqty_max());
    	$retour .= '<br><i>('.$idealStockLevel.')</i>';
    	$retour .= '</span>';

        return $retour;
    }

}
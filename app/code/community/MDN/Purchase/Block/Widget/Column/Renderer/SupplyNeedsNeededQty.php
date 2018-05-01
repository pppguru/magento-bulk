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


class MDN_Purchase_Block_Widget_Column_Renderer_SupplyNeedsNeededQty
	extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract 
{
    public function render(Varien_Object $row)
    {
    	$retour = '<span style="white-space: nowrap;">';
    	$retour .= '<b>'.$this->__('Min : %s', $row->getsn_needed_qty_for_valid_orders()).'</b>';
    	$retour .= ' - '.$this->__('Max : %s', $row->getsn_needed_qty());
    	$retour .= '</span>';
    	
    	$productId = $row->getsn_product_id();
    	$idealStockLevel = mage::helper('AdvancedStock/Product_PreferedStockLevel')->getIdealStockLevelForAllStocks($productId);
    	
    	$retour = '<span style="white-space: nowrap;">';
    	$retour .= '<b>'.$this->__('Min : %s', $row->getsn_needed_qty_for_valid_orders()).'</b>';
    	$retour .= ' - '.$this->__('Max : %s', $row->getsn_needed_qty());
    	$retour .= '<br><i>('.$idealStockLevel.')</i>';
    	$retour .= '</span>';

    	return $retour;
    }
    
}
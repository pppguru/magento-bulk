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

class MDN_Purchase_Block_Widget_Column_Renderer_AddProductToOrder
	extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract 
{
    public function render(Varien_Object $row)
    {
    	$ProductId = $row->getId();
    	$url = $this->getUrl('adminhtml/Purchase_Orders/AddProductToOrder', array('ProductId' => $ProductId, 'OrderId' => $this->getColumn()->getpo_num()));
    	$html = '<a href="'.$url.'">'.mage::helper('purchase')->__('Add').'</a>';
		return $html;
    }
    
}
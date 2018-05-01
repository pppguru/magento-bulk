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
/*
* Display all suppliers for one product
*/
class MDN_Purchase_Block_Widget_Column_Renderer_ProductSerial_PurchaseOrder
	extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract 
{
    public function render(Varien_Object $row)
    {
    	$retour = '';
		$purchaseOrderId = $row->getpps_purchaseorder_id();
		if ($purchaseOrderId)
		{
			$purchaseOrder = mage::getModel('Purchase/Order')->load($purchaseOrderId);
			$url = $this->getUrl('adminhtml/Purchase_Orders/Edit', array('po_num' => $purchaseOrderId));
			$label = $purchaseOrder->getpo_order_id();
			$retour = '<a href="'.$url.'">'.$label.'</a>';
		}
					
		return $retour;
    }
    
}
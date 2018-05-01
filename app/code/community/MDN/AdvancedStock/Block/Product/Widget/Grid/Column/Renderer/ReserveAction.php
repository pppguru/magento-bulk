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
class MDN_AdvancedStock_Block_Product_Widget_Grid_Column_Renderer_ReserveAction
	extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract 
{
    public function render(Varien_Object $order)
    {
		$html = '';

    	//retrieve information
    	$productId =  $this->getColumn()->getproduct_id();
    	$collection = mage::getModel('sales/order_item')
    						->getCollection()
    						->addFieldToFilter('order_id', $order->getId())
    						->addFieldToFilter('product_id', $productId);

    	foreach ($collection as $orderItem)
    	{
			if ($orderItem != null)
			{
				$html = $this->getActions($orderItem,$productId,$order);
				break;
			}
    	}

		return $html;
    }

	public function getActions($orderItem,$productId,$order){
		$html ='';

		$params = array('product_id' => $productId, 'order_id' => $order->getId(), 'order_item_id' => $orderItem->getId());
		$reserveUrl = Mage::helper('adminhtml')->getUrl('adminhtml/AdvancedStock_Products/ReserveProductForOrder', $params);
		$releaseUrl = Mage::helper('adminhtml')->getUrl('adminhtml/AdvancedStock_Products/ReleaseProductForOrder', $params);

		if (($orderItem->getreserved_qty() == 0) && ($orderItem->getRemainToShipQty() > 0))
			$html .= '<a href="'.$reserveUrl.'">'.mage::helper('AdvancedStock')->__('Reserve').'</a><br>';

		if ($orderItem->getreserved_qty() > 0)
			$html .= '<a href="'.$releaseUrl.'">'.mage::helper('AdvancedStock')->__('Release').'</a>';

		return $html;
	}
    
}
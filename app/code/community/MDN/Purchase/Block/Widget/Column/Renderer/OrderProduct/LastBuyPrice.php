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

class MDN_Purchase_Block_Widget_Column_Renderer_OrderProduct_LastBuyPrice
	extends MDN_Purchase_Block_Widget_Column_Renderer_OrderProduct_Abstract 
{
    public function render(Varien_Object $row)
    {
		$productId = $row->getpop_product_id();
		$html = $this->GetLastPriceWithoutFees($productId);
		return $html;
    }
	
	public function GetLastPriceWithoutFees($ProductId)
	{
		$sql = 'select pop_price_ht_base from '.mage::getModel('Purchase/Constant')->getTablePrefix().'purchase_order_product, '.mage::getModel('Purchase/Constant')->getTablePrefix().'purchase_order where pop_order_num = po_num and po_status = \''.MDN_Purchase_Model_Order::STATUS_COMPLETE.'\' and pop_price_ht_base > 0 and pop_product_id = '.$ProductId.' order by po_num DESC LIMIT 1';
		$retour = mage::getResourceModel('sales/order_item_collection')->getConnection()->fetchOne($sql);
		if ($retour > 0)
			$retour = number_format($retour, 2);
		else
			$retour = '';
		return $retour;
	}
    
}
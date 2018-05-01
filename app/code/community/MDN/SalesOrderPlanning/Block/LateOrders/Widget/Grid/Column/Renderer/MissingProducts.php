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

class MDN_SalesOrderPlanning_Block_LateOrders_Widget_Grid_Column_Renderer_MissingProducts extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract 
{
    public function render(Varien_Object $row)
    {
		$order = $row;
		$html = '';
		foreach ($order->getAllItems() as $orderItem)
		{
			$remainingToReserveQty = $orderItem->getRemainToShipQty() - $orderItem->getreserved_qty();
			if (($remainingToReserveQty > 0))
			{
				$html .= $remainingToReserveQty.'x '.$orderItem->getname()."<br>";
			}
		}
		return $html;
    }
    
}
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

/**
* retourne les elements a envoyer pour une commande selectionnee pour la preparation de commandes
*/
class MDN_Purchase_Block_RemainingSupplyQuantities_Widget_Grid_Column_Renderer_PendingCustomerOrdersQty
	extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract 
{
	public function render(Varien_Object $row)
	{
		$productId = $row->getId();

		$stocks = mage::helper('AdvancedStock/Product_Base')->getStocks($productId);
		$validOrderedQtyCount = 0;
		$orderedQtyCount = 0;

		foreach ($stocks as $stock)
		{
			$tempValidOrderedQtyCount = $stock->getstock_ordered_qty_for_valid_orders() - $stock->getQty();
			if ($tempValidOrderedQtyCount < 0)
				$tempValidOrderedQtyCount = 0;

			$tempOrderedQtyCount = $stock->getstock_ordered_qty() - $stock->getQty();
			if ($tempOrderedQtyCount < 0)
				$tempOrderedQtyCount = 0;

			$validOrderedQtyCount += $tempValidOrderedQtyCount;
			$orderedQtyCount += $tempOrderedQtyCount;
		}

		$validOrderedQtyCount = (int)$validOrderedQtyCount;
		$orderedQtyCount = (int)$orderedQtyCount;

		if ($validOrderedQtyCount == 0)
			$validOrderedQtyCount = "0";

		if ($orderedQtyCount == 0)
			$orderedQtyCount = "0";

		return $validOrderedQtyCount.'/'.$orderedQtyCount;
	}
}
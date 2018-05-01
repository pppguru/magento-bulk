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
* retourne les �l�ments � envoyer pour une commande s�lectionn�e pour la pr�paration de commandes
*/
class MDN_Purchase_Block_RemainingSupplyQuantities_Widget_Grid_Column_Renderer_PurchaseOrder
	extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract 
{
	public function render(Varien_Object $row)
    {
    	$html = '<table border="1" cellspacing="0">';

    	$productId = $row->getId();
    	$collection = mage::getResourceModel('Purchase/Order_collection')->getPendingOrdersForProduct($productId);
    	foreach ($collection as $item)
    	{
    		$supplierUrl = $this->getUrl('adminhtml/Purchase_Suppliers/Edit/', array('sup_id' => $item->getsup_id()));
    		$orderUrl = $this->getUrl('adminhtml/Purchase_Orders/Edit/', array('po_num' => $item->getpo_num()));

            $supplyDate = $item->getpo_supply_date();
            if ($item->getpop_delivery_date())
                $supplyDate = $item->getpop_delivery_date();

			$carrier = $item->getpo_carrier();
			$tracking = $item->getpo_tracking();
            $color = mage::helper('purchase/RemainingSupplyQuantities')->getColorForDate($supplyDate);

    		$html .= '<tr>';
    		$html .= '<td><a href="'.$supplierUrl.'" target="_blank">'.$item->getsup_name().'</a></td>';
    		$html .= '<td><a href="'.$orderUrl.'" target="_blank">'.$item->getpo_order_id().'</a></td>';
    		$html .= '<td>'.$item->getpop_supplier_ref().'</td>';
    		$html .= '<td><font color="'.$color.'">'.$supplyDate.'</font></td>';
    		$html .= '<td>'.$carrier.'</td>';
    		$html .= '<td>'.$tracking.'</td>';

            $suppliedQty = mage::helper('purchase/Product_Packaging')->convertToSalesUnit($productId, $item->getpop_supplied_qty());
            $orderedQty = mage::helper('purchase/Product_Packaging')->convertToSalesUnit($productId, $item->getpop_qty());

    		$html .= '<td nowrap>'.$suppliedQty.' / '.$orderedQty.'</td>';
    		$html .= '</tr>';
    	}
    	
    	$html .= '</table>';

    	return $html;
    }
    
    public function renderExport(Varien_Object $row)
    {
    	$csv = '';
    	
    	$productId = $row->getId();
        if($productId) {
            $collection = mage::getResourceModel('Purchase/Order_collection')->getPendingOrdersForProduct($productId);
            foreach ($collection as $item) {
                $csv .= '(' . $item->getsup_name() . ' - ' . $item->getpo_order_id() . ' - ' . $item->getpo_supply_date() . ' - ' . ($item->getpop_supplied_qty() . '/' . $item->getpop_qty()) . ') ';
            }
        }

    	return $csv;
    }
}
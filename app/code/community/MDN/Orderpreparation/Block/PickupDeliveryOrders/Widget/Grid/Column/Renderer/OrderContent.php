<?php

class MDN_Orderpreparation_Block_PickupDeliveryOrders_Widget_Grid_Column_Renderer_OrderContent extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract 
{
    public function render(Varien_Object $row)
    {
    	$retour = '';
    	$websiteId = $row->getStore()->getwebsite_id();
    	
		foreach ($row->getItemsCollection() as $item) 
		{
            $remaining_qty = $item->getRemainToShipQty();
            $productId = $item->getproduct_id();
            $name = $item->getName();
            $name .= mage::helper('AdvancedStock/Product_ConfigurableAttributes')->getDescription($productId);
            
            if ($remaining_qty > 0) {
                
                $productStockManagement = Mage::getModel('cataloginventory/stock_item')->loadByProduct($productId);
                if ($productStockManagement->getManageStock()) {
                    if ($item->getreserved_qty() >= $remaining_qty) {
                        $retour .= "<font color=\"green\">" . ((int) $remaining_qty) . 'x ' . $name . "</font>";
                    } else {
                        if (($item->getreserved_qty() < $remaining_qty) && ($item->getreserved_qty() > 0)) {
                            $retour .= "<font color=\"orange\">" . ((int) $remaining_qty) . 'x ' . $name . " (" . $item->getreserved_qty() . '/' . $remaining_qty . ")</font>";
                        } else {
                            $availableStock = mage::helper('AdvancedStock/Product_Base')->getAvailableQty($productId, $websiteId);
                            if ($remaining_qty <= $availableStock)
                                $retour .= ( (int) $remaining_qty) . 'x ' . $name;
                            else
                                $retour .= "<font color=\"red\">" . ((int) $remaining_qty) . 'x ' . $name . "</font>";
                        }
                    }
                    $retour .= "<br>";
                }
                else
                    $retour .= "<i>" . $name . "</i><br>";
            }
            else {
                $retour .= "<s>" . ((int) $item->getqty_ordered()) . 'x ' . $name . "</s><br>";
            }
        }
    	
        return $retour;
    }
}
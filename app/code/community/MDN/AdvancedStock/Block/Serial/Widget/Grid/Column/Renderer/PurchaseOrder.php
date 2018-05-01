<?php

class MDN_AdvancedStock_Block_Serial_Widget_Grid_Column_Renderer_PurchaseOrder extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract 
{
    public function render(Varien_Object $row)
    {
    	$retour = '';

        if ($row->getpps_purchaseorder_id())
    	{
    		$purchaseOrderId = $row->getpps_purchaseorder_id();
    		$purchaseOrder = mage::getModel('Purchase/Order')->load($purchaseOrderId);
            if($purchaseOrder->getId()>0){
                $url = $this->getUrl('adminhtml/Purchase_Orders/Edit', array('po_num' => $purchaseOrderId));
                $retour = '<a href="'.$url.'" target="_blanck">'.$purchaseOrder->getpo_order_id().'</a>';
            }
    	}

        return $retour;
    }
    
}

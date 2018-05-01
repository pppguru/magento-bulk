<?php

class MDN_AdvancedStock_Block_Product_Widget_Grid_Column_Renderer_StockStatus
	extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract 
{
    public function render(Varien_Object $row)
    {
    	$color = '#000000';
    	
    	switch ($row->getStatus()) {
    		case MDN_AdvancedStock_Model_CatalogInventory_Stock_Item::_StatusOk:
		    	$color = '#00FF00';
    			break;
    		case MDN_AdvancedStock_Model_CatalogInventory_Stock_Item::_StatusSalesOrder :
		    	$color = '#FF0000';
    			break;
    		case MDN_AdvancedStock_Model_CatalogInventory_Stock_Item::_StatusQtyMini :
		    	$color = '#FF8040';
    			break;
    	}
    	$retour = '<font color="'.$color.'">';
		$retour .= $this->__($row->getStatus());
		$retour .= '</font>';
		
		return $retour;
    }
    
}
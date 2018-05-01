<?php

class MDN_AdvancedStock_Block_Adminhtml_Sales_OrderItem_Widget_Grid_Column_Renderer_PreparationWarehouse
	extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract 
{
    public function render(Varien_Object $row)
    {
		//manually load it, because row can be sales_order instead of sales_order_item
		$preparationWarehouseCode = $row->getpreparation_warehouse();		
		if ($preparationWarehouseCode)
		{
			$preparationWarehouse = mage::getModel('AdvancedStock/Warehouse')->load($preparationWarehouseCode);
			return $preparationWarehouse->getstock_name();
		}
		else
			return '<font color="red">'.$this->__('Undefined').'</font>';
    }
    
}
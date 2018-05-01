<?php


class MDN_AdvancedStock_Block_Product_Widget_Grid_Column_Renderer_PreparationWarehouse extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $html = '';
        $orderId = $row->getId();
        $productId =  $this->getColumn()->getproduct_id();

        $orderItem = mage::getModel('sales/order_item')
    						->getCollection()
    						->addFieldToFilter('order_id', $orderId)
    						->addFieldToFilter('product_id', $productId)
                            ->getFirstitem();

        $orderItemId = $orderItem->getId();

		$item = mage::getModel('AdvancedStock/SalesFlatOrderItem')->load($orderItemId);
		$preparationWarehouseCode = $item->getpreparation_warehouse();
		if ($preparationWarehouseCode)
		{
			$preparationWarehouse = mage::getModel('AdvancedStock/Warehouse')->load($preparationWarehouseCode);
			$html.= $preparationWarehouse->getstock_name();
		}
		else
			$html.= '<font color="red">'.$this->__('Undefined').'</font>';

        return $html;
    }

}
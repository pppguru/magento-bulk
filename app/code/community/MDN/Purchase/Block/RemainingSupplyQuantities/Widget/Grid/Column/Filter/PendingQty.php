<?php

class MDN_Purchase_Block_RemainingSupplyQuantities_Widget_Grid_Column_Filter_PendingQty extends Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Select
{
	protected function _getOptions()
    {
        $html = array();
		$html[] = array('label' => '', 'value' => '');
		$html[] = array('label' => $this->__('Greater than 0'), 'value' => 'greater_than_zero');
        return $html;
    }

	public function getCondition()
	{
		$productIds = array();

		if($this->getValue() == 'greater_than_zero') {
			$collection = mage::getResourceModel('cataloginventory/stock_item_collection');
			$collection->getSelect()
					->where("(stock_ordered_qty_for_valid_orders > qty) OR (stock_ordered_qty > qty)");

			foreach ($collection as $item) {
				$productIds[] = $item->getproduct_id();
			}
		}

		if (count($productIds) > 0)
			return array('in' => $productIds);
		else
			return null;

	}
    
}
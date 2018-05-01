<?php

class MDN_AdvancedStock_Block_Product_Widget_Grid_Column_Renderer_AvailableQty
	extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract 
{
    public function render(Varien_Object $row)
    {
		$html = $row->getAvailableQty();
		if ($html == 0)
			$html = '0';
		return '<b>'.$html.'</b>';
    }
    
}
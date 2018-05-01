<?php

class MDN_AdvancedStock_Block_StockMovement_Widget_Grid_Column_Renderer_DeleteStockMovement extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract 
{
    public function render(Varien_Object $row)
    {
    	$url = $this->getUrl('adminhtml/AdvancedStock_StockMovement/Delete', array('sm_id' => $row->getId()));
    	return '<a href="'.$url.'">'.$this->__('Delete').'</a>';
    }
    
}
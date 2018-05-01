<?php

class MDN_AdvancedStock_Block_Widget_Grid_Column_Renderer_ConfigurableAttributes extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract 
{
    public function render(Varien_Object $row)
    {
    	$getter = $this->getColumn()->getgetter();
    	$productId = $row->getData($getter);
    	return mage::helper('AdvancedStock/Product_ConfigurableAttributes')->getDescription($productId);
    }
    
}
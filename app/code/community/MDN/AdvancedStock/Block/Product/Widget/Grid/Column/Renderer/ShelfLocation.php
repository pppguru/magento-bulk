<?php

class MDN_AdvancedStock_Block_Product_Widget_Grid_Column_Renderer_ShelfLocation
	extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract 
{
    public function render(Varien_Object $row)
    {
		$id = 'shelf_location_'.$row->getId().'';
		$value = $row->getshelf_location();
		$size = 6;
		return '<input size="'.$size.'" type="text" value="'.$value.'" id="'.$id.'" name="'.$id.'"><br>';
    }
    
}
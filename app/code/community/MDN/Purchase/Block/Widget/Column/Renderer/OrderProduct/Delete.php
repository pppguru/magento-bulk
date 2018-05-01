<?php

class MDN_Purchase_Block_Widget_Column_Renderer_OrderProduct_Delete
	extends MDN_Purchase_Block_Widget_Column_Renderer_OrderProduct_Abstract 
{
    public function render(Varien_Object $row)
    {
		$checkBoxName = 'delete_'.$row->getpop_num();
	
		$value = '';
		$html = '<input onclick="persistantProductGrid.logChange(this.name, 0)" type="checkbox" name="'.$checkBoxName.'" id="'.$checkBoxName.'" value="1">';
		return $html;
    }
	
	public function getFieldName()
	{
		return 'delete';
	}
    
}
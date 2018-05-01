<?php

class MDN_AdvancedStock_Block_StockMovement_Widget_Grid_Column_Renderer_Picto
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
            $direction = ($row->getsm_source_stock() && !$row->getsm_target_stock())?'decrease':'increase';
            $direction = ($row->getsm_source_stock() && $row->getsm_target_stock())?'forward':$direction;
            return '<img src="'.$this->getSkinUrl('images/advancedstock/stockmovement/'.$direction.'.png').'">';
    }

}
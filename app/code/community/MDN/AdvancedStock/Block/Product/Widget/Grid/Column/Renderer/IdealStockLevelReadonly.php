<?php

class MDN_AdvancedStock_Block_Product_Widget_Grid_Column_Renderer_IdealStockLevelReadonly extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) {
        //init vars
        $value = $row->getIdealStockLevel();
        if ($value == 0)
            $value = '0';

        return $value;
    }

}
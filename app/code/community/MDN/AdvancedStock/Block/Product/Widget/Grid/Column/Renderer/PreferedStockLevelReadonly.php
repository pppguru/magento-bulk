<?php

class MDN_AdvancedStock_Block_Product_Widget_Grid_Column_Renderer_PreferedStockLevelReadonly extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) {
        $retour = (int) $row->getWarningStockLevel();
        if ($retour == 0)
            $retour = '0';
        return $retour;
    }

}
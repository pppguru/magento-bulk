<?php

class MDN_AdvancedStock_Block_MassStockEditor_Widget_Grid_Column_Renderer_IdealStockLevel extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) {
        
        $stockId = $row->getitem_id();
        $idealStockLevel = (int)$row->getideal_stock_level();
        $useConfig = $row->getuse_config_ideal_stock_level();
        if ($useConfig)
            $idealStockLevel = Mage::getStoreConfig('advancedstock/prefered_stock_level/ideal_stock_default_value');
        $disabled = ($useConfig ? ' disabled ' : '');
        $checked = ($useConfig ? ' checked ' : '');

        $onChange = 'onchange="persistantGrid.logChange(this.name, \''.$idealStockLevel.'\')"';
        $retour = '<input '.$onChange.' type="text" name="ideal_stock_level_' . $stockId . '" id="ideal_stock_level_' . $stockId . '" value="' . $idealStockLevel . '" size="4" '.$disabled.'>';
        
        $onChange = 'onchange="toggleTextboxDisable(this, \'ideal_stock_level_' . $stockId . '\');persistantGrid.logChange(this.name, \'\');"';
        $retour .= '<br>&nbsp;<input '.$onChange.' type="checkbox" name="use_config_ideal_stock_level_' . $stockId . '" id="use_config_ideal_stock_level_' . $stockId . '" value="1" '.$checked.' > '.$this->__('Use default');
        
        return $retour;
    }

}
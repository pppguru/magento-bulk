<?php

class MDN_AdvancedStock_Block_MassStockEditor_Widget_Grid_Column_Renderer_WarningStockLevel extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) {

        $stockId = $row->getitem_id();
        $warningStockLevel = (int) $row->getnotify_stock_qty();
        $useConfig = $row->getuse_config_notify_stock_qty();
        if ($useConfig)
            $warningStockLevel = Mage::getStoreConfig('cataloginventory/item_options/notify_stock_qty');
        $disabled = ($useConfig ? ' disabled ' : '');
        $checked = ($useConfig ? ' checked ' : '');

        $onChange = 'onchange="persistantGrid.logChange(this.name, \''.$warningStockLevel.'\')"';
        $retour = '<input '.$onChange.' type="text" name="notify_stock_qty_' . $stockId . '" id="notify_stock_qty_' . $stockId . '" value="' . $warningStockLevel . '" size="4" ' . $disabled . '>';
        
        $onChange = 'onchange="toggleTextboxDisable(this, \'notify_stock_qty_' . $stockId . '\');persistantGrid.logChange(this.name, \'\');"';
        $retour .= '<br>&nbsp;<input  '.$onChange.' type="checkbox" name="use_config_notify_stock_qty_' . $stockId . '" id="use_config_notify_stock_qty_' . $stockId . '" value="1" ' . $checked . ' > ' . $this->__('Use default');

        return $retour;
    }

}
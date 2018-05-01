<?php

class MDN_AdvancedStock_Block_Product_Widget_Grid_Column_Renderer_IdealStockLevel extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) {
        $retour = '';

        //init vars
        $value = $row->getIdealStockLevel();

        if ($this->getColumn()->getread_only()) {
            $retour = $value;
        } else {

            //textbox
            $textboxName = 'ideal_stock_level_' . $row->getId() . '';
            $enabled = ($row->getuse_config_ideal_stock_level() ? ' disabled="disabled" ' : '');
            $retour = '<input size="4" type="text" value="' . $value . '" id="' . $textboxName . '" name="' . $textboxName . '" ' . $enabled . '><br>';

            //checkbox
            $name = 'use_config_ideal_stock_level_' . $row->getId() . '';
            $checked = ($row->getuse_config_ideal_stock_level() ? ' checked ' : '');
            $onclick = "toggleFieldFromCheckbox('" . $name . "', '" . $textboxName . "')";
            $retour .= '<input type="checkbox" value="1" id="' . $name . '" name="' . $name . '" ' . $checked . ' onclick="' . $onclick . '"> ' . $this->__('Use default');
        }

        return $retour;
    }

}
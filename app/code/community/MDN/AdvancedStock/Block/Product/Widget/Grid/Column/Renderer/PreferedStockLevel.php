<?php

class MDN_AdvancedStock_Block_Product_Widget_Grid_Column_Renderer_PreferedStockLevel extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) {
        $retour = '';

        //init vars
        $value = $row->getWarningStockLevel();

        if ($this->getColumn()->getread_only())
        {
            $retour = $value;
        }
        else
        {
            //textbox
            $textboxName = 'notify_stock_qty_' . $row->getId() . '';
            $enabled = ($row->getuse_config_notify_stock_qty() ? ' disabled="disabled" ' : '');
            $retour = '<input size="4" type="text" value="' . $value . '" id="' . $textboxName . '" name="' . $textboxName . '" ' . $enabled . '><br>';

            //checkbox
            $name = 'use_config_notify_stock_qty_' . $row->getId() . '';
            $checked = ($row->getuse_config_notify_stock_qty() ? ' checked ' : '');
            $onclick = "toggleFieldFromCheckbox('" . $name . "', '" . $textboxName . "')";
            $retour .= '<input type="checkbox" value="1" id="' . $name . '" name="' . $name . '" ' . $checked . ' onclick="' . $onclick . '"> ' . $this->__('Use default');
        }


        return $retour;
    }

}
<?php

class MDN_AdvancedStock_Block_Product_Widget_Grid_Column_Renderer_AutoUpdate extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) {
       
        //init vars
        $value = $row->geterp_exclude_automatic_warning_stock_level_update();

        //textbox
        $name = 'erp_exclude_automatic_warning_stock_level_update_'.$row->getId();

        $values = array(0 => $this->__('No'), 1 => $this->__('Yes'));
        $html = '<select name="' . $name . '" id="' . $name . '">';
        foreach ($values as $key => $label) {
            $selected = '';
            if ($key == $value)
                $selected = ' selected ';
            $html .= '<option value="' . $key . '" ' . $selected . '>' . $label . '</option>';
        }
        $html .= '</select>';

        return $html;
    }

}
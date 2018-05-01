<?php

class MDN_AdvancedStock_Block_Product_Widget_Grid_Column_Renderer_FavoriteWarehouse extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) {
 
        //init vars
        $value = $row->getis_favorite_warehouse();

        //textbox
        $name = 'is_favorite_warehouse_'.$row->getId();

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
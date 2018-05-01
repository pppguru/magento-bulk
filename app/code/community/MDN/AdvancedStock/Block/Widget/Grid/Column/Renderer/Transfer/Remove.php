<?php

class MDN_AdvancedStock_Block_Widget_Grid_Column_Renderer_Transfer_Remove extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    public function render(Varien_Object $row) {
        $checkBoxName = 'delete_' . $row->getId();
        $value = '';
        $html = '<input onclick="persistantProductGrid.logChange(this.name, 0)" 
                        type="checkbox"
                        name="' . $checkBoxName . '"
                        id="' . $checkBoxName . '"
                        value="1">';
        return $html;
    }

    public function getFieldName() {
        return 'remove';
    }

}
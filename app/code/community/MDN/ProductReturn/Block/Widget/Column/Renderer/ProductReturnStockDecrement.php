<?php


class MDN_ProductReturn_Block_Widget_Column_Renderer_ProductReturnStockDecrement extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $html = '';
        $html .= '<input type="checkbox" name="cb_' . $row->getentity_id() . '" id="cb_' . $row->getentity_id() . '" /> from warehouse: ';
        $html .= '<select name="select_' . $row->getentity_id() . '" id="select_' . $row->getentity_id() . '"><option>' . $this->__('Select warehouse') . '</option>';
        $warehouse_col = mage::getModel('AdvancedStock/Warehouse')->getCollection();
        foreach ($warehouse_col as $warehouse) {
            if ($warehouse->getAvailableQty($row->getentity_id()) > 0)
                $html .= '<option value=' . $warehouse->getId() . '>' . $warehouse->getstock_name() . '</option>';
        }
        echo '</select>';

        return $html;
    }
}
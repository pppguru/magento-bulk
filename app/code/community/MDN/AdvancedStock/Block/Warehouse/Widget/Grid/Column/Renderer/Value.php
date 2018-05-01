<?php

class MDN_AdvancedStock_Block_Warehouse_Widget_Grid_Column_Renderer_Value
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $currency = mage::getModel('directory/currency')->load(Mage::getStoreConfig('currency/options/base'));
        return $currency->formatTxt($row->getStockValue());
    }

}
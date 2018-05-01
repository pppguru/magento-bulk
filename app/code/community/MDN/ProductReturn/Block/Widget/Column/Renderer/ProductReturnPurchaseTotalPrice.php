<?php

/*
 * 
 */
class MDN_ProductReturn_Block_Widget_Column_Renderer_ProductReturnPurchaseTotalPrice extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $price = '';
        $price .= round($row->getTotalPurchasePrice(), 2);
        if ($price == '' || $price == 0)
            return 'Unable to get price';

        return $price . ' ' . mage::getStoreConfig('currency/options/base');

    }
}

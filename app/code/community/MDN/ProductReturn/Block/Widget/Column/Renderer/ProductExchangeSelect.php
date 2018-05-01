<?php

class MDN_ProductReturn_Block_Widget_Column_Renderer_ProductExchangeSelect extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{

    public function render(Varien_Object $row)
    {
        $rpId = mage::app()->getRequest()->getParam('rp_id');

        //init vars
        $name                              = $row->getname();
        $productId                         = $row->getId();
        $configurableAttributesDescription = mage::helper('ProductReturn/Configurable')->getDescription($productId);
        if ($configurableAttributesDescription != '')
            $name .= '<i>' . $configurableAttributesDescription . '</i>';

        $price = $row->getPrice();
        if (!Mage::getStoreConfig('tax/calculation/price_includes_tax'))
        {
            $currentRma = Mage::registry('current_rma');
            $price = Mage::helper('ProductReturn/Tax')->exclToIncl($row->getId(), $price, $currentRma->getShippingAddress());
        }

        $name .= ' (' . number_format($price, 2) . ')';
        $name = str_replace("'", " ", $name);
        $name = str_replace('"', " ", $name);

        // convert price from base price to order currency price
        $order = Mage::getModel('sales/order')->load(Mage::registry('current_rma')->getrma_order_id());
        $price *= Mage::Helper('ProductReturn/Price_Rate')->getRate($order->getbase_currency_code(), $order->getorder_currency_code());

        $onclick = 'selectProductForExchange(' . $row->getId() . ', ' . $rpId . ', \'' . $name . '\', ' . $price . ')';
        $html    = '<a href="#" onclick="' . $onclick . '">' . $this->__('Select') . '</a>';

        return $html;
    }

}

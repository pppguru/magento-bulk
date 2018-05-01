<?php

class MDN_CompetitorPrice_Block_ProductView extends Mage_Core_Block_Template {

    public function display()
    {
        return (Mage::helper('CompetitorPrice')->isEnabled() && Mage::helper('CompetitorPrice')->isConfigured());
    }

    public function getProduct()
    {
        $product = Mage::registry('current_product');
        if (!$product)
            $product = Mage::registry('product');
        return $product;
    }

    public function getBarcode()
    {
        return Mage::helper('CompetitorPrice')->getEan($this->getProduct()->getId());
    }

    public function getChannel()
    {
        return Mage::helper('CompetitorPrice')->getGoogleShoppingChannel();
    }

}

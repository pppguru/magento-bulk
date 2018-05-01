<?php

class MDN_CompetitorPrice_Helper_Data extends Mage_Core_Helper_Abstract {

    const kModeUpcEan = 'ean';
    const kModeChannelReference = 'reference';

    public function log($msg)
    {
        Mage::log($msg, null, 'bms_competitor_price.log');
    }

    public function getAccountDetails()
    {
        $details = array('status' => 'OK', 'credits' => 0);

        $result = Mage::getSingleton('CompetitorPrice/Offers')->getOffers(array());

        if (isset($result['body']['credits']))
            $details['credits'] = $result['body']['credits'];
        if (isset($result['body']['errors'])) {
            $details['status'] = $this->__('ERROR');
            $details['message'] = implode(',', $result['body']['errors']);
        }

        return $details;
    }

    public function isErpInstalled(){

        return Mage::getStoreConfig('advancedstock/erp/is_installed');

    }

    public function isConfigured()
    {

        return (
            (Mage::getStoreConfig('competitorprice/account/user') != '')
            &&
            (Mage::getStoreConfig('competitorprice/account/secret_key') != '')
        );
    }

    public function isEnabled()
    {
        return Mage::getStoreConfig('competitorprice/general/enable');
    }

    public function getGoogleShoppingChannel()
    {
        return Mage::getStoreConfig('competitorprice/general/gs_website');
    }

    public function getConfigurationUrl()
    {
        return Mage::helper('adminhtml')->getUrl('adminhtml/system_config/edit', array('section' => 'competitorprice'));
    }

    public function erpIsInstalled()
    {
        return Mage::getStoreConfig('advancedstock/erp/is_installed');
    }

    public function getEan($productId)
    {
        if ($this->erpIsInstalled())
            return Mage::helper('AdvancedStock/Product_Barcode')->getBarcodeForProduct($productId);
        else
            return $this->getEanFromAttribute($productId);
    }

    public function getEanFromAttribute($productId)
    {
        $attribute = Mage::getStoreConfig('competitorprice/general/barcode_attribute');
        if ($attribute)
        {
            $product = Mage::getModel('catalog/product')->load($productId);
            return $product->getData($attribute);
        }
    }
}

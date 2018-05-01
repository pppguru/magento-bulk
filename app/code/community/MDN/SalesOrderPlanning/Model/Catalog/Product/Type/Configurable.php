<?php

/**
 * Override class to Fix magento < 1.6 issue in getSku() method
 */
/**
 * Fix extension conflict
 * 15 Dec 2016, Erik
 */
// class MDN_SalesOrderPlanning_Model_Catalog_Product_Type_Configurable extends Mage_Catalog_Model_Product_Type_Configurable
class MDN_SalesOrderPlanning_Model_Catalog_Product_Type_Configurable extends OrganicInternet_SimpleConfigurableProducts_Catalog_Model_Product_Type_Configurable
{

    /**
     * Get sku of product
     *
     * @param  Mage_Catalog_Model_Product $product
     * @return string
     */
    public function getSku($product = null)
    {
        $sku = $this->getProduct($product)->getData('sku');
        $simpleOption = $this->getProduct($product)->getCustomOption('simple_product');
        if($simpleOption) {
            $optionProduct = $simpleOption->getProduct($product);
            $simpleSku = null;
            if ($optionProduct) {
                $simpleSku =  $simpleOption->getProduct($product)->getSku();
            }
            $sku = parent::getOptionSku($product, $simpleSku);
        } else {
            $sku = parent::getSku($product);
        }

        return $sku;
    }

}

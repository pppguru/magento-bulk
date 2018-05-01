<?php

class MDN_AdvancedStock_Helper_Product_Image extends Mage_Core_Helper_Abstract {

    CONST MODE_URL = 'url';
    CONST MODE_PATH = 'path';


    /**
     * $imageDir = Mage::helper('AdvancedStock/Product_Image')->getProductImageDir($productId);
     *
     * @param $product is a magento product model object or a product ID
     * @return the dir of the image
     */
    public function getProductImageDir($product) {
        return $this->getProductImageUrlOrPath($product,self::MODE_PATH);
    }

    /**
     * $imageUrl = Mage::helper('AdvancedStock/Product_Image')->getProductImageUrl($productId);
     *
     * @param $product is a magento product model object or a product ID
     * @return the dir of the image
     */
    public function getProductImageUrl($product) {
        return $this->getProductImageUrlOrPath($product,self::MODE_URL);
    }

    private function getProductImageUrlOrPath($product,$mode) {
        $imageUrl = '';

        if (is_numeric($product))
            $product = Mage::getModel('catalog/product')->load($product);

        //get the small image From simple product
        if (($product->getsmall_image()) && ($product->getsmall_image() != 'no_selection')) {
            $imageUrl = $this->getBaseProductImages($mode) . $product->getsmall_image();
        } else {
            //if there is no image on simple product, try to get one from his parent (configurable ...)
            $configurableProduct = Mage::helper('AdvancedStock/Product_ConfigurableAttributes')->getConfigurableProduct($product->getId());
            if ($configurableProduct)
                if (($configurableProduct->getsmall_image()) && ($configurableProduct->getsmall_image() != 'no_selection')) {
                    if ($configurableProduct->getSmallImage()) {
                        $imageUrl = $this->getBaseProductImages($mode) . $configurableProduct->getSmallImage();
                    }
                }
        }

        return $imageUrl;
    }

    private function getBaseProductImages($mode){
        switch($mode){
            case self::MODE_URL :
                return $this->getBaseUrlForProductImages();
                break;
            case self::MODE_PATH :
                return $this->getBaseDirForProductImages();
                break;
        }
    }

    public function getBaseDirForProductImages(){
        return Mage::getBaseDir() . DS . 'media' . $this->getProductsFolder();
    }

    public function getBaseUrlForProductImages(){
        return Mage::getBaseUrl('media') . $this->getProductsFolder();
    }

    public function getProductsFolder(){
        return DS . 'catalog' . DS . 'product';
    }

}

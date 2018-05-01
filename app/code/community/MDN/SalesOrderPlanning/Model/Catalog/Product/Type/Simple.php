<?php

/**
 * Fix extension conflict
 * 15 Dec 2016, Erik
 */
// class MDN_SalesOrderPlanning_Model_Catalog_Product_Type_Simple extends Mage_Catalog_Model_Product_Type_Simple {
class MDN_SalesOrderPlanning_Model_Catalog_Product_Type_Simple extends OrganicInternet_SimpleConfigurableProducts_Catalog_Model_Product_Type_Simple {

    /**
     * Check is product available for sale
     *
     * @param Mage_Catalog_Model_Product $product
     * @return bool
     */
    public function isSalable($product = null) {
        $salable = $this->getProduct($product)->getStatus() == Mage_Catalog_Model_Product_Status::STATUS_ENABLED;

        //define if product is saleable from ProductAvailabilityStatus
        $ProductAvailabilityStatus = mage::getModel('SalesOrderPlanning/ProductAvailabilityStatus')->load($this->getProduct($product)->getId(), 'pa_product_id');
        if ($ProductAvailabilityStatus && $ProductAvailabilityStatus->getId()) {
            $salable = ($ProductAvailabilityStatus->getpa_is_saleable() == 1);
        }

        if ($salable && $this->getProduct($product)->hasData('is_salable')) {
            $salable = $this->getProduct($product)->getData('is_salable');
        } elseif ($salable && $this->isComposite()) {
            $salable = null;
        }

        return $salable;
    }

}

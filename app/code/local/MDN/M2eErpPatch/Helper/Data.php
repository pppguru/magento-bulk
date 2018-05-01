<?php

class MDN_M2eErpPatch_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function updateAvailableQty($productAvailabilityStatus) {

        $attributeCode = mage::getStoreConfig('m2eerppatch/general/qty_attribute');
        $initMode = mage::getStoreConfig('m2eerppatch/general/init_mode');

        if($this->isAllowedForRefresh($productAvailabilityStatus, $attributeCode, $initMode)) {
            $this->updateProductAvailableQtyInAttribute($productAvailabilityStatus, $attributeCode);
        }
    }

    private function isAllowedForRefresh($productAvailabilityStatus,$attributeCode,$initMode) {

        $productId = $productAvailabilityStatus->getpa_product_id();
        $availableQty = $productAvailabilityStatus->getpa_available_qty();
        $websiteId = $productAvailabilityStatus->getpa_website_id();

        if (is_null($availableQty)) {
            return false;
        }

        if (empty($productId)) {
            return false;
        }

        if(!$attributeCode){
            return false;
        }

        //Checks if the product has not been deleted in Magento
        $product = $this->getProduct($productId);
        if (!$product) {
            return false;
        }

        //compatibility with product availability status per website (0 si Admin website, so it is retro-compatibility)
        if($websiteId > 0) {
            $allowedWebsites = explode(',', Mage::getStoreConfig('m2eerppatch/general/allowed_websites'));
            if (!in_array($websiteId, $allowedWebsites)) {
                return false;
            }
        }

        if($initMode == 1){
            return true;
        }

        //init mode one by one
        $method = 'get'.$attributeCode;
        if($product->$method() !=  $availableQty){
            return true;
        }

        //normal case
        if(mage::helper('M2eErpPatch')->fieldHasChanged($productAvailabilityStatus, 'pa_available_qty')){
            return true;
        }

        return false;
    }



    private function updateProductAvailableQtyInAttribute($productAvailabilityStatus,$attributeCode) {

        $productId = $productAvailabilityStatus->getpa_product_id();
        $availableQty = $productAvailabilityStatus->getpa_available_qty();
        $websiteId = $productAvailabilityStatus->getpa_website_id();

        //default for classic mode (only using 0 - Admin)
        $storeId = 0; // = 0 - for Admin

        if($websiteId > 0) {
            $website = mage::getModel('core/website')->load($websiteId);
            if($website != null && $website->getId()>0) {
                $storeGroup = $website->getDefaultGroup();
                if($storeGroup != null && $storeGroup->getId()>0) {
                    $storeId = $storeGroup->getDefaultStoreId();//if not store view, return 0;
                }
            }
        }

        $this->saveAttribute($productId,$attributeCode,$availableQty,$storeId);
    }

    private function saveAttribute($productId,$attributeCode,$availableQty,$storeId){
        if (Mage::getStoreConfig('advancedstock/general/avoid_magento_auto_reindex')) {
            $model = Mage::getSingleton('catalog/Resource_Product_Action');
        } else {
            $model = Mage::getSingleton('catalog/product_action');
        }

        $model->updateAttributes(array($productId), array($attributeCode => $availableQty), $storeId);

        $this->notifyTiersExtension($productId);
    }

    private function getProduct($productId) {
        $product = Mage::getModel('catalog/product')->load($productId);
        return  ($product->getId() > 0)?$product:null;
    }

    public function fieldHasChanged($object, $fieldName) {
        return ($object->getData($fieldName) != $object->getOrigData($fieldName))?true:false;
    }

    public function notifyTiersExtension($productId){

        //M2E PRO
        if(mage::getStoreConfig('m2eerppatch/general/m2epro_notify')){
            //http://docs.m2epro.com/display/BestPractice/Programmatic+Possibilities+to+work+with+Extension
            if(Mage::helper('core')->isModuleEnabled('Ess_M2ePro')){
                try {
                    $model = Mage::getModel('M2ePro/PublicServices_Product_SqlChange');
                    $model->markQtyWasChanged($productId);
                    $model->applyChanges();
                }catch(Exception $ex){
                    Mage::LogException($ex);
                }
            }
        }
    }
}
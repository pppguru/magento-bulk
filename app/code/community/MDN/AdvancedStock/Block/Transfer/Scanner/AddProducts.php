<?php

class MDN_AdvancedStock_Block_Transfer_Scanner_AddProducts extends Mage_Adminhtml_Block_Widget_Form {

    /**
     * return current transfer
     * @return type 
     */
    public function getTransfer()
    {
        return Mage::registry('current_transfer');
    }
    
    /**
     * return url to get information about one product
     * @return type 
     */
    public function getProductInformationUrl()
    {
        return Mage::helper('adminhtml')->getUrl('adminhtml/AdvancedStock_Transfer/ScannerProductInformation');
    }
    
    /**
     *
     * @return type 
     */
    public function getSubmitUrl()
    {
        return Mage::helper('adminhtml')->getUrl('adminhtml/AdvancedStock_Transfer/AddProductsToTransfer');
    }
    
    public function getBackUrl()
    {
        return Mage::helper('adminhtml')->getUrl('adminhtml/AdvancedStock_Transfer/Edit', array('st_id' => $this->getTransfer()->getId()));
    }
    
}

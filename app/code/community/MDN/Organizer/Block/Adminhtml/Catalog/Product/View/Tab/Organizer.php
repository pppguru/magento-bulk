<?php

class MDN_Organizer_Block_Adminhtml_Catalog_Product_View_Tab_Organizer extends Mage_Adminhtml_Block_Widget implements Mage_Adminhtml_Block_Widget_Tab_Interface {

    protected function _construct() {
        parent::_construct();
        $this->setTemplate('Organizer/Catalog/Product/View/Tab/Organizer.phtml');
    }

    public function getProduct()
    {
        return Mage::registry('product');
    }

    /**
     * ######################## TAB settings #################################
     */
    public function getTabLabel() {
        return Mage::helper('Organizer')->__('Organizer');
    }

    public function getTabTitle() {
        return Mage::helper('Organizer')->__('Organizer');
    }

    public function canShowTab() {
        return true;
    }

    public function isHidden() {
        return false;
    }

}
<?php

class MDN_SmartReport_Block_Adminhtml_Catalog_Product_Edit_Tab_SmartReport
    extends MDN_SmartReport_Block_Report_Type
    implements Mage_Adminhtml_Block_Widget_Tab_Interface {

    protected function _construct() {
        parent::_construct();
        $this->setTemplate('SmartReport/Report/Type.phtml');

        return Mage::register('mpm_product', $this->getProduct());
    }


    public function getGroup()
    {
        return strtolower('product_detail');
    }

    public function getVariables()
    {
        $vars = parent::getVariables();
        $vars['product_id'] = $this->getProduct()->getId();
        return $vars;
    }

    public function getDisableHeader()
    {
        return true;
    }

    public function getProduct()
    {
        return Mage::registry('product');
    }


    public function getTabLabel() {
        return Mage::helper('SmartReport')->getName();
    }

    public function getTabTitle() {
        return Mage::helper('SmartReport')->getName();
    }

    public function canShowTab() {
        return true;
    }

    public function isHidden() {
        return false;
    }

    public function getTabUrl()
    {
        return Mage::helper('adminhtml')->getUrl('adminhtml/SmartReport_Reports/SkuDetailAjax', array('product_id' => $this->getProduct()->getId()));
    }

    public function getClass()
    {
        return 'ajax';
    }

    public function getTabClass()
    {
        return 'ajax';
    }

}
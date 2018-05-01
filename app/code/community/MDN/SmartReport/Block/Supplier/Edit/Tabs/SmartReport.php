<?php

class MDN_SmartReport_Block_Supplier_Edit_Tabs_SmartReport
    extends MDN_SmartReport_Block_Report_Type
    implements Mage_Adminhtml_Block_Widget_Tab_Interface {

    protected function _construct() {
        parent::_construct();
        $this->setTemplate('SmartReport/Report/Type.phtml');
    }


    public function getGroup()
    {
        return strtolower('supplier_detail');
    }

    public function getVariables()
    {
        $vars = parent::getVariables();
        $vars['supplier_id'] = $this->getSupplier()->getId();
        return $vars;
    }

    public function getDisableHeader()
    {
        return true;
    }

    public function getProduct()
    {
        return Mage::registry('supplier');
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
        return Mage::helper('adminhtml')->getUrl('adminhtml/SmartReport_Reports/SupplierDetailAjax', array('supplier_id' => $this->getSupplier()->getId()));
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
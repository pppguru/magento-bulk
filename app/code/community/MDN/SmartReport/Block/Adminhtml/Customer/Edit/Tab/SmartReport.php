<?php

class MDN_SmartReport_Block_Adminhtml_Customer_Edit_Tab_SmartReport
    extends MDN_SmartReport_Block_Report_Type
    implements Mage_Adminhtml_Block_Widget_Tab_Interface {

    protected $_customer = null;

    protected function _construct() {
        parent::_construct();
        $this->setTemplate('SmartReport/Report/Type.phtml');

        return Mage::register('mpm_customer', $this->getCustomer());
    }


    public function getGroup()
    {
        return strtolower('customer_detail');
    }

    public function getVariables()
    {
        $vars = parent::getVariables();
        $vars['customer_id'] = $this->getCustomer()->getId();
        return $vars;
    }

    public function getDisableHeader()
    {
        return true;
    }

    public function getCustomer()
    {
        if ($this->_customer == null)
        {
            $customerId = $this->getRequest()->getParam('id');
            $this->_customer = Mage::getModel('customer/customer')->load($customerId);

        }
        return $this->_customer;
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
        return (!$this->getCustomer()->getId());
    }

    public function getTabUrl()
    {
        return Mage::helper('adminhtml')->getUrl('adminhtml/SmartReport_Reports/CustomerDetailAjax', array('id' => $this->getCustomer()->getId()));
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
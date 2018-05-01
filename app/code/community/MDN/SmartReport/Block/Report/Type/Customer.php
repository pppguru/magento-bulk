<?php


class MDN_SmartReport_Block_Report_Type_Customer extends MDN_SmartReport_Block_Report_Type
{
    protected $_customer = null;

    public function getTitle()
    {
        return Mage::helper('SmartReport')->getName().' - '.$this->__('Customer').' '.$this->getCustomer()->getName();
    }

    public function getGroup()
    {
        return 'customer_detail';
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

    public function getVariables()
    {
        $vars = Mage::helper('SmartReport')->getVariables();
        $vars['customer_id'] = $this->getCustomer()->getId();
        return $vars;
    }

    public function getFormHiddens()
    {
        return array('customer_id' => $this->getCustomer()->getId());
    }

    public function isFormLess()
    {
        return true;
    }

    public function getContainer()
    {
        return 'customer_info_tabs_smart_report_content';
    }

    public function getAjaxUrl()
    {
        $params = array();
        $params['id'] = $this->getCustomer()->getId();
        $params['period'] = '{period}';
        $params['date_from'] = '{date_from}';
        $params['date_to'] = '{date_to}';
        $params['group_by_date'] = '{group_by_date}';
        $params['sm_store'] = '{sm_store}';

        return $this->getUrl('adminhtml/SmartReport_Reports/CustomerDetailAjax', $params);
    }

}
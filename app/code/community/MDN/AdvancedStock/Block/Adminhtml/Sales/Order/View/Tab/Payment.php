<?php

class MDN_AdvancedStock_Block_Adminhtml_Sales_Order_View_Tab_Payment
    extends Mage_Adminhtml_Block_Sales_Order_Abstract
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('sales/order/view/tab/Payment.phtml');
    }
	
    /**
     * Retrieve order model instance
     *
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        return Mage::registry('current_order');
    }

    /**
     * Retrieve source model instance
     *
     * @return Mage_Sales_Model_Order
     */
    public function getSource()
    {
        return $this->getOrder();
    }
    
    /**
     * Return url to submit form to change payment_validated value
     *
     */
    public function getSubmitPaymentUrl()
    {
    	return $this->getUrl('adminhtml/AdvancedStock_Misc/Savepayment');
    }

    /**
     * Return url to submit form to change payment_validated value
     *
     */
    public function getSubmitIsValidUrl()
    {
    	return $this->getUrl('adminhtml/AdvancedStock_Misc/SaveIsValid');
    }

    /**
     * ######################## TAB settings #################################
     */
    public function getTabLabel()
    {
        return Mage::helper('AdvancedStock')->__('Payment');
    }

    public function getTabTitle()
    {
        return Mage::helper('AdvancedStock')->__('Payment');
    }

    public function canShowTab()
    {
        return Mage::getSingleton('admin/session')->isAllowed('admin/sales/erp_tabs/payment');
    }

    public function isHidden()
    {
        return false;
    }
}
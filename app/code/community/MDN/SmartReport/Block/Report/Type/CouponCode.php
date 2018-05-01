<?php


class MDN_SmartReport_Block_Report_Type_CouponCode extends MDN_SmartReport_Block_Report_Type
{
    protected $_campaign = null;

    public function getTitle()
    {
        return Mage::helper('SmartReport')->getName().' - '.$this->__('Coupon code').' '.$this->getCouponCode();
    }

    public function getCouponCode()
    {
        $vars = $this->getVariables();
        return $vars['coupon_code'];
    }

    public function getBackUrl()
    {
        return $this->getUrl('adminhtml/SmartReport_Reports/CouponCode');
    }

}

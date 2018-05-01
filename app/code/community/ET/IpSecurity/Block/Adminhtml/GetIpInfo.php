<?php
/**
 * NOTICE OF LICENSE
 *
 * You may not sell, sub-license, rent or lease
 * any portion of the Software or Documentation to anyone.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade to newer
 * versions in the future.
 *
 * @category   ET
 * @package    ET_IpSecurity
 * @copyright  Copyright (c) 2014 ET Web Solutions (http://etwebsolutions.com)
 * @contacts   support@etwebsolutions.com
 * @license    http://shop.etwebsolutions.com/etws-license-free-v1/   ETWS Free License (EFL1)
 */

/**
 * Class ET_IpSecurity_Block_Adminhtml_GetIpInfo
 */
class ET_IpSecurity_Block_Adminhtml_GetIpInfo extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /**
     * Shows in admin panel which ip address returns each method
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     *
     * @inheritdoc
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        /** @var ET_IpSecurity_Helper_Data $helper */
        $helper = Mage::helper('etipsecurity');
        /** @var ET_IpSecurity_Model_IpVariable $model */
        $model = Mage::getModel('etipsecurity/ipVariable');

        $result = $helper->__('Below is a list of standard variables where the server can '
            . 'store the IP address of the visitor, and what each of these variables contains on your server:<br><br>');

        $getIpMethodArray = $model->getOptionArray();
        foreach ($getIpMethodArray as $key=>$value) {
            $ip = (isset($_SERVER[$value])) ? $_SERVER[$value] : $helper->__('Nothing');
            $result .= ' <b>' . $key . '</b> ' .
                $helper->__('returns') .
                '<b> ' . $ip . '</b><br>';
        }
        return $result;
    }
}
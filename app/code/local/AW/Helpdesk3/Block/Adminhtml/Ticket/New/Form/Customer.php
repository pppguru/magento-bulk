<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This software is designed to work with Magento community edition and
 * its use on an edition other than specified is prohibited. aheadWorks does not
 * provide extension support in case of incorrect edition use.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Helpdesk3
 * @version    3.3.0
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Helpdesk3_Block_Adminhtml_Ticket_New_Form_Customer extends Varien_Data_Form_Element_Abstract
{
    /**
     * @return string
     */
    public function getElementHtml()
    {
        $html = ""
            . '<input id="customer_email" name="customer_email" value="'.$this->_getValue().'" type="text" class="input-text required-entry">'
            . '<div id="awhdu3-findcustomer-autocomplete"></div>'
            . $this->_getScriptHtml()
        ;
        return $html;
    }

    /**
     * @return string
     */
    protected function _getScriptHtml()
    {
        $url = Mage::helper('adminhtml')->getUrl('helpdesk_admin/adminhtml_ticket/ajaxFindCustomer');
        return '<script type="text/javascript">'
            . 'new Ajax.Autocompleter("customer_email", "awhdu3-findcustomer-autocomplete", "' . $url . '", {'
            .     'paramName: "search",'
            .     'minChars: 3,'
            .     'updateElement: function(li){'
            .         '$("customer_name").setValue(li.getAttribute("data-name"));'
            .         '$("customer_email").setValue(li.getAttribute("data-email"));'
            .     '}'
            . '});'
            . '</script>'
        ;
    }

    protected function _getValue() {
        return Mage::app()->getRequest()->getParam('customer_email');
    }

}

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


class AW_Helpdesk3_Block_Adminhtml_Ticket_Edit_Form_CustomerSummary_CustomerNote extends Varien_Data_Form_Element_Abstract
{
    public function getElementHtml()
    {
        $originalContent = htmlspecialchars(Zend_Json::encode($this->getOriginalValue()), ENT_QUOTES);
        $html = "<span class='awhdu3-ticket-edit-inline_editable'>"
            . "<span class='awhdu3-ticket-edit-customersummary-customernote' data-original-content='{$originalContent}'>"
            . nl2br($this->getValue())
            . "</span>"
            . "</span>"
        ;
        $html .= $this->_getInlineHtml();
        $html.= $this->getAfterElementHtml();
        return $html;
    }

    /**
     * @return string
     */
    protected function _getInlineHtml()
    {
        $titles = array(
            'btnCancel' => Mage::helper('aw_hdu3')->__('Cancel'),
            'btnSave'   => Mage::helper('aw_hdu3')->__('Save')
        );
        $url = Mage::helper('adminhtml')->getUrl(
            'helpdesk_admin/adminhtml_ticket/ajaxChangeCustomerInfo',
            array('id' => Mage::registry('current_ticket')->getId())
        );
        $result = "<script type='text/javascript'>";
        $result .= "var awHDU3TicketCustomerInfoInline = new AWHDU3_TICKET_INLINE("
            . "$$('.awhdu3-ticket-edit-inline_editable').first()"
            . "," . "function(){awHDU3TicketView.changeCustomerInfo(" . Zend_Json::encode($url) . ");}"
            . "," . "function(){return eval($$('.awhdu3-ticket-edit-customersummary-customernote').first().getAttribute('data-original-content'));}"
            . "," . Zend_Json::encode($titles)
            . ");"
        ;
        $result .= "</script>";
        return $result;
    }
}
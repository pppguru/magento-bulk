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


class AW_Helpdesk3_Block_Adminhtml_Ticket_Edit_Form_Details_Order extends Varien_Data_Form_Element_Abstract
{
    public function getElementHtml()
    {
        $html = "<span class='awhdu3-ticket-edit-editable'>"
            . "<span class='awhdu3-ticket-edit-details-order'>"
            . $this->getEscapedValue()
            . "</span>"
            . "</span>"
        ;
        $url = $this->_getUrlToOrder();
        $isVisible = Mage::registry('current_ticket')->getOrder()->getId() !== null;
        $html .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"
            . "<a href='{$url}' target='_blank' style='" . ($isVisible?"":"visibility:hidden;") . "'>"
            . Mage::helper('aw_hdu3')->__('View Order')
            . "</a>"
        ;
        $html .= $this->_getPopupHtml();
        return $html;
    }

    /**
     * @return string
     */
    protected function _getUrlToOrder()
    {
        $orderId = Mage::registry('current_ticket')->getOrder()->getId();
        if (null === $orderId) {
            $orderId = 0;
        }
        return Mage::helper('adminhtml')->getUrl('adminhtml/sales_order/view', array('order_id' => $orderId));
    }

    /**
     * @return string
     */
    protected function _getPopupHtml()
    {
        $content = Mage::app()->getLayout()->createBlock('aw_hdu3/adminhtml_ticket_edit_popup_order')
            ->toHtml()
        ;
        $titles = array(
            'btnCancel' => Mage::helper('aw_hdu3')->__('Cancel'),
            'btnDone'   => Mage::helper('aw_hdu3')->__('Done'),
            'header'    => Mage::helper('aw_hdu3')->__('Assign Order')
        );
        $url = Mage::helper('adminhtml')->getUrl(
            'helpdesk_admin/adminhtml_ticket/ajaxChangeOrder',
            array('id' => Mage::registry('current_ticket')->getId())
        );
        $result = "<script type='text/javascript'>";
        $result .= "var awHDU3TicketDetailsOrderPopup = new AWHDU3_TICKET_POPUP("
            . Zend_Json::encode($content)
            . "," . "function(){awHDU3TicketView.changeOrder(" . Zend_Json::encode($url) . ");}"
            . "," . Zend_Json::encode($titles)
            . ");"
        ;
        $result .= "Event.observe("
            . "$$('.awhdu3-ticket-edit-details-order').first().up(), 'click', "
            . "function(e){awHDU3TicketDetailsOrderPopup.show();"
            . "});"
        ;
        $result .= "</script>";
        return $result;
    }

}
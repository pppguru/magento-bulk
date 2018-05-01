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


class AW_Helpdesk3_Block_Adminhtml_Ticket_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        $this->_objectId = 'id';
        $this->_blockGroup = 'aw_hdu3';
        $this->_controller = 'adminhtml_ticket';
        parent::__construct();
        $this->_removeButton('reset');
        $this->_removeButton('save');
    }

    protected function _prepareLayout()
    {
        $this->_addButton(
            'assign',
            array(
                'label'   => $this->__('Assign'),
                'onclick' => "awHDU3TicketAssigneePopup.show();",
            ), 10
        );
        parent::_prepareLayout();
    }

    /**
     * @return string
     */
    public function getHeaderText()
    {
        /** @var AW_Helpdesk3_Model_Ticket $ticket */
        $ticket = Mage::registry('current_ticket');
        return $this->__("Details of '%s' [%s]", $ticket->getSubject(), $ticket->getUid());
    }

    /**
     * @return string
     */
    public function getHeaderCssClass()
    {
        return 'head-' . strtr($this->_controller, '_', '-');
    }

    /**
     * Prepare html output
     *
     * @return string
     */
    protected function _toHtml()
    {
        $html = parent::_toHtml();
        $html .= $this->_getPopupHtml();
        return $html;
    }

    /**
     * @return string
     */
    protected function _getPopupHtml()
    {
        $content = Mage::app()->getLayout()->createBlock('aw_hdu3/adminhtml_ticket_edit_popup_assignee')
            ->toHtml()
        ;
        $titles = array(
            'btnCancel' => Mage::helper('aw_hdu3')->__('Cancel'),
            'btnDone'   => Mage::helper('aw_hdu3')->__('Done'),
            'header'    => Mage::helper('aw_hdu3')->__('Assign Ticket')
        );
        $url = $this->getUrl(
            'helpdesk_admin/adminhtml_ticket/ajaxChangeAssignee',
            array('id' => Mage::registry('current_ticket')->getId())
        );
        $result = "<script type='text/javascript'>";
        $result .= "var awHDU3TicketAssigneePopup = new AWHDU3_TICKET_POPUP("
            . Zend_Json::encode($content)
            . "," . "function(){awHDU3TicketView.changeAssignee(" . Zend_Json::encode($url) . ");}"
            . "," . Zend_Json::encode($titles)
            . ");"
        ;
        $result .= "</script>";
        return $result;
    }
}
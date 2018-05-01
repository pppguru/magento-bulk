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


class AW_Helpdesk3_Block_Adminhtml_Ticket_Grid_Column_Renderer_Assign
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Text
{
    public function render(Varien_Object $row)
    {
        $id = $row->getId();
        $jsCallString
            = "(awHDU3TicketGridAssigneePopupList[{$id}]).show();"
        ;
        return "<a href='#' onclick='return (function(){{$jsCallString}; return false;})();'>"
            . $this->__('Manage')
            . "</a>"
            . $this->_getPopupHtml($row)
        ;
    }

    /**
     * @param Varien_Object $ticket
     *
     * @return string
     */
    protected function _getPopupHtml(Varien_Object $ticket)
    {
        $content = Mage::app()->getLayout()->createBlock('aw_hdu3/adminhtml_ticket_edit_popup_assignee')
            ->setTicket($ticket)
            ->toHtml()
        ;
        $titles = array(
            'btnCancel' => Mage::helper('aw_hdu3')->__('Cancel'),
            'btnDone'   => Mage::helper('aw_hdu3')->__('Done'),
            'header'    => Mage::helper('aw_hdu3')->__('Manage Ticket')
        );
        $url = Mage::helper('adminhtml')->getUrl(
            'helpdesk_admin/adminhtml_ticket/ajaxChangeAssignee',
            array('id' => $ticket->getId())
        );
        $currentAgentId = MAge::getSingleton('admin/session');
        $id = $ticket->getId();
        $result = "<script type='text/javascript'>";
        $result .= "var awHDU3TicketGridAssigneePopupList = awHDU3TicketGridAssigneePopupList || {};";
        $result .= "awHDU3TicketGridAssigneePopupList['{$id}'] = new AWHDU3_TICKET_POPUP("
            . Zend_Json::encode($content)
            . "," . "function(){awHDU3TicketGrid.changeAssignee(" . Zend_Json::encode($url) . ",{$id});}"
            . "," . Zend_Json::encode($titles)
            . ");"
            . "var departmentsConfig = " . Mage::helper('aw_hdu3/ticket')->getDepartmentsConfigJson() . ";"
        ;
        $result .= "</script>";
        return $result;
    }

    /**
     * Render column for export
     *
     * @param Varien_Object $row
     * @return string
     */
    public function renderExport(Varien_Object $row)
    {
        $result = parent::renderExport($row);
        return strip_tags($result);
    }
}
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


class AW_Helpdesk3_Block_Adminhtml_Ticket_Edit_Form_Details_Priority extends Varien_Data_Form_Element_Abstract
{
    protected $_cachedData = null;

    public function getElementHtml()
    {
        /** @var AW_Helpdesk3_Model_Ticket $ticket */
        $ticket = Mage::registry('current_ticket');
        $value = $ticket->getPriority();
        $style = "";
        $bgColor = $this->_getBackgroundColorByPriorityId($value);
        if (null !== $bgColor) {
            $style .= "background-color:{$bgColor};";
        }
        $textColor = $this->_getTextColorByPriorityId($value);
        if (null !== $textColor) {
            $style .= "color:{$textColor};";
        }

        $html = "<span class='awhdu3-ticket-edit-editable'>"
            . "<span class='awhdu3-ticket-edit-details-priority' style='{$style}'>"
            . $this->getEscapedValue()
            . "</span>"
            . "</span>"
            . $this->_getPopupHtml()
        ;
        $html.= $this->getAfterElementHtml();
        return $html;
    }

    /**
     * @return string
     */
    protected function _getPopupHtml()
    {
        $content = Mage::app()->getLayout()->createBlock('aw_hdu3/adminhtml_ticket_edit_popup_priority')
            ->toHtml()
        ;
        $titles = array(
            'btnCancel' => Mage::helper('aw_hdu3')->__('Cancel'),
            'btnDone'   => Mage::helper('aw_hdu3')->__('Done'),
            'header'    => Mage::helper('aw_hdu3')->__('Change Priority')
        );
        $url = Mage::helper('adminhtml')->getUrl(
            'helpdesk_admin/adminhtml_ticket/ajaxChangePriority',
            array('id' => Mage::registry('current_ticket')->getId())
        );
        $result = "<script type='text/javascript'>";
        $result .= "var awHDU3TicketDetailsPriorityPopup = new AWHDU3_TICKET_POPUP("
            . Zend_Json::encode($content)
            . "," . "function(){awHDU3TicketView.changePriority(" . Zend_Json::encode($url) . ");}"
            . "," . Zend_Json::encode($titles)
            . ");"
        ;
        $result .= "Event.observe("
            . "$$('.awhdu3-ticket-edit-details-priority').first().up(), 'click', "
            . "function(e){awHDU3TicketDetailsPriorityPopup.show();"
            . "});"
        ;
        $result .= "</script>";
        return $result;
    }

    /**
     * @param int $priorityId
     *
     * @return null|string
     */
    protected function _getBackgroundColorByPriorityId($priorityId)
    {
        $data = $this->_getPriorityData($priorityId);
        if (null === $data || !array_key_exists('background_color', $data) || empty($data['background_color'])) {
            return null;
        }
        $color = $data['background_color'];
        $color = (strpos($color, '#') === FALSE)?('#' . $color):$color;
        return $color;
    }

    /**
     * @param int $priorityId
     *
     * @return null|string
     */
    protected function _getTextColorByPriorityId($priorityId)
    {
        $data = $this->_getPriorityData($priorityId);
        if (null === $data || !array_key_exists('font_color', $data) || empty($data['font_color'])) {
            return null;
        }
        $color = $data['font_color'];
        $color = (strpos($color, '#') === FALSE)?('#' . $color):$color;
        return $color;
    }

    /**
     * @param int $priorityId
     *
     * @return null|array
     */
    protected function _getPriorityData($priorityId)
    {
        if (null === $this->_cachedData) {
            /** @var AW_Helpdesk3_Model_Resource_Ticket_Priority_Collection $collection */
            $collection = Mage::getModel('aw_hdu3/ticket_priority')->getCollection()->addNotDeletedFilter();
            foreach ($collection->getData() as $value) {
                $this->_cachedData[$value['id']] = $value;
            }
        }
        if (!array_key_exists($priorityId, $this->_cachedData)) {
            return null;
        }
        return $this->_cachedData[$priorityId];
    }
}
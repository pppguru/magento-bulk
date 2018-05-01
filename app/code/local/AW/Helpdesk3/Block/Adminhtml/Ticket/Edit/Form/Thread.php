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


class AW_Helpdesk3_Block_Adminhtml_Ticket_Edit_Form_Thread extends Mage_Adminhtml_Block_Template
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('aw_hdu3/ticket/edit/container/thread.phtml');
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->setChild(
            'tab.content.all', $this->getLayout()->createBlock('aw_hdu3/adminhtml_ticket_edit_form_thread_all')
        );
        $this->setChild(
            'tab.content.discussion',
            $this->getLayout()->createBlock('aw_hdu3/adminhtml_ticket_edit_form_thread_discussion')
        );
        $this->setChild(
            'tab.content.notes', $this->getLayout()->createBlock('aw_hdu3/adminhtml_ticket_edit_form_thread_notes')
        );
        $this->setChild(
            'tab.content.history', $this->getLayout()->createBlock('aw_hdu3/adminhtml_ticket_edit_form_thread_history')
        );
        return parent::_prepareLayout();
    }

    /**
     * @return AW_Helpdesk3_Model_Ticket
     */
    public function getTicket()
    {
        return Mage::registry('current_ticket');
    }

    /**
     * @return string
     */
    public function getAddNotePopupContent()
    {
        return $this->getLayout()->createBlock('aw_hdu3/adminhtml_ticket_edit_popup_note')->toHtml();
    }

    /**
     * @return string
     */
    public function getAddNotePopupActionUrl()
    {
        return $this->getUrl('helpdesk_admin/adminhtml_ticket/ajaxAddNote', array('id' => $this->getTicket()->getId()));
    }

    /**
     * @return array
     */
    public function getAddNotePopupTitles()
    {
        return array(
            'btnCancel' => $this->__('Cancel'),
            'btnDone'   =>$this->__('Done'),
            'header'    => Mage::helper('aw_hdu3')->__('Add Note')
        );
    }

    /**
     * @return string
     */
    public function getTabContentHtmlUrl()
    {
        return $this->getUrl(
            'helpdesk_admin/adminhtml_ticket/ajaxGetThreadTabsContentHtml', array('id' => $this->getTicket()->getId())
        );
    }

    /**
     * @return int
     */
    public function getNotesCount()
    {
        $notesCollection = $this->getChild('tab.content.notes')->getEventCollection();
        return $notesCollection->getSize();
    }
}
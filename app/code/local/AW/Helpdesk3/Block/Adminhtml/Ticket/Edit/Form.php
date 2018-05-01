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


class AW_Helpdesk3_Block_Adminhtml_Ticket_Edit_Form extends Mage_Adminhtml_Block_Abstract
{
    protected function _construct()
    {
        $this->setTemplate('aw_hdu3/ticket/edit/container.phtml');
        parent::_construct();
    }

    protected function _prepareLayout()
    {
        $this->setChild('reply', $this->getLayout()->createBlock('aw_hdu3/adminhtml_ticket_edit_form_reply'));
        $this->setChild('thread', $this->getLayout()->createBlock('aw_hdu3/adminhtml_ticket_edit_form_thread'));
        $this->setChild('details', $this->getLayout()->createBlock('aw_hdu3/adminhtml_ticket_edit_form_details'));
        $this->setChild(
            'customer_summary', $this->getLayout()->createBlock('aw_hdu3/adminhtml_ticket_edit_form_customerSummary')
        );
        $this->setChild(
            'customer_orders', $this->getLayout()->createBlock('aw_hdu3/adminhtml_ticket_edit_form_customerOrders')
        );
        $this->setChild(
            'customer_tickets', $this->getLayout()->createBlock('aw_hdu3/adminhtml_ticket_edit_form_customerTickets')
        );
        return parent::_prepareLayout();
    }
}
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


class AW_Helpdesk3_Block_Adminhtml_Ticket_Edit_Form_Thread_Discussion
    extends AW_Helpdesk3_Block_Adminhtml_Ticket_Edit_Form_Thread_Abstract
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('aw_hdu3/ticket/edit/container/thread/discussion.phtml');
    }

    /**
     * @return AW_Helpdesk3_Model_Resource_Ticket_History_Collection
     */
    public function getEventCollection()
    {
        /** @var AW_Helpdesk3_Model_Resource_Ticket_History_Collection $collection */
        $collection = Mage::getModel('aw_hdu3/ticket_history')->getCollection();
        $collection->addFilterByTicketId($this->getTicket()->getId());
        $collection->addFilterByType(
            array(
                AW_Helpdesk3_Model_Ticket_History_Event_Message::TYPE
            )
        );
        $collection->setOrder('id', Varien_Data_Collection_Db::SORT_ORDER_DESC);
        return $collection;
    }
}
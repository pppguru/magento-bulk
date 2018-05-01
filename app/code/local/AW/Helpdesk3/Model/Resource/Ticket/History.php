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


class AW_Helpdesk3_Model_Resource_Ticket_History extends Mage_Core_Model_Mysql4_Abstract
{
    protected $_serializableFields = array(
        'event_data' => array(null, array())
    );

    protected function _construct()
    {
        $this->_init('aw_hdu3/ticket_history', 'id');
    }

    /**
     * @param AW_Helpdesk3_Model_Ticket_History $history
     *
     * @return $this
     */
    public function saveAdditionalData($history)
    {
        $write = $this->_getWriteAdapter();
        $write->insert($this->getTable('aw_hdu3/ticket_history_additional'),
            array(
                'ticket_history_id'   => $history->getId(),
                'department_agent_id' => $history->getTicket()->getDepartmentAgentId(),
                'department_id'       => $history->getTicket()->getDepartmentId(),
                'status'              => $history->getTicket()->getStatus(),
                'priority'            => $history->getTicket()->getPriority()
            )
        );
        return $this;
    }

    /**
     * @param AW_Helpdesk3_Model_Ticket_History $history
     *
     * @return Varien_Object
     */
    public function getAdditionalData($history)
    {
        $read = $this->_getReadAdapter();
        $select = $read->select()
            ->from(array('tha' => $this->getTable('aw_hdu3/ticket_history_additional')), '*')
            ->where('tha.ticket_history_id = ?', $history->getId())
        ;
        $additionalDataArray = $read->fetchRow($select->__toString());
        if (!is_array($additionalDataArray)) {
            $additionalDataArray = array();
        }
        return new Varien_Object($additionalDataArray);
    }
}
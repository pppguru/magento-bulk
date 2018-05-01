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


class AW_Helpdesk3_Model_Resource_Ticket_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected function _construct()
    {
        parent::_construct();
        $this->_init('aw_hdu3/ticket');
    }

    /**
     * @param string $customerEmail
     *
     * @return $this
     */
    public function addFilterByCustomerEmail($customerEmail)
    {
        return $this->addFieldToFilter('customer_email', $customerEmail);
    }

    public function addNotArchivedFilter()
    {
        return $this->addFieldToFilter('archived',array('eq' => AW_Helpdesk3_Model_Ticket::NOT_ARCHIVED));
    }
    /**
     * @param Mage_Admin_Model_User $user
     *
     * @return $this
     */
    public function addAdminUserFilter($user)
    {
        $this->getSelect()
            ->joinLeft(
                array(
                    'dep_perm' => $this->getTable('aw_hdu3/department_permission')
                ),
                'main_table.department_id = dep_perm.department_id',
                array()
            )
        ;
        /**
         * @var $agent AW_Helpdesk3_Model_Department_Agent
         */
        $agent = Mage::getModel('aw_hdu3/department_agent')->loadAgentByUserId($user->getId());
        $agentFullDepartmentCollection = $agent->getFullDepartmentCollection();
        $agentFullDepartmentIds = $agentFullDepartmentCollection->getAllIds();
        $mainTableDepartmentSql = '1=0';
        if (!empty($agentFullDepartmentIds)) {
            $mainTableDepartmentSql = 'FIND_IN_SET(main_table.department_id, \'' . join(',', $agentFullDepartmentCollection->getAllIds()) . '\')';
        }
        $agentDepartmentCollection = $agent->getDepartmentCollection();
        $departmentIdConditionList = array();
        foreach ($agentDepartmentCollection->getAllIds() as $departmentId) {
            $departmentIdConditionList[] = 'FIND_IN_SET(' . $departmentId . ', department_ids)';
        }
        $departmentIdSql = '1=0';
        if (count($departmentIdConditionList)) {
            $departmentIdSql = join(' OR ', $departmentIdConditionList);
        }
        $adminRoleIdSql = 'FIND_IN_SET(' . $user->getRole()->getId() . ', admin_role_ids)';
        $whereSql = '(' . $mainTableDepartmentSql . ' OR ' . $departmentIdSql . ' OR ' . $adminRoleIdSql . ')';
        $this->getSelect()->where($whereSql);
        return $this;
    }

    public function addFilterByStatus($status)
    {
        return $this->addFieldToFilter('status', $status);
    }

    public function addFilterByPriority($priority)
    {
        return $this->addFieldToFilter('priority', $priority);
    }

    public function addTicketIdFilter($ticketId)
    {
        return $this->addFieldToFilter('main_table.id', array('eq' => $ticketId));
    }

    /**
     * @return $this
     */
    public function joinMessagesTable()
    {
        if (!$this->getFlag('messages_count_joined')) {
            $this->getSelect()
                ->columns(
                    array(
                        new Zend_Db_Expr($this->getMessagesCountFilterIndex() . " as messages_count"),
                        new Zend_Db_Expr($this->getLastMessageDateFilterIndex() . " as last_message_date")
                    )
                );
            ;
            $this->setFlag('messages_count_joined', true);
        }
        return $this;
    }

    public function getMessagesCountFilterIndex()
    {
        return "(SELECT IFNULL(COUNT(t_tm.id), 0)
            FROM {$this->getTable('aw_hdu3/ticket_message')} as t_tm
            LEFT JOIN {$this->getTable('aw_hdu3/ticket_history')} as t_th ON t_tm.history_id = t_th.id AND t_th.event_type = "
            . AW_Helpdesk3_Model_Ticket_History_Event_Message::TYPE . "
            WHERE t_tm.ticket_id = main_table.id
            GROUP BY t_tm.ticket_id ORDER BY t_th.created_at " . Zend_Db_Select::SQL_DESC . ")"
        ;
    }

    public function getLastMessageDateFilterIndex()
    {
        return "(SELECT IFNULL(MAX(t_th.created_at), '0000-00-00')
            FROM {$this->getTable('aw_hdu3/ticket_message')} as t_tm
            LEFT JOIN {$this->getTable('aw_hdu3/ticket_history')} as t_th ON t_tm.history_id = t_th.id AND t_th.event_type = "
            . AW_Helpdesk3_Model_Ticket_History_Event_Message::TYPE . "
            WHERE t_tm.ticket_id = main_table.id
            GROUP BY t_tm.ticket_id)"
        ;
    }

    public function addFilterBySearch($searchValue)
    {
        $this->getSelect()->where('main_table.id IN(?)',
            new Zend_Db_Expr("(SELECT GROUP_CONCAT(ticket_id) FROM {$this->getTable('aw_hdu3/ticket_message')}"
            . " as t_tm_search WHERE t_tm_search.content LIKE ("
            . $this->getConnection()->quote('%' . $searchValue.'%')."))")
        );
        return $this;
    }

    /**
     * @param int $storeId
     *
     * @return $this
     */
    public function addFilterByStoreId($storeId)
    {
        return $this->addFieldToFilter('store_id', $storeId);
    }

    /**
     * @param int $departmentId
     *
     * @return $this
     */
    public function addFilterByDepartmentId($departmentId)
    {
        return $this->addFieldToFilter('department_id', $departmentId);
    }

    /**
     * @param int $agentId
     *
     * @return $this
     */
    public function addFilterByAgentId($agentId)
    {
        return $this->addFieldToFilter('department_agent_id', $agentId);
    }

    public function addFilterByStatusIds($statusIds)
    {
        return $this->addFieldToFilter('status', array('in' => $statusIds));
    }

    public function addFilterByCustomer($filterValue)
    {
        $this->getSelect()->where("customer_email LIKE ("
                . $this->getConnection()->quote('%' . $filterValue.'%').") OR customer_name LIKE ("
                . $this->getConnection()->quote('%' . $filterValue.'%').")"
        );
        return $this;
    }

    public function addFilterByOrder($orderIncrementId)
    {
        return $this->addFieldToFilter('order_increment_id', $orderIncrementId);
    }

    public function setOrderByStatus($direction = self::SORT_ORDER_ASC)
    {
        $this->setOrder('status', $direction);
    }

    public function setOrderByUpdatedAt($direction = self::SORT_ORDER_DESC)
    {
        $this->setOrder('updated_at', $direction);
    }
}

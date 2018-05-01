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


class AW_Helpdesk3_Helper_Statistics extends Mage_Core_Helper_Abstract
{
    const PERIOD_DAY_TYPE     = 1;
    const PERIOD_WEEK_TYPE    = 2;
    const PERIOD_MONTH_TYPE   = 3;
    const PERIOD_QUARTER_TYPE = 4;
    const PERIOD_YEAR_TYPE    = 5;

    /**
     * @param int $storeId
     *
     * @return array(agent_id => array(status_id => array(priority_id => count)))
     */
    public function getWorkloadStatisticsByAgents($storeId = 0)
    {
        return $this->_getWorkloadStatistics('aw_hdu3/department_agent', 'department_agent_id', 'agent_id', 'aw_hdu3/department', 'department_id', $storeId);
    }

    /**
     * @param int $storeId
     *
     * @return array(department_id => array(status_id => array(priority_id => count)))
     */
    public function getWorkloadStatisticsByDepartments($storeId = 0)
    {
        return $this->_getWorkloadStatistics('aw_hdu3/department', 'department_id', 'department_id', 'aw_hdu3/department_agent', 'department_agent_id', $storeId);
    }

    /**
     * @param string $mainTable
     * @param string $joinOnField
     * @param string $fieldAlias
     * @param string $secondaryTable
     * @param string $secondaryJoinOnField
     * @param string $storeId
     *
     * @return array($fieldAlias => array(status_id => array(priority_id => count)))
     */
    protected function _getWorkloadStatistics($mainTable, $joinOnField, $fieldAlias, $secondaryTable, $secondaryJoinOnField, $storeId)
    {
        $storeFilter = '';
        if ($storeId) {
            $storeFilter = ' AND store_id = ' . $storeId;
        }
        $read = Mage::getSingleton('core/resource')->getConnection('core_read');
        $select = $read->select()
            ->from(
                array('main_table' => Mage::getSingleton('core/resource')->getTableName($mainTable)),
                array($fieldAlias => 'main_table.id')
            )
        ;
        $statusIds = Mage::getModel('aw_hdu3/ticket_status')->getCollection()->addNotDeletedFilter()->getAllIds();
        foreach ($statusIds as $statusId) {
            $select->joinLeft(
                array('t' . $statusId => new Zend_Db_Expr(
                        '(SELECT COUNT(ticket.id) as ticket_count, ticket.priority, ticket.status, ticket.' . $joinOnField . ' FROM '
                        . Mage::getSingleton('core/resource')->getTableName('aw_hdu3/ticket') . ' AS ticket LEFT JOIN ' . Mage::getSingleton('core/resource')->getTableName($secondaryTable) . ' AS secondary ON secondary.id = ticket.' . $secondaryJoinOnField . ' WHERE ticket.status = \''
                        . $statusId . '\' AND ticket.archived = \''.AW_Helpdesk3_Model_Ticket::NOT_ARCHIVED.'\' AND secondary.status = \'' . AW_Helpdesk3_Model_Source_Status::ENABLED_VALUE . '\' ' . $storeFilter . ' GROUP BY ticket.priority, ticket.status, ticket.' . $joinOnField . ') '
                    )
                ),
                't' . $statusId . '.' . $joinOnField . ' = main_table.id',
                't' . $statusId . '.priority as priority_' . $statusId .', IF (t' . $statusId . '.ticket_count IS NULL, 0, t' . $statusId . '.ticket_count) AS ' . $statusId
            );
        }
        $selectSql = $select->__toString();
        $result = $read->fetchAll($selectSql);
        $priorityArray = array_fill_keys(Mage::getModel('aw_hdu3/ticket_priority')->getCollection()->addNotDeletedFilter()->getAllIds(), 0);
        $statusArray = array_fill_keys($statusIds, $priorityArray);
        $statusStatistics = array();
        foreach ($result as $row) {
            $_mainIndex = $row[$fieldAlias];
            if (!array_key_exists($_mainIndex, $statusStatistics)) {
                $statusStatistics[$_mainIndex] = $statusArray;
            }
            unset($row[$fieldAlias]);
            foreach ($row as $key => $value) {
                if (array_key_exists($key, $statusStatistics[$_mainIndex])
                    && array_key_exists('priority_' . $key, $row)
                ) {
                    $statusStatistics[$_mainIndex][$key][$row['priority_' . $key]] = (int)$value;
                }
            }
        }
        return $statusStatistics;
    }

    /**
     * @param string $fromDate ( '2014-05-03' )
     * @param string $toDate ( '2014-05-03' )
     * @param array $agentIds
     * @param int    $storeId
     *
     * @return array ( array(agentId1 => array('2014-01-01' => 2, '2014-01-02' => 0)) )
     */
    public function getFirstReplyByAgentsAvgTime($fromDate, $toDate, $agentIds, $storeId = 0)
    {
        $read = Mage::getSingleton('core/resource')->getConnection('core_read');
        $agentFilter = '';
        if (count($agentIds) != 0) {
            $agentFilter = ' AND tha.department_agent_id IN (' . implode(',', $agentIds) . ')';
        }

        //get first reply ticket history ids
        $select = $read->select()
            ->from(
                array('th' => Mage::getSingleton('core/resource')->getTableName('aw_hdu3/ticket_history')),
                array('id' => 'th.id')
            )
            ->joinLeft(
                array('ticket' => Mage::getSingleton('core/resource')->getTableName('aw_hdu3/ticket')),
                'th.ticket_id = ticket.id'
            )
            ->joinLeft(
                array('agent' => Mage::getSingleton('core/resource')->getTableName('aw_hdu3/department_agent')),
                'ticket.department_agent_id = agent.id'
            )
            ->joinLeft(
                array('dept' => Mage::getSingleton('core/resource')->getTableName('aw_hdu3/department')),
                'ticket.department_id = dept.id'
            )
            ->join(
                array('tha' => Mage::getSingleton('core/resource')->getTableName('aw_hdu3/ticket_history_additional')),
                'tha.ticket_history_id = th.id' . $agentFilter
                . ' AND tha.status != ' . AW_Helpdesk3_Model_Source_Ticket_Status::NEW_VALUE,
                array()
            )
            ->where('th.event_type = ?', AW_Helpdesk3_Model_Ticket_History_Event_Message::TYPE)
            ->where('agent.status', AW_Helpdesk3_Model_Source_Status::ENABLED_VALUE)
            ->where('dept.status', AW_Helpdesk3_Model_Source_Status::ENABLED_VALUE)
            ->group('th.ticket_id')
        ;

        $historyIds = $read->fetchCol($select->__toString());
        if (count($historyIds) == 0) {
            $historyIds = array(-1);
        }

        //agent_id = ticket owner id in period ticket NEW -> ticket.status != NEW
        $select = $read->select()
            ->from(
                array('t' => Mage::getSingleton('core/resource')->getTableName('aw_hdu3/ticket')),
                array()
            )
            ->joinLeft(
                array('agent' => Mage::getSingleton('core/resource')->getTableName('aw_hdu3/department_agent')),
                't.department_agent_id = agent.id'
            )
            ->joinLeft(
                array('dept' => Mage::getSingleton('core/resource')->getTableName('aw_hdu3/department')),
                't.department_id = dept.id'
            )
            ->joinLeft(
                array('th' => Mage::getSingleton('core/resource')->getTableName('aw_hdu3/ticket_history')),
                'th.ticket_id = t.id AND th.id IN (' . implode(',', $historyIds) . ')',
                array()
            )
            ->join(
                array('tha' => Mage::getSingleton('core/resource')->getTableName('aw_hdu3/ticket_history_additional')),
                'tha.ticket_history_id = th.id' . $agentFilter,
                array(
                    'agent_id'      => 'tha.department_agent_id',
                    'new_from_date' => 'DATE_FORMAT(t.created_at, "%Y-%m-%d")',
                    'diff_in_hours'  => 'ROUND(TIMESTAMPDIFF(SECOND, t.created_at, th.created_at)/3600, 2)'
                )
            )
            ->where('DATE_FORMAT(t.created_at, "%Y-%m-%d") >= ?', $fromDate)
            ->where('DATE_FORMAT(t.created_at, "%Y-%m-%d") <= ?', $toDate)
            ->where('agent.status', AW_Helpdesk3_Model_Source_Status::ENABLED_VALUE)
            ->where('dept.status', AW_Helpdesk3_Model_Source_Status::ENABLED_VALUE)
        ;
        if ($storeId) {
            $select->where('t.store_id = ?', $storeId);
        }
        $result = $read->fetchAll($select->__toString());

        $firstReplyAgentsAvgTime = array();
        $allPeriodDaysArray = $this->_getPreparedDaysOfPeriod($fromDate, $toDate);

        //init result array
        foreach ($agentIds as $agentId) {
            $firstReplyAgentsAvgTime[$agentId] = $allPeriodDaysArray;
        }

        //prepare array for median calculate
        foreach ($result as $row) {
            $firstReplyAgentsAvgTime[$row['agent_id']][$row['new_from_date']] = array();
            $firstReplyAgentsAvgTime[$row['agent_id']][$row['new_from_date']]['diff_in_hours'][] = $row['diff_in_hours'];
        }

        //calculate median
        $firstReplyAgentsAvgTime = $this->_calculateMedianFromMultiArray($firstReplyAgentsAvgTime, 'diff_in_hours');
        return $firstReplyAgentsAvgTime;
    }

    /**
     * @param $arrayData
     * @param $index
     *
     * @return array
     */
    protected function _calculateMedianFromMultiArray($arrayData, $index)
    {
        foreach ($arrayData as $key => $data) {
            if (!is_array($data)) {
                continue;
            }
            if (!array_key_exists($index, $data)) {
                $arrayData[$key] = $this->_calculateMedianFromMultiArray($arrayData[$key], $index);
            } else {
                if (count($data[$index]) == 0) {
                    $newArray[$key] = 0;
                    continue;
                }
                $arrayData[$key] = $this->calculateMedianFromArray($data[$index]);
            }
        }
        return $arrayData;
    }

    public function calculateMedianFromArray($arrayData)
    {
        if (count($arrayData) == 0) {
            return 0;
        }
        $middleIndex = floor(count($arrayData) / 2);
        sort($arrayData, SORT_NUMERIC);
        $median = $arrayData[$middleIndex];
        if (count($arrayData) % 2 == 0) {
            $median = ($median + $arrayData[$middleIndex - 1]) / 2;
        }
        return $median;
    }

    /**
     * @param string $fromDate ( '2014-05-03' )
     * @param string $toDate ( '2014-05-03' )
     * @param array $agentIds
     * @param int    $storeId
     *
     * @return array ( array(agentId1 => array('2014-01-01' => 2, '2014-01-02' => 0)) )
     */
    public function getCloseTicketByAgentsAvgTime($fromDate, $toDate, $agentIds, $storeId = 0)
    {
        $read = Mage::getSingleton('core/resource')->getConnection('core_read');

        $agentFilter = '';
        if (count($agentIds) != 0) {
            $agentFilter = ' AND tha.department_agent_id IN (' . implode(',', $agentIds) . ')';
        }

        //get closed ticket history ids (period from ticket NEW -> last ticket CLOSED
        $select = $read->select()
            ->from(
                array('th' => Mage::getSingleton('core/resource')->getTableName('aw_hdu3/ticket_history')),
                array('id' => 'max(th.id)')
            )
            ->joinLeft(
                array('ticket' => Mage::getSingleton('core/resource')->getTableName('aw_hdu3/ticket')),
                'th.ticket_id = ticket.id'
            )
            ->joinLeft(
                array('agent' => Mage::getSingleton('core/resource')->getTableName('aw_hdu3/department_agent')),
                'ticket.department_agent_id = agent.id'
            )
            ->joinLeft(
                array('dept' => Mage::getSingleton('core/resource')->getTableName('aw_hdu3/department')),
                'ticket.department_id = dept.id'
            )
            ->join(
                array('tha' => Mage::getSingleton('core/resource')->getTableName('aw_hdu3/ticket_history_additional')),
                'tha.ticket_history_id = th.id' . $agentFilter
                . ' AND tha.status = ' . AW_Helpdesk3_Model_Source_Ticket_Status::CLOSED_VALUE,
                array()
            )
            ->where('agent.status', AW_Helpdesk3_Model_Source_Status::ENABLED_VALUE)
            ->where('dept.status', AW_Helpdesk3_Model_Source_Status::ENABLED_VALUE)
            ->group('th.ticket_id')
        ;

        $historyIds = $read->fetchCol($select->__toString());
        if (count($historyIds) == 0) {
            $historyIds = array(-1);
        }

        //code copy
        $select = $read->select()
            ->from(
                array('t' => Mage::getSingleton('core/resource')->getTableName('aw_hdu3/ticket')),
                array()
            )
            ->joinLeft(
                array('agent' => Mage::getSingleton('core/resource')->getTableName('aw_hdu3/department_agent')),
                't.department_agent_id = agent.id'
            )
            ->joinLeft(
                array('dept' => Mage::getSingleton('core/resource')->getTableName('aw_hdu3/department')),
                't.department_id = dept.id'
            )
            ->joinLeft(
                array('th' => Mage::getSingleton('core/resource')->getTableName('aw_hdu3/ticket_history')),
                'th.ticket_id = t.id AND th.id IN (' . implode(',', $historyIds) . ')',
                array()
            )
            ->join(
                array('tha' => Mage::getSingleton('core/resource')->getTableName('aw_hdu3/ticket_history_additional')),
                'tha.ticket_history_id = th.id' . $agentFilter,
                array(
                    'agent_id'      => 'tha.department_agent_id',
                    'new_from_date' => 'DATE_FORMAT(th.created_at, "%Y-%m-%d")',
                    'diff_in_hours'  => 'ROUND(TIMESTAMPDIFF(SECOND, t.created_at, th.created_at)/3600, 2)'
                )
            )
            ->where('t.status = ?', AW_Helpdesk3_Model_Source_Ticket_Status::CLOSED_VALUE)
            ->where('DATE_FORMAT(th.created_at, "%Y-%m-%d") >= ?', $fromDate)
            ->where('DATE_FORMAT(th.created_at, "%Y-%m-%d") <= ?', $toDate)
            ->where('agent.status', AW_Helpdesk3_Model_Source_Status::ENABLED_VALUE)
            ->where('dept.status', AW_Helpdesk3_Model_Source_Status::ENABLED_VALUE)
        ;
        if ($storeId) {
            $select->where('t.store_id = ?', $storeId);
        }

        $result = $read->fetchAll($select->__toString());
        $closeTicketAgentsAvgTime = array();
        $allPeriodDaysArray = $this->_getPreparedDaysOfPeriod($fromDate, $toDate);

        //init result array
        foreach ($agentIds as $agentId) {
            $closeTicketAgentsAvgTime[$agentId] = $allPeriodDaysArray;
        }

        //prepare array for median calculate
        foreach ($result as $row) {
            $closeTicketAgentsAvgTime[$row['agent_id']][$row['new_from_date']] = array();
            $closeTicketAgentsAvgTime[$row['agent_id']][$row['new_from_date']]['diff_in_hours'][] = $row['diff_in_hours'];
        }

        //calculate median
        $closeTicketAgentsAvgTime = $this->_calculateMedianFromMultiArray($closeTicketAgentsAvgTime, 'diff_in_hours');
        return $closeTicketAgentsAvgTime;
    }

    protected function _getPreparedDaysOfPeriod($fromDate, $toDate)
    {
        $startDate = gmdate("Y-m-d", strtotime($fromDate));
        $endDate = gmdate("Y-m-d", strtotime($toDate));
        $days[$startDate] = 0;
        $currentDate = $startDate;
        while($currentDate < $endDate){
            $currentDate = gmdate("Y-m-d", strtotime("+1 day", strtotime($currentDate)));
            $days[$currentDate] = 0;
        }
        return $days;
    }

    /**
     * @param array $statistics (array('2014-01-01' => 2,'2014-01-02' => 0))
     * @param int $periodType
     *
     * @return array (array('2014-01' => 2))
     */
    public function getGroupedByPeriod($statistics, $periodType)
    {
        if ($periodType == self::PERIOD_DAY_TYPE) {
            return $statistics;
        }
        ksort($statistics);

        //get first key
        $intervalDate = new Zend_Date(key($statistics), Varien_Date::DATE_INTERNAL_FORMAT);

        //reset day to 1
        $intervalDate->setDay(1);
        if ($periodType == self::PERIOD_WEEK_TYPE) {
            //for week period need set start interval like 2014-01-07
            $firstWeekDay = (int)Mage::getStoreConfig('general/locale/firstday') == 0 ?
                7 : (int)Mage::getStoreConfig('general/locale/firstday')
            ;
            $intervalDate->setWeekday($firstWeekDay);
            if ($intervalDate->toString(Varien_Date::DATE_INTERNAL_FORMAT) == key($statistics)) {
                $intervalDate->addWeek(1);
            }
            $intervalDate->subDay(1);
        }
        $result = array();
        foreach ($statistics as $key => $value) {
            if (!$this->_isInPeriodInterval($intervalDate, $key, $periodType)) {
                $this->_setPeriodIntervalIndex($intervalDate, $key, $periodType);
            }
            $intervalIndex  = $intervalDate->toString($this->_getPeriodIntervalDateFormat($periodType));
            if ($periodType == self::PERIOD_WEEK_TYPE) {
                $_prevWeek = clone $intervalDate;
                $_prevWeek->subWeek(1);
                $_prevWeek->addDay(1);
                $_prevWeekIndex = $_prevWeek->toString($this->_getPeriodIntervalDateFormat($periodType));
                $result[$_prevWeekIndex . '/' . $intervalIndex] = (array_key_exists($_prevWeekIndex
                    . '/' . $intervalIndex, $result) ? $result[$_prevWeekIndex . '/' . $intervalIndex] : 0)
                    + $this->_getValue($value)
                ;
            } else {
                $result[$intervalIndex] = (array_key_exists($intervalIndex, $result) ? $result[$intervalIndex] : 0)
                    + $this->_getValue($value)
                ;
            }
        }
        return $result;
    }

    protected function _getValue($data)
    {
        if (!is_array($data)) {
            return $data;
        }
        $result = 0;
        foreach ($data as $value) {
            $result += $this->_getValue($value);
        }
        return $result;
    }

    /**
     * @param Zend_Date $intervalDate
     * @param string $dateValue
     * @param int $periodType
     *
     * @return bool
     */
    protected function _isInPeriodInterval($intervalDate, $dateValue, $periodType)
    {
        $_date = clone $intervalDate;
        if ($periodType == self::PERIOD_YEAR_TYPE) {
            $_date->setMonth(12);
        }
        if ($periodType != self::PERIOD_WEEK_TYPE && $periodType != self::PERIOD_DAY_TYPE) {
            //need reset day to end of month
            $_date->addMonth(1);
            $_date->setDay(1);
            $_date->subDay(1);
        }
        if ($dateValue <= $_date->toString(Varien_Date::DATE_INTERNAL_FORMAT)) {
            return true;
        }
        return false;
    }

    /**
     * @param Zend_Date $intervalDate
     * @param string $dateValue
     * @param int $periodType
     *
     * @return $this
     */
    protected function _setPeriodIntervalIndex($intervalDate, $dateValue, $periodType)
    {
        if (!$this->_isInPeriodInterval($intervalDate, $dateValue, $periodType)) {
            switch ($periodType) {
                case self::PERIOD_WEEK_TYPE :
                    $intervalDate->addWeek(1);
                    break;
                case self::PERIOD_MONTH_TYPE :
                    $intervalDate->addMonth(1);
                    break;
                case self::PERIOD_QUARTER_TYPE :
                    $intervalDate->addMonth(3);
                    break;
                case self::PERIOD_YEAR_TYPE :
                    $intervalDate->addYear(1);
                    break;
                default : return $this;
            }
            $this->_setPeriodIntervalIndex($intervalDate, $dateValue, $periodType);
        }
        return $this;
    }

    /**
     * @param int $periodType
     *
     * @return string
     */
    protected function _getPeriodIntervalDateFormat($periodType)
    {
        switch ($periodType) {
            case self::PERIOD_WEEK_TYPE :
            case self::PERIOD_DAY_TYPE :
                return Varien_Date::DATE_INTERNAL_FORMAT;
            case self::PERIOD_YEAR_TYPE :
                return 'YYYY';
            default : return 'YYYY-MM';
        }
    }

    /**
     * @param string $fromDate ( '2014-03-03' )
     * @param string $toDate   ( '2014-06-03' )
     * @param array  $statusIds
     * @param array  $departmentIds
     * @param int    $storeId
     *
     * @return array ( array(departmentId => array(datetime => count, etc)) )
     */
    public function getTicketStatusStatisticsByDepartment($fromDate, $toDate, $statusIds, $departmentIds, $storeId = 0)
    {
        if (count($departmentIds) == 0) {
            $departmentIds = array(0);
        }
        if (count($statusIds) == 0) {
            $statusIds = array(0);
        }
        $read = Mage::getSingleton('core/resource')->getConnection('core_read');
        $select = $read->select()
            ->from(
                array('t' => Mage::getSingleton('core/resource')->getTableName('aw_hdu3/ticket')),
                'COUNT(t.id) AS ticket_count'
            )
            ->joinLeft(
                array('agent' => Mage::getSingleton('core/resource')->getTableName('aw_hdu3/department_agent')),
                't.department_agent_id = agent.id'
            )
            ->joinLeft(
                array('th'=> new Zend_Db_Expr(
                        '(SELECT * FROM (SELECT * FROM '
                        . Mage::getSingleton('core/resource')->getTableName('aw_hdu3/ticket_history')
                        . ' order by id desc) as th_sort group by ticket_id)'
                    )
                ),
                't.id = th.ticket_id',
                'DATE_FORMAT(th.created_at, "%Y-%m-%d") AS date'
            )
            ->joinLeft(
                array('tha' => Mage::getSingleton('core/resource')->getTableName('aw_hdu3/ticket_history_additional')),
                'tha.ticket_history_id = th.id',
                array('department_id' => 'tha.department_id')
            )
            ->where('DATE_FORMAT(th.created_at, "%Y-%m-%d") >= ?', $fromDate)
            ->where('DATE_FORMAT(th.created_at, "%Y-%m-%d") <= ?', $toDate)
            ->where('tha.department_id IN(?)', $departmentIds)
            ->where('tha.status IN(?)', $statusIds)
            ->where('agent.status = ?', AW_Helpdesk3_Model_Source_Status::ENABLED_VALUE)
            ->where('t.archived = ?', AW_Helpdesk3_Model_Ticket::NOT_ARCHIVED)
            ->group(array('department_id', 'date'))
        ;
        if ($storeId) {
            $select->where('t.store_id = ?', $storeId);
        }
        $result = $read->fetchAll($select->__toString());
        $statisticsByDepartment = array();
        $allPeriodDaysArray = $this->_getPreparedDaysOfPeriod($fromDate, $toDate);

        //init result array
        foreach ($departmentIds as $departmentId) {
            $statisticsByDepartment[$departmentId] = $allPeriodDaysArray;
        }
        foreach ($result as $row) {
            $statisticsByDepartment[$row['department_id']][$row['date']] = (int)$row['ticket_count'];
        }
        return $statisticsByDepartment;
    }
}

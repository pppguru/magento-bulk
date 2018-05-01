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
 * @package    AW_Helpdeskultimate
 * @version    2.10.7
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Helpdeskultimate_Model_Mysql4_Popmessage_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    private $_isStatusFilterSetted = false;
    private $_isRejectedPatternNamesJoined = false;

    const STATUS_PROCESSED = 1;
    const STATUS_UNPROCESSED = 2;
    const STATUS_REJECTED = 3;

    public function _construct()
    {
        parent::_construct();
        $this->_init('helpdeskultimate/popmessage');
    }

    public function joinPatternNames()
    {
        if (!$this->_isRejectedPatternNamesJoined) {
            $this->getSelect()->joinLeft(
                array('patterns' => $this->getTable('helpdeskultimate/rpattern')),
                'main_table.rej_pid = patterns.id',
                array('pattern_name' => 'patterns.name')
            );
            $this->_isRejectedPatternNamesJoined = true;
        }
        return $this;
    }

    public function addUnprocessedFilter()
    {
        return $this->addStatusFilter(self::STATUS_UNPROCESSED);
    }

    /**
     * Excludes messages with specified UIDs from collection
     *
     * @param array $uids
     *
     * @return AW_Helpdeskultimate_Model_Mysql4_Popmessage_Collection
     */
    public function addExcludeUIDFilter(Array $uids)
    {
        if (!sizeof($uids)) {
            return $this;
        }
        $uidsPart = "'" . implode("', '", $uids) . "'";
        $this->getSelect()->where("uid NOT IN $uidsPart");
        return $this;
    }

    public function addHashFilter($hash)
    {
        $this->getSelect()->where('hash=?', $hash);
        return $this;
    }

    /**
     * Adds filter by gateway
     *
     * @param integer $id
     *
     * @return AW_Helpdeskultimate_Model_Mysql4_Popmessage_Collection
     */
    public function addGatewayIdFilter($id)
    {
        $this->getSelect()->where('gateway_id=?', $id);
        $this->getSelect()->orwhere('gateway_id=0');
        return $this;
    }

    /**
     * Adds rejected filter
     *
     * @return AW_Helpdeskultimate_Model_Mysql4_Proto_Collection
     */
    public function addRejectedFilter()
    {
        return $this->addStatusFilter(self::STATUS_REJECTED);
    }

    /**
     * Filters collection by status
     *
     * @param String $status
     *
     * @return AW_Helpdeskultimate_Model_Mysql4_Proto_Collection
     */
    public function addStatusFilter($status)
    {
        if (!$this->_isStatusFilterSetted) {
            $this->getSelect()->where('status = ?', $status);
            $this->_isStatusFilterSetted = true;
        }
        return $this;
    }
}

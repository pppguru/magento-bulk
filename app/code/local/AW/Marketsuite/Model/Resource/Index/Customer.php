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
 * @package    AW_Marketsuite
 * @version    2.1.0
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Marketsuite_Model_Resource_Index_Customer extends Mage_Core_Model_Mysql4_Abstract
{
    /**
     *
     */
    public function _construct()
    {
        $this->_init('marketsuite/index_customer', 'rule_id');
    }

    /**
     * @param $ruleModel
     *
     * @return $this
     * @throws Exception
     */
    public function clearIndexByRule($ruleModel)
    {
        $adapter = $this->_getWriteAdapter();
        if (!$adapter) {
            return $this;
        }
        $adapter->beginTransaction();
        try {
            $this->_getWriteAdapter()->delete(
                $this->getMainTable(),
                array('rule_id = ?' => $ruleModel->getId())
            );
        } catch (Exception $e) {
            $adapter->rollback();
            throw $e;
        }
        $adapter->commit();
        return $this;
    }

    /**
     * @param int   $ruleId
     * @param array $customerIdList
     *
     * @return $this
     * @throws Exception
     */
    public function saveCustomerListToIndex($ruleId, array $customerIdList = array())
    {
        $adapter = $this->_getWriteAdapter();
        if (!$adapter) {
            return $this;
        }

        $data = array();
        foreach ($customerIdList as $customerId) {
            $data[] = array($ruleId, $customerId);
        }
        $adapter->beginTransaction();
        try {
            if ($data) {
                $this->_getWriteAdapter()->insertArray($this->getMainTable(), array('rule_id', 'customer_id'), $data);
            }
        } catch (Exception $e) {
            $adapter->rollback();
            throw $e;
        }
        $adapter->commit();
        return $this;
    }

    public function updateCustomer($customerId, $ruleId, $add)
    {
        $adapter = $this->_getWriteAdapter();
        if (!$adapter) {
            return $this;
        }
        $data = array();
        if ($ruleId) {
            $data[] = array($ruleId, $customerId);
        }
        $adapter->beginTransaction();
        try {
            $this->_getWriteAdapter()->delete(
                $this->getMainTable(),
                array('customer_id = ?' => $customerId, 'rule_id = ?' => $ruleId)
            );
            if ($add) {
                $this->_getWriteAdapter()->insertArray($this->getMainTable(), array('rule_id', 'customer_id'), $data);
            }
        } catch (Exception $e) {
            $adapter->rollback();
            throw $e;
        }
        $adapter->commit();
        return $this;
    }
}
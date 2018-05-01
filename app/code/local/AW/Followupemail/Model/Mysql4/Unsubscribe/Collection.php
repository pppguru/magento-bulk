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
 * @package    AW_Followupemail
 * @version    3.5.8
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */

class AW_Followupemail_Model_Mysql4_Unsubscribe_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('followupemail/unsubscribe');
    }

    /**
     * @param Mage_Core_Model_App|integer $store
     *
     * @return AW_Followupemail_Model_Mysql4_Unsubscribe_Collection
     */
    public function addStoreFilter($store)
    {
        if ($store instanceof Mage_Core_Model_Store) {
            $storeId = $store->getId();
        } elseif (is_numeric($store)) {
            $storeId = $store;
        } else {
            return $this;
        }
        $this->addFieldToFilter('store_id', $storeId);
        return $this;
    }

    /**
     * @param string $customerEmail
     *
     * @return AW_Followupemail_Model_Mysql4_Unsubscribe_Collection
     */
    public function addEmailFilter($customerEmail)
    {
        $this->addFieldToFilter('customer_email', $customerEmail);
        return $this;
    }

    /**
     * @param Mage_Customer_Model_Customer|integer $customer
     *
     * @return AW_Followupemail_Model_Mysql4_Unsubscribe_Collection
     */
    public function addCustomerFilter($customer)
    {
        if ($customer instanceof Mage_Customer_Model_Customer) {
            $customerId = $customer->getId();
        } elseif (is_numeric($customer)) {
            $customerId = $customer;
        } else {
            return $this;
        }
        $this->addFieldToFilter('customer_id', $customerId);
        return $this;
    }

    /**
     * @param AW_Followupemail_Model_Rule|integer $rule
     *
     * @return AW_Followupemail_Model_Mysql4_Unsubscribe_Collection
     */
    public function addRuleFilter($rule)
    {
        if ($rule instanceof AW_Followupemail_Model_Rule) {
            $ruleId = $rule->getId();
        } elseif (is_numeric($rule)) {
            $ruleId = $rule;
        } else {
            return $this;
        }
        $this->addFieldToFilter('rule_id', $ruleId);
        return $this;
    }

    /**
     * @param bool $statusFlag
     *
     * @return AW_Followupemail_Model_Mysql4_Unsubscribe_Collection
     */
    public function addIsUnsubscribedFilter($statusFlag)
    {
        if ($statusFlag) {
            $status = 1;
        } else {
            $status = 0;
        }
        $this->addFieldToFilter('is_unsubscribed', $status);
        return $this;
    }
}

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


class AW_Marketsuite_Model_Cron
{
    const LOCK_CACHE_INDEX = 'aw_marketsuite_lock';
    const LOCK_CACHE_TIMEOUT = 1800;

    public function updateAlpha()
    {
        if (!$this->_isLocked()) {
            $this->_setLock();
            $ruleCollection = Mage::getModel('marketsuite/filter')->getActiveRuleCollection();
            foreach ($ruleCollection as $ruleModel) {
                if (!$ruleModel->isReindexRunning() && !is_null($ruleModel->getData('progress_percent'))) {
                    $ruleModel->load($ruleModel->getFilterId());

                    $date = $ruleModel->getUpdatedAt();

                    $productCollection = Mage::getResourceModel('catalog/product_collection');
                    $productCollection->addWebsiteFilter(array_keys(Mage::app()->getWebsites()));
                    $ruleModel->getConditions()->collectValidatedAttributes($productCollection);

                    $updatedCustomerIdList = Mage::helper('marketsuite/customer')->getUpdatedCustomerIdList($date);
                    foreach ($updatedCustomerIdList as $customerId) {
                        $this->_updateCustomer($customerId, $ruleModel);

                        $orderIdList = Mage::helper('marketsuite/customer')->getOrderIdListByCustomer($customerId);
                        foreach ($orderIdList as $orderId) {
                            $this->_updateOrder($orderId, $ruleModel);
                        }
                    }

                    $customerIdList = array();
                    $updatedOrders = Mage::helper('marketsuite/order')->getUpdatedOrderIdList($date);
                    foreach ($updatedOrders as $orderData) {
                        $this->_updateOrder($orderData['order_id'], $ruleModel);
                        $customerIdList[] = $orderData['customer_id'];
                    }
                    foreach (array_unique($customerIdList) as $customerId) {
                        $this->_updateCustomer($customerId, $ruleModel);
                    }
                    $ruleModel->setUpdatedAtFlag(true)->save();
                }
            }
            $this->_removeLock();
        }
    }

    public function updateAll()
    {
        if (!$this->_isLocked()) {
            $this->_setLock();
            $progressHelper = Mage::helper('marketsuite/progress');
            $pageCount = $progressHelper->getPageCount();
            $ruleCollection = Mage::getModel('marketsuite/filter')->getActiveRuleCollection();

            foreach ($ruleCollection as $ruleModel) {
                $ruleModel->load($ruleModel->getFilterId());

                if (!$ruleModel->isReindexRunning()) {
                    $ruleModel
                        ->setUpdatedAtFlag(true)
                        // Reset progress
                        ->setProgressPercent(0)
                        ->save()
                    ;

                    $productCollection = Mage::getResourceModel('catalog/product_collection');
                    $productCollection->addWebsiteFilter(array_keys(Mage::app()->getWebsites()));
                    $ruleModel->getConditions()->collectValidatedAttributes($productCollection);

                    Mage::getModel('marketsuite/index_customer')->clearIndexByRule($ruleModel);
                    Mage::getModel('marketsuite/index_order')->clearIndexByRule($ruleModel);

                    for ($currentPage = 1; $currentPage <= $pageCount; $currentPage++) {
                        $rule = Mage::getModel('marketsuite/filter')->load($ruleModel->getId());
                        Mage::getModel('marketsuite/index_customer')->processPage($rule, $currentPage);
                        Mage::getModel('marketsuite/index_order')->processPage($rule, $currentPage);
                        $rule
                            ->setProgressPercent($progressHelper->getCurrentProgress($currentPage, $pageCount))
                            ->save()
                        ;
                    }
                }
            }
            $this->_removeLock();
        }
    }

    protected function _updateCustomer($customerId, $rule)
    {
        $customer = Mage::getModel('customer/customer')->load($customerId);
        $add = false;
        if ($rule->validate($customer)) {
            $add = true;
        }
        Mage::getModel('marketsuite/index_customer')->updateCustomer($customerId, $rule->getId(), $add);
    }

    protected function _updateOrder($orderId, $rule)
    {
        $order = Mage::getModel('sales/order')->load($orderId);
        $add = false;
        if ($rule->validate($order)) {
            $add = true;
        }
        Mage::getModel('marketsuite/index_order')->updateOrder($orderId, $rule->getId(), $add);
    }

    /**
     * Checks if one MSS is already running
     *
     * @return bool
     */
    protected function _isLocked()
    {
        $lastExecutionTime = Mage::app()->loadCache(self::LOCK_CACHE_INDEX);
        if (self::LOCK_CACHE_TIMEOUT > (time() - $lastExecutionTime)) {
            return true;
        }
        return false;
    }

    protected function _setLock()
    {
        Mage::app()->saveCache(time(), self::LOCK_CACHE_INDEX, array(), self::LOCK_CACHE_TIMEOUT);
    }

    protected function _removeLock()
    {
        Mage::app()->removeCache(self::LOCK_CACHE_INDEX);
    }
}
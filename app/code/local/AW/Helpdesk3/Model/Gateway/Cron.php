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


class AW_Helpdesk3_Model_Gateway_Cron
{
    const LOCK_CACHE_ID = 'AW_Helpdesk3_Model_Gateway_Cron::createMailFromNewMessage';
    const LOCK_CACHE_LIFETIME = 1800;

    public function createMailFromNewMessage()
    {
        if ($this->_isLocked()
            || !Mage::helper('aw_hdu3/config')->isEnabled()
        ) {
            return $this;
        }
        $departmentCollection = Mage::getModel('aw_hdu3/department')->getCollection();
        $departmentCollection
            ->addActiveFilter()
            ->sortByOrder()
        ;
        foreach ($departmentCollection as $department) {
            /** @var AW_Helpdesk3_Model_Gateway $gateway */
            $gateway = $department->getGateway();
            if (!$gateway->getIsActive()) {
                continue;
            }
            AW_Lib_Helper_Log::start(Mage::helper('aw_hdu3')->__('Start process gateway "%s".', $gateway->getTitle()));
            try {
                $gateway->load($gateway->getId());
                $gateway->process();
            } catch (Exception $e) {
                AW_Lib_Helper_Log::log($e->getMessage(), AW_Lib_Helper_Log::SEVERITY_ERROR);
            }
            AW_Lib_Helper_Log::stop(Mage::helper('aw_hdu3')->__('Stop process gateway "%s".', $gateway->getTitle()));
        }

        //remove lock
        Mage::app()->removeCache(self::LOCK_CACHE_ID);
        return $this;
    }

    /**
     * set lock
     *
     * @return bool
     */
    protected function _isLocked()
    {
        if (Mage::app()->loadCache(self::LOCK_CACHE_ID)) {
            return true;
        }
        Mage::app()->saveCache(time(), self::LOCK_CACHE_ID, array(), self::LOCK_CACHE_LIFETIME);
        return false;
    }
}

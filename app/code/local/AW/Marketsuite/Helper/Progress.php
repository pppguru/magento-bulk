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

class AW_Marketsuite_Helper_Progress extends Mage_Core_Helper_Abstract
{
    const PAGE_SIZE = 500;

    public function getPageCount()
    {
        $customerCount = Mage::helper('marketsuite/customer')->getCustomerCount();
        $orderCount = Mage::helper('marketsuite/order')->getOrderCount();
        $pageCount = ceil(max($customerCount, $orderCount) / self::PAGE_SIZE);
        return $pageCount;
    }

    public function getCurrentProgress($currentPage, $pageCount)
    {
        return $currentPage * 100 / $pageCount;
    }
}
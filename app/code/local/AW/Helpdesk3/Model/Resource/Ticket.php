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


class AW_Helpdesk3_Model_Resource_Ticket extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('aw_hdu3/ticket', 'id');
    }

    public function updateCustomerEmailByWebsiteId($newEmail, $oldEmail, $websiteId)
    {
        $writeAdapter = $this->_getWriteAdapter();
        $tableName = Mage::getSingleton('core/resource')->getTableName('aw_hdu3/ticket');
        $storeIds = Mage::app()->getWebsite($websiteId)->getStoreIds();
        $bind  = array('customer_email' => new Zend_Db_Expr('"' . $newEmail . '"'));
        $where = array('customer_email = ?' => $oldEmail, '`store_id` IN (?)' => $storeIds);
        $writeAdapter->update($tableName, $bind, $where);
    }
}
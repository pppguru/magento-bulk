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


class AW_Lib_Model_Log_Logger extends Mage_Core_Model_Abstract
{
    const XML_PATH_ENABLE_ARCHIVATION = 'awall/aw_lib/save_archive';
    const XML_PATH_LOGGER_STORE_DAYS = 'awall/aw_lib/logger_store_days';


    protected function _construct()
    {
        $this->_init('aw_lib/logger');
    }

    public function processOldLogs()
    {
        $Date = new Zend_Date();
        Zend_Date::setOptions(array('extend_month' => true));
        $Date->sub(Mage::getStoreConfig(self::XML_PATH_LOGGER_STORE_DAYS), Zend_Date::DAY);
        $collection = $this->getCollection()->addOlderThanFilter($Date);
        if (Mage::getStoreConfig(self::XML_PATH_ENABLE_ARCHIVATION)) {
            $resourceSingleton = Mage::getResourceSingleton('aw_lib/logger');
            $sql = $resourceSingleton->getPartInsertSql('aw_lib_logger', $collection->getSelect());
            $resourceSingleton->createBackupFile($sql);
        }

        foreach ($collection as $entry) {
            $entry->delete();
        }
        return $this;
    }
}
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

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$installer->startSetup();
$installer->run("
    ALTER TABLE  {$this->getTable('marketsuite/filters')} ADD `progress_percent` TINYINT(1) DEFAULT NULL;
    ALTER TABLE  {$this->getTable('marketsuite/filters')} ADD `updated_at` DATETIME NOT NULL;

    UPDATE {$this->getTable('marketsuite/filters')} SET `updated_at` = NOW();

    DROP TABLE IF EXISTS {$installer->getTable('marketsuite/index_customer')};
    CREATE TABLE `{$installer->getTable('marketsuite/index_customer')}` (
        `rule_id` int(10) unsigned NOT NULL default '0',
        `customer_id` int(10) unsigned NOT NULL default '0',
        PRIMARY KEY (`rule_id`,`customer_id`),
        CONSTRAINT `FK_INDEX_CUSTOMER_FILTERS`
            FOREIGN KEY (`rule_id`)
            REFERENCES `{$installer->getTable('marketsuite/filters')}` (`filter_id`)
            ON DELETE CASCADE
            ON UPDATE CASCADE,
        CONSTRAINT `FK_INDEX_CUSTOMER_CUSTOMER_ENTITY`
            FOREIGN KEY (`customer_id`)
            REFERENCES `{$installer->getTable('customer_entity')}` (`entity_id`)
            ON DELETE CASCADE
            ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    DROP TABLE IF EXISTS {$installer->getTable('marketsuite/index_order')};
    CREATE TABLE `{$installer->getTable('marketsuite/index_order')}` (
        `rule_id` int(10) unsigned NOT NULL default '0',
        `order_id` int(10) unsigned NOT NULL default '0',
        PRIMARY KEY (`rule_id`,`order_id`),
        CONSTRAINT `FK_INDEX_ORDER_FILTERS`
            FOREIGN KEY (`rule_id`)
            REFERENCES `{$installer->getTable('marketsuite/filters')}` (`filter_id`)
            ON DELETE CASCADE
            ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");
$installer->endSetup();
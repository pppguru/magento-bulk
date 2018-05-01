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
 * @package    AW_Advancedreports
 * @version    2.5.3
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */

/* @var Mage_Core_Model_Resource_Setup $installer */
$installer = $this;
$installer->startSetup();

$installer->run("
    -- DROP TABLE IF EXISTS `{$this->getTable('aw_arep_options')}`;
    CREATE TABLE IF NOT EXISTS `{$this->getTable('aw_arep_options')}`(
        `option_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `admin_id` mediumint(9) UNSIGNED NOT NULL,
        `report_id` varchar(255) NOT NULL,
        `path` varchar(255) NOT NULL,
        `value` text,
        PRIMARY KEY (`option_id`)
    ) Engine=InnoDB charset=utf8;

    -- DROP TABLE IF EXISTS `{$this->getTable('aw_arep_aggregation')}`;
    CREATE TABLE IF NOT EXISTS `{$this->getTable('aw_arep_aggregation')}`(
        `entity_id` int UNSIGNED NOT NULL AUTO_INCREMENT,
        `table` varchar(64) NOT NULL,
        `from` datetime NOT NULL,
        `to` datetime NOT NULL,
        `timetype` varchar(255) NOT NULL,
        `expired` tinyint(1) NOT NULL DEFAULT '0',
        PRIMARY KEY (`entity_id`)
    ) Engine=InnoDB charset=utf8;

    -- DROP TABLE IF EXISTS `{$this->getTable('aw_arep_sku_relevance')}`;
    CREATE TABLE IF NOT EXISTS `{$this->getTable('aw_arep_sku_relevance')}`(
        `entity_id` int UNSIGNED NOT NULL AUTO_INCREMENT,
        `sku` varchar(64) NOT NULL,
        `relevance` smallint UNSIGNED NOT NULL DEFAULT '0',
        PRIMARY KEY (`entity_id`),
        KEY (`sku`)
    ) Engine=InnoDB charset=utf8;
");
$installer->endSetup();

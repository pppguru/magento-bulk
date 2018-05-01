<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2016 Amasty (https://www.amasty.com)
 * @package Amasty_Methods
 */

$installer = $this;

$installer->startSetup();

$installer->run("
CREATE TABLE IF NOT EXISTS `{$this->getTable('ammethods/visibility')}` (
  `entity_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` enum('payment','shipping') NOT NULL,
  `website_id` int(10) unsigned NOT NULL,
  `method` varchar(196) NOT NULL,
  `group_ids` varchar(255) NOT NULL,
  PRIMARY KEY (`entity_id`),
  KEY `website_id` (`website_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ;
");

$installer->endSetup(); 
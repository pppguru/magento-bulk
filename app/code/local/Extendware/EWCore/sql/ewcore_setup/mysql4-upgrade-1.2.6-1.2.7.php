<?php

$installer = $this;
$installer->startSetup();

$command  = "
DROP TABLE IF EXISTS `ewcore_message`;
CREATE TABLE `ewcore_message` (
  `message_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `reference_id` int(11) NOT NULL,
  `status` enum('enabled','disabled') NOT NULL DEFAULT 'enabled',
  `state` enum('read','unread') NOT NULL default 'unread',
  `severity` enum('critical','major','minor','notice') NOT NULL DEFAULT 'notice',
  `category` varchar(255) NOT NULL,
  `subject` text NOT NULL,
  `summary` text NOT NULL,
  `body` text,
  `url` text,
  `sent_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`message_id`),
  UNIQUE KEY `idx_reference_id` (`reference_id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
";

$command = @preg_replace('/(EXISTS\s+`)([a-z0-9\_]+?)(`)/ie', '"\\1" . $this->getTable("\\2") . "\\3"', $command);
$command = @preg_replace('/(ON\s+`)([a-z0-9\_]+?)(`)/ie', '"\\1" . $this->getTable("\\2") . "\\3"', $command);
$command = @preg_replace('/(REFERENCES\s+`)([a-z0-9\_]+?)(`)/ie', '"\\1" . $this->getTable("\\2") . "\\3"', $command);
$command = @preg_replace('/(TABLE\s+`)([a-z0-9\_]+?)(`)/ie', '"\\1" . $this->getTable("\\2") . "\\3"', $command);

$installer->run($command);

Mage::helper('ewcore/config')->setLastMessagesUpdatedServerTime(strtotime(gmdate('c')));

$installer->endSetup();
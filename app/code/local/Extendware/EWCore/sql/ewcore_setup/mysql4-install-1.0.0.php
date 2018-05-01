<?php
$installer = $this;
$installer->startSetup();

$command  = "
	DROP TABLE IF EXISTS `ewcore_module_summary`;
	CREATE TABLE `ewcore_module_summary` (
	  `module_summary_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
	  `status` enum('enabled','disabled') NOT NULL DEFAULT 'enabled',
	  `name` varchar(255) NOT NULL,
	  `friendly_name` varchar(255) NOT NULL,
	  `namespace` varchar(255) NOT NULL,
	  `version` varchar(255) NOT NULL,
	  `code_pool` varchar(255) NOT NULL,
	  `identifier` varchar(255) NOT NULL,
	  `is_extendware` tinyint(4) NOT NULL DEFAULT '0',
	  `created_at` datetime NOT NULL,
	  PRIMARY KEY (`module_summary_id`),
	  UNIQUE KEY `idx_identifier` (`identifier`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;
";

$command = @preg_replace('/(EXISTS\s+`)([a-z0-9\_]+?)(`)/ie', '"\\1" . $this->getTable("\\2") . "\\3"', $command);
$command = @preg_replace('/(REFERENCES\s+`)([a-z0-9\_]+?)(`)/ie', '"\\1" . $this->getTable("\\2") . "\\3"', $command);
$command = @preg_replace('/(TABLE\s+`)([a-z0-9\_]+?)(`)/ie', '"\\1" . $this->getTable("\\2") . "\\3"', $command);

$installer->run($command);

$installer->endSetup();

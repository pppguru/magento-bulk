<?php

$installer = $this;
$installer->startSetup();

$command  = "
	DROP TABLE IF EXISTS `ewcrawler_job`;
	CREATE TABLE `ewcrawler_job` (
	  `job_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
	  `status` enum('enabled','paused','disabled') NOT NULL DEFAULT 'enabled',
	  `state` enum('queued','paused','running','finished') NOT NULL DEFAULT 'queued',
	  `max_threads` int(10) unsigned NOT NULL,
	  `num_generated_urls` int(10) unsigned DEFAULT NULL,
	  `num_logged_urls` int(10) unsigned DEFAULT NULL,
	  `num_crawled_urls` int(10) unsigned DEFAULT NULL,
	  `log` text NOT NULL,
	  `is_extendware_page_cache` tinyint(3) unsigned NOT NULL,
	  `lock_key` varchar(255) DEFAULT NULL,
	  `using_extendware_page_cache` tinyint(3) unsigned NOT NULL,
	  `last_crawl_activity_at` datetime DEFAULT NULL,
	  `crawl_started_at` datetime DEFAULT NULL,
	  `crawl_finished_at` datetime DEFAULT NULL,
	  `finished_at` datetime DEFAULT NULL,
	  `started_at` datetime DEFAULT NULL,
	  `scheduled_at` datetime NOT NULL,
	  `updated_at` datetime NOT NULL,
	  `created_at` datetime NOT NULL,
	  PRIMARY KEY (`job_id`),
	  UNIQUE KEY `idx_lock_key` (`lock_key`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;
";

$command = @preg_replace('/(EXISTS\s+`)([a-z0-9\_]+?)(`)/ie', '"\\1" . $this->getTable("\\2") . "\\3"', $command);
$command = @preg_replace('/(ON\s+`)([a-z0-9\_]+?)(`)/ie', '"\\1" . $this->getTable("\\2") . "\\3"', $command);
$command = @preg_replace('/(REFERENCES\s+`)([a-z0-9\_]+?)(`)/ie', '"\\1" . $this->getTable("\\2") . "\\3"', $command);
$command = @preg_replace('/(TABLE\s+`)([a-z0-9\_]+?)(`)/ie', '"\\1" . $this->getTable("\\2") . "\\3"', $command);

$installer->run($command);


$installer->endSetup();
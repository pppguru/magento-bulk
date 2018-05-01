<?php

$installer = $this;
$installer->startSetup();

$command  = "
ALTER TABLE `ewcrawler_job` 
	ADD COLUMN `generation_mode` enum('automatic','manual')  COLLATE utf8_general_ci NULL DEFAULT 'automatic' after `state` , 
	CHANGE `max_threads` `max_threads` int(10) unsigned   NOT NULL after `generation_mode` , 
	CHANGE `num_generated_urls` `num_generated_urls` int(10) unsigned   NULL after `max_threads` , 
	CHANGE `num_logged_urls` `num_logged_urls` int(10) unsigned   NULL after `num_generated_urls` , 
	CHANGE `num_crawled_urls` `num_crawled_urls` int(10) unsigned   NULL after `num_logged_urls` , 
	CHANGE `num_custom_urls` `num_custom_urls` int(11)   NULL after `num_crawled_urls` , 
	CHANGE `log` `log` text  COLLATE utf8_general_ci NOT NULL after `num_custom_urls` , 
	CHANGE `is_extendware_page_cache` `is_extendware_page_cache` tinyint(3) unsigned   NOT NULL after `log` , 
	CHANGE `lock_key` `lock_key` varchar(255)  COLLATE utf8_general_ci NULL after `is_extendware_page_cache` , 
	CHANGE `using_extendware_page_cache` `using_extendware_page_cache` tinyint(3) unsigned   NOT NULL after `lock_key` , 
	CHANGE `last_activity_at` `last_activity_at` datetime   NULL after `using_extendware_page_cache` , 
	CHANGE `last_crawl_activity_at` `last_crawl_activity_at` datetime   NULL after `last_activity_at` , 
	CHANGE `crawl_started_at` `crawl_started_at` datetime   NULL after `last_crawl_activity_at` , 
	CHANGE `crawl_finished_at` `crawl_finished_at` datetime   NULL after `crawl_started_at` , 
	CHANGE `finished_at` `finished_at` datetime   NULL after `crawl_finished_at` , 
	CHANGE `started_at` `started_at` datetime   NULL after `finished_at` , 
	CHANGE `scheduled_at` `scheduled_at` datetime   NOT NULL after `started_at` , 
	CHANGE `updated_at` `updated_at` datetime   NOT NULL after `scheduled_at` , 
	CHANGE `created_at` `created_at` datetime   NOT NULL after `updated_at` ;
";

$command = @preg_replace('/(EXISTS\s+`)([a-z0-9\_]+?)(`)/ie', '"\\1" . $this->getTable("\\2") . "\\3"', $command);
$command = @preg_replace('/(ON\s+`)([a-z0-9\_]+?)(`)/ie', '"\\1" . $this->getTable("\\2") . "\\3"', $command);
$command = @preg_replace('/(REFERENCES\s+`)([a-z0-9\_]+?)(`)/ie', '"\\1" . $this->getTable("\\2") . "\\3"', $command);
$command = @preg_replace('/(TABLE\s+`)([a-z0-9\_]+?)(`)/ie', '"\\1" . $this->getTable("\\2") . "\\3"', $command);

$installer->run($command);


$installer->endSetup();
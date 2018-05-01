<?php

$installer = $this;
$installer->startSetup();

$command  = "
ALTER TABLE `ewcrawler_job` 
	ADD COLUMN `last_activity_at` datetime   NULL after `using_extendware_page_cache` , 
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
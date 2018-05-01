<?php

$installer = $this;
$installer->startSetup();

$command  = "
/* Alter table in target */
ALTER TABLE `ewgppercent_group_price` 
	DROP KEY `CC12C83765B562314470A24F2BDD0F36`, 
	ADD UNIQUE KEY `idx_unique`(`entity_id`,`all_groups`,`customer_group_id`,`website_id`);


/* Alter table in target */
ALTER TABLE `ewgppercent_tier_price` 
	DROP KEY `E8AB433B9ACB00343ABB312AD2FAB087`, 
	ADD UNIQUE KEY `idx_unique`(`entity_id`,`all_groups`,`customer_group_id`,`qty`,`website_id`);
";

$command = @preg_replace('/(EXISTS\s+`)([a-z0-9\_]+?)(`)/ie', '"\\1" . $this->getTable("\\2") . "\\3"', $command);
$command = @preg_replace('/(ON\s+`)([a-z0-9\_]+?)(`)/ie', '"\\1" . $this->getTable("\\2") . "\\3"', $command);
$command = @preg_replace('/(REFERENCES\s+`)([a-z0-9\_]+?)(`)/ie', '"\\1" . $this->getTable("\\2") . "\\3"', $command);
$command = @preg_replace('/(TABLE\s+`)([a-z0-9\_]+?)(`)/ie', '"\\1" . $this->getTable("\\2") . "\\3"', $command);

$installer->run($command);

$installer->endSetup();
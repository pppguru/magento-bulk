<?php
Mage::helper('ewcore/cache')->clean();
$installer = $this;
$installer->startSetup();

$command = "
DROP TABLE IF EXISTS `ewalsobought_log`;
CREATE TABLE `ewalsobought_log` (
  `log_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `customer_identifier` varchar(255) NOT NULL,
  `order_item_id` int(11) unsigned NOT NULL,
  `product_id` int(11) unsigned NOT NULL,
  `store_id` smallint(5) unsigned NOT NULL,
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`log_id`),
  UNIQUE KEY `idx_unique` (`customer_identifier`,`order_item_id`),
  KEY `idx_order_item_id` (`order_item_id`),
  KEY `idx_product_id` (`product_id`),
  KEY `idx_store_id` (`store_id`),
  CONSTRAINT `fk_mbejhft8pn7ytit` FOREIGN KEY (`store_id`) REFERENCES `core_store` (`store_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_of98ymutf25otk3` FOREIGN KEY (`product_id`) REFERENCES `catalog_product_entity` (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_thwk8ths1d0vu1a` FOREIGN KEY (`order_item_id`) REFERENCES `sales_flat_order_item` (`item_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
";

$command = @preg_replace('/(EXISTS\s+`)([a-z0-9\_]+?)(`)/ie', '"\\1" . $this->getTable("\\2") . "\\3"', $command);
$command = @preg_replace('/(REFERENCES\s+`)([a-z0-9\_]+?)(`)/ie', '"\\1" . $this->getTable("\\2") . "\\3"', $command);
$command = @preg_replace('/(TABLE\s+`)([a-z0-9\_]+?)(`)/ie', '"\\1" . $this->getTable("\\2") . "\\3"', $command);

if ($command) $installer->run($command);
$installer->endSetup(); 
if (Mage::helper('ewcore/environment')->isDemoServer() === true) {
	Mage::getResourceModel('ewalsobought/log')->rebuild();
}
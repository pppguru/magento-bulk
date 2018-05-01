<?php

$installer = $this;
$installer->startSetup();

$configCollection = Mage::getModel('core/config_data')->getCollection();
$configCollection->addFieldToFilter('path', 'ewpagecache_advanced/segmentable/cookies');
foreach ($configCollection as $item) {
	$item->delete();
}


$configCollection = Mage::getModel('core/config_data')->getCollection();
$configCollection->addFieldToFilter('path', 'ewpagecache_advanced/caching/page_rules');
foreach ($configCollection as $item) {
	$item->delete();
}

$installer->endSetup();

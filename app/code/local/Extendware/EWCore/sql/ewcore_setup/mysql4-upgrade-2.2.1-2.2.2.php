<?php

$installer = $this;
$installer->startSetup();

$paths = array(
	'ewcore_licensing/serial/verify_integrity',
	'ewcore_licensing/license/verify_integrity',
	'ewcore_licensing/encoder_license/verify_integrity',
);

$configCollection = Mage::getModel('core/config_data')->getCollection();
$configCollection->addFieldToFilter('path', $paths);
foreach ($configCollection as $item) {
	$item->delete();
}

$installer->endSetup();
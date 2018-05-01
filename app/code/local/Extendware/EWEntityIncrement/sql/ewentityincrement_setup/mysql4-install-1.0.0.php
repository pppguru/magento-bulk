<?php

$installer = $this;

$installer->startSetup();

// done so any pre-loaded orders do not conflict with any numbering scheme the user sets
if (Mage::helper('ewcore/environment')->isDemoServer() === true) {
	$eavTypeCodes = Mage::helper('ewentityincrement/internal_api')->getEavTypeCodes();
	foreach ($eavTypeCodes as $eavTypeCode) {
		$entityType = Mage::getModel('eav/entity_type')->loadByCode($eavTypeCode);
		if (!$entityType->getId()) continue;
		
		$stores = Mage::getResourceModel('core/store_collection');
		foreach($stores as $store) {
			if (!$store->getId()) continue;
			Mage::helper('ewentityincrement')->deleteLastNumberCache($entityType->getId(), $store->getId());
		}
	}
}

$installer->endSetup();

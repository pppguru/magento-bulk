<?php

$installer = $this;
$installer->startSetup();

$paths = array(
	'ewpagecache_tagging/autoflushing/product_event',
	'ewpagecache_tagging/autoflushing/category_event',
	'ewpagecache_tagging/autoflushing/cms_event',
);

$content = '';
$configCollection = Mage::getModel('core/config_data')->getCollection();
$configCollection->addFieldToFilter('path', $paths);
foreach ($configCollection as $item) {
	$content .= "#####################################################################################\n";
	$content .= '# ' . $item->getScope() . ':' . (int)$item->getScopeId() . ':' . $item->getPath() . "\n";
	$content .= "#####################################################################################\n";
	$content .= $item->getValue() . "\n\n";
	
	$item->delete();
}

$upgradeFile = Mage::helper('ewpagecache/internal_api')->getTmpDir('upgrade') . DS . time() . '-upgrade.txt';
@file_put_contents($upgradeFile, $content);

$installer->endSetup();

try {
	$process = Mage::getModel('ewpagecache/indexer')->getProcess();
	if ($process and $process->getId()) {
		$process->changeStatus(Mage_Index_Model_Process::STATUS_PENDING);
	}
} catch (Exception $e) {
	
}
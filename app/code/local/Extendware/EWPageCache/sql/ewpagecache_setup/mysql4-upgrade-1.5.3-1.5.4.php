<?php

$installer = $this;
$installer->startSetup();

$paths = array(
	'ewpagecache/general/servers',
	'ewpagecache_advanced/cache_key/ignored_parameters',
	'ewpagecache_advanced/cache_key/translated_customer_groups',
	'ewpagecache_advanced/injectors/list',
	'ewpagecache_advanced/segmentable/user_agents',
	'ewpagecache_advanced/segmentable/cookies',
	'ewpagecache_advanced/caching/content_disqualifiers',
	'ewpagecache_misc/runnable/uri_disqualifiers',
	'ewpagecache_misc/runnable/cookie_disqualifiers',
	'ewpagecache_misc/runnable/user_agent_disqualifiers',
	'ewpagecache_developer/general/ip_rules',
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
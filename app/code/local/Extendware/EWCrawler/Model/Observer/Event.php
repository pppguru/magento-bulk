<?php
class Extendware_EWCrawler_Model_Observer_Event
{
	static public function beforeBeginCrawl(Varien_Event_Observer $observer) {
		if (Mage::helper('ewcrawler/config')->isFlushFullPageCacheEnabled()) {
            if (Mage::helper('ewcrawler')->isUtilizingExtendwareFullPageCache()) {
                Mage::getSingleton('ewpagecache/cache_secondary')->cleanCache();
            }
        }

        if (Mage::helper('ewcrawler/config')->isFlushMagentoCacheEnabled()) {
           Mage::app()->cleanCache(); 
        }
	}
	
	static public function addCustomUrls(Varien_Event_Observer $observer) {
		// this is an example of how to add custom urls to the crawler
		// if you want to use this event you will need to delete the 'return;' below.
		// please note, that this file will be overwritten on upgrades, so its better to use
		// a separate file and register the event. otherwise, you can set this file to read only
		// so that it will not be overwritten when updating.
		return;
		$crawler = $observer->getEvent()->getCrawler();
		$store = Mage::app()->getStore(1);
		
		// this is how a custom url is added to the queue
		// notice the url should only include the path. the base url will be determined by the store
		$cookies = array(); // list of cookies in the form of cookie_name => cookie_value
		$paths = array('test.html'); // list of paths that will be added to the store base url
		$crawler->addStoreUrlToQueue($store, $paths, $cookies);
	}
}

<?php
class Extendware_EWCrawler_Helper_Rewrite_CatalogSearch_Data extends Mage_CatalogSearch_Helper_Data
{
	public function getQuery()
    {
        if (!$this->_query) {
            $this->_query = parent::getQuery();
            // done so that the crawler requests are not counted
            if (@preg_match('/EWCrawler/', $_SERVER['HTTP_USER_AGENT'])) {
	            if (Mage::helper('ewcore')->getRequestRoute() == 'catalogsearch/result/index') {
	            	$this->_query->setPopularity($this->_query->getPopularity() - 1);
	            }
            }
        }
        return $this->_query;
    }
}
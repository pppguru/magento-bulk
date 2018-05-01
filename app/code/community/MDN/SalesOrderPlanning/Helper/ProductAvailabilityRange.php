<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright  Copyright (c) 2009 Maison du Logiciel (http://www.maisondulogiciel.com)
 * @author : Olivier ZIMMERMANN
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MDN_SalesOrderPlanning_Helper_ProductAvailabilityRange extends Mage_Core_Helper_Abstract
{
	//todo: change key
	const kPath = 'purchase/product_availability/ranges';
	
	/**
	 * Read config
	 *
	 * @return unknown
	 */
	public function getConfig()
	{
    	//init or retrieve config
    	$config = Mage::getStoreConfig(self::kPath);

    	if ($config == '')
    		$config = array();
    	else 
	    	$config = unserialize($config);
    	
    	return $config;
	}
	
	/**
	 * Save config
	 *
	 * @param unknown_type $config
	 */
	public function saveConfig($config)
	{
		//save in database
		$data = serialize($config);
		Mage::getConfig()->saveConfig(self::kPath, $data);
		
		//update in cache
		Mage::getConfig()->reinit();
	}
	
	/**
	 * Add a new range
	 *
	 */
	public function newRange()
	{
		$config = $this->getConfig();
		
		$range = array();
		$range['from'] = 0;
		$range['to'] = 0;
		$range['label'] = '';
		$config[] = $range;		
		
		$this->saveConfig($config);
	}
	
	/**
	 * Return label matching to delay & store
	 *
	 * @param unknown_type $storeId
	 * @param unknown_type $days
	 */
	public function getLabel($storeCode, $days)
	{
		//default value		
		$retour = mage::helper('SalesOrderPlanning')->__('Shipped under %s days', $days);
		
		//parse config
		$config = $this->getConfig();
		if (is_array($config))
		{
			for($i=0;$i<count($config);$i++)
			{
				if (($days >= $config[$i]['from']) && ($days <= $config[$i]['to']))
				{
					$retour = $config[$i]['label'];
					
					//check for store values
					if (isset($config[$i][$storeCode]))
						if ($config[$i][$storeCode] != '')
							$retour = $config[$i][$storeCode];
					
				}
			}
		}
				
		return $retour;
	}
	
	/**
	 * Return directory where are stored range images
	 */
	public function getImageDirectory()
	{
		return Mage::getStoreConfig('system/filesystem/media').DS.'erp_availability_range'.DS;
	}
	
	public function getRangeImageUrl($i)
	{
            $isSecure = false;
            $baseMediaUrl = Mage::getBaseUrl('media', $isSecure);
            return $baseMediaUrl.'erp_availability_range/'.$i.'.gif';
	}
	
}
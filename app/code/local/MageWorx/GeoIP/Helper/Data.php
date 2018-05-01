<?php
/**
 * MageWorx
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MageWorx EULA that is bundled with
 * this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.mageworx.com/LICENSE-1.0.html
 *
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@mageworx.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the extension
 * to newer versions in the future. If you wish to customize the extension
 * for your needs please refer to http://www.mageworx.com/ for more information
 * or send an email to sales@mageworx.com
 *
 * @category   MageWorx
 * @package    MageWorx_GeoIP
 * @copyright  Copyright (c) 2009 MageWorx (http://www.mageworx.com/)
 * @license    http://www.mageworx.com/LICENSE-1.0.html
 */

/**
 * GeoIP extension
 *
 * @category   MageWorx
 * @package    MageWorx_GeoIP
 * @author     MageWorx Dev Team <dev@mageworx.com>
 */

class MageWorx_GeoIP_Helper_Data extends Mage_Core_Helper_Abstract
{
	const XML_GEOIP_DATABASE_TYPE = 'mageworx_customers/geoip/db_type';
	const XML_GEOIP_DATABASE_PATH = 'mageworx_customers/geoip/db_path';

	public function getConfGeoIpDbType()
	{
		return Mage::getStoreConfig(self::XML_GEOIP_DATABASE_TYPE);
	}

	public function isCityDbType()
	{
		return ($this->getConfGeoIpDbType() == MageWorx_GeoIP_Model_Database::GEOIP_CITY_DATABASE);
	}

	public function getFlagPath($name = null)
	{
		$flagName = strtolower($name).'.png';
		$filePath = Mage::getSingleton('core/design_package')->getSkinBaseUrl().DS.'images'.DS.'flags'.DS.$flagName;
		if (!file_exists($filePath)) {
			return Mage::getDesign()->getSkinUrl('images/flags/'.$flagName);
		} else {
			return $filePath;
		}
	}

	public function getDatabasePath()
	{
	    $path = Mage::getStoreConfig(self::XML_GEOIP_DATABASE_PATH);
	    if ($path{0} != '/' && $path{0} != '\\'){
	        $path = Mage::getBaseDir() . DS . $path;
	    }
	    return $path;
	}

	public function getGeoIpHtml($obj)
	{
		$block = Mage::app()->getLayout()
			->createBlock('core/template')
			->setTemplate('geoip/adminhtml-customer-geoip.phtml')
			->addData(array('item' => $obj))
			->toHtml();

        return $block;
	}
}

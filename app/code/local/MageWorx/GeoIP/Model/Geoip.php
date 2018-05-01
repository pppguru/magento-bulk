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

class MageWorx_GeoIP_Model_Geoip
{
	public function getGeoIP($ip)
	{
		$obj = new Varien_Object();
		$geoipData = array();

		$dbPath = Mage::helper('geoip')->getDatabasePath();
		if (!file_exists($dbPath)){
		    return $obj;
		}

		if (Mage::helper('geoip')->isCityDbType()) {
			include_once 'GeoIP/geoipcity.php';
			include_once 'GeoIP/geoipregionvars.php';
			$geoip = geoip_open($dbPath, GEOIP_STANDARD);
			$record = geoip_record_by_addr($geoip, $ip);

			if ($record) {
				$geoipData['code']        = $record->country_code;
				$geoipData['country']     = $record->country_name;
				$geoipData['region']      = $record->region . (isset($GEOIP_REGION_NAME[$record->country_code][$record->region]) ? ' ' . $GEOIP_REGION_NAME[$record->country_code][$record->region] : '');
				$geoipData['city']        = $record->city;
				$geoipData['postal_code'] = $record->postal_code;
			}
			geoip_close($geoip);
		} else {
			include_once 'GeoIP/geoip.php';
			$geoip = geoip_open($dbPath, GEOIP_STANDARD);

			$geoipData['code']    = geoip_country_code_by_addr($geoip, $ip);
			$geoipData['country'] = geoip_country_name_by_addr($geoip, $ip);
			geoip_close($geoip);
		}
		if (isset($geoipData['code'])) {
			$geoipData['flag'] = Mage::helper('geoip')->getFlagPath($geoipData['code']);
		}
		$obj->setData($geoipData);
		return $obj;
	}
}

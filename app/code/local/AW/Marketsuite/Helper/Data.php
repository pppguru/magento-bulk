<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This software is designed to work with Magento community edition and
 * its use on an edition other than specified is prohibited. aheadWorks does not
 * provide extension support in case of incorrect edition use.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Marketsuite
 * @version    2.1.0
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Marketsuite_Helper_Data extends Mage_Core_Helper_Abstract
{
    const SESSION_BACKURL_KEY = '_aw_back_url';
    const USE_AW_BACKURL_FLAG = '_use_aw_backurl';

    private $_countries;
    private $_options;

    public function checkUselessProductAttributes($code)
    {
        $disabledAttributeCodeList = array(
            'custom_design',
            'custom_design_from',
            'custom_design_to',
            'custom_layout_update',
            'description',
            'meta_description',
            'meta_keyword',
            'meta_title',
            'news_from_date',
            'news_to_date',
            'options_container',
            'page_layout',
            'price_view',
            'short_description',
            'special_to_date',
            'special_from_date',
            'tier_price',
            'url_key',
        );
        if (in_array($code, $disabledAttributeCodeList)) {
            return true;
        }
        return false;
    }

    public function getRegions()
    {
        if (!$this->_options) {
            $countriesArray = Mage::getResourceModel('directory/country_collection')->load()
                ->toOptionArray(false);
            $this->_countries = array();
            foreach ($countriesArray as $a) {
                $this->_countries[$a['value']] = $a['label'];
            }

            $countryRegions = array();
            $regionsCollection = Mage::getResourceModel('directory/region_collection')->load();
            foreach ($regionsCollection as $region) {
                $countryRegions[$region->getCountryId()][$region->getId()] = $region->getDefaultName();
            }
            uksort($countryRegions, array($this, 'sortRegionCountries'));

            $this->_options = array();
            foreach ($countryRegions as $countryId => $regions) {
                $regionOptions = array();
                foreach ($regions as $regionName) {
                    $regionOptions[] = array('label' => $regionName, 'value' => $regionName);
                }
                $this->_options[] = array('label' => $this->_countries[$countryId], 'value' => $regionOptions);
            }
        }
        $options = $this->_options;
        array_unshift($options, array('value' => '', 'label' => ''));

        return $options;
    }

    public function sortRegionCountries($a, $b)
    {
        return strcmp($this->_countries[$a], $this->_countries[$b]);
    }

    public function getStoresArray($withAdminStore = false)
    {
        $multiOptions = array();
        $stores = Mage::getModel('core/store')->getCollection();

        if ($withAdminStore) {
            $multiOptions[] = array('value' => 0, 'label' => Mage::helper('marketsuite')->__('Admin Store'));
        }

        foreach ($stores as $store) {
            if (!trim($store->getName())) {
                continue;
            }
            $multiOptions[] = array(
                'value' => $store->getId(),
                'label' => Mage::helper('marketsuite')->__(
                    '%s store of %s website', $store->getName(), $store->getWebsite()->getName()
                ),
            );
        }

        return $multiOptions;

    }

    public function getStatusesArray()
    {
        $statusOptions = array();
        $statuses = Mage::getSingleton('sales/order_config')->getStatuses();
        foreach ($statuses as $key => $status) {
            $statusOptions[] = array('value' => $key, 'label' => $status);
        }
        return $statusOptions;
    }

    public function getOptionsForYesnoCustomerAttributeAsArray()
    {
        $yesnoOptions = array();
        $options = Mage::helper('aw_customerattributes/options')->getOptionsForYesnoAttribute(false);
        foreach ($options as $key => $option) {
            $yesnoOptions[] = array('value' => $key, 'label' => $option);
        }
        return $yesnoOptions;
    }

    public function getOptionsForCustomerAttributesAsArray(AW_Customerattributes_Model_Attribute $attribute)
    {
        return Mage::helper('aw_customerattributes/options')->getOptionsForAttributeByStoreIdAsArray($attribute, null, false);
    }

    public function getCustomerAttributes()
    {
        if (!$this->isCustomerAttributesEnabled()){
            return array();
        }
        return Mage::getModel('aw_customerattributes/attribute')->getCollection();
    }

    /**
     * Check is Advanced Newsletter enabled
     *
     * @return bool
     */
    public function isAdvancedNewsletterEnabled()
    {
        return $this->isModuleOutputEnabled('AW_Advancednewsletter');
    }

    public function isCustomerAttributesEnabled()
    {
        return $this->isModuleOutputEnabled('AW_Customerattributes');
    }

    protected function _getAdminhtmlSession()
    {
        return Mage::getSingleton('adminhtml/session');
    }

    public function setBackUrl($url)
    {
        $this->_getAdminhtmlSession()->setData(self::SESSION_BACKURL_KEY, $url);
    }

    public function getBackUrl()
    {
        return $this->_getAdminhtmlSession()->getData(self::SESSION_BACKURL_KEY);
    }

    public function sortConditionListByLabel(array $conditionList = array())
    {
        foreach ($conditionList as $key => $row) {
            $label[$key]  = strtolower($row['label']);
        }
        array_multisort($label, SORT_ASC, SORT_STRING, $conditionList);
        return $conditionList;
    }
}
<?php

/**
 * @package Conlabz_Rpsc
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class Conlabz_Rpsc_Helper_Data extends Mage_Core_Helper_Abstract
{    
    const ALLOWED_COUNTRIES_ATTR_CODE   = 'rpsc_countries';
    const ACCESS_CONTROL_ATTR_CODE      = 'rpsc_access_control';

    const ACCESS_CONTROL_ALLOW          = 'allow';
    const ACCESS_CONTROL_DENY           = 'deny';

    const BASED_ON_BILLING              = 'billing';
    const BASED_ON_SHIPPING             = 'shipping';
    const BASED_ON_DEFAULT              = self::BASED_ON_SHIPPING;

    const XML_PATH_ACCESS_CONTROL       = 'checkout/rpsc/access_control';
    const XML_PATH_BASED_ON             = 'checkout/rpsc/based_on';

    /**
     *
     * @return string
     */
    public function getCountryAttributeCode()
    {
        return self::ALLOWED_COUNTRIES_ATTR_CODE;
    }
    
    /**
     * check if product can be sent to current country
     * 
     * @return bool
     */
    public function canSendToCountry(
        Mage_Catalog_Model_Product $product,
        Mage_Sales_Model_Quote $quote
    ) {
        $basedOn   = $this->getBasedOnByProduct($product);
        $country   = $this->getCountry($quote, $basedOn);
        $countries = $product->getData(self::ALLOWED_COUNTRIES_ATTR_CODE);

        if (empty($countries)) {
            return true;
        }
        if ($quote->isVirtual() && $basedOn === self::BASED_ON_SHIPPING) {
            return true;
        }
        if (is_string($countries)) {
            $countries = explode(',', $countries);
        }
        $accessControl = $this->getAccessControl($product);
        switch ($accessControl) {
            case self::ACCESS_CONTROL_DENY:
                return !in_array($country, $countries);
            case self::ACCESS_CONTROL_ALLOW:
            default:
                return in_array($country, $countries);
        }
    }

    /**
     *
     * @param Mage_Sales_Model_Quote $quote
     * @param string $basedOn
     * @return string
     */
    public function getCountry(Mage_Sales_Model_Quote $quote, $basedOn)
    {
        switch($basedOn) {
            case Conlabz_Rpsc_Helper_Data::BASED_ON_BILLING:
                return $quote->getBillingAddress()->getCountry();            
            case Conlabz_Rpsc_Helper_Data::BASED_ON_SHIPPING:
            default:
                return $quote->getShippingAddress()->getCountry();
        }
    }

    /**
     *
     * @param Mage_Catalog_Model_Product $product
     * @return string
     */
    public function getBasedOnByProduct(Mage_Catalog_Model_Product $product)
    {
        $typeId = $product->getTypeId();
        foreach ($this->getBasedOn() as $row) {
            if (isset($row['type_id'], $row['based_on'])) {
                if ($row['type_id'] === $typeId) {
                    return $row['based_on'];
                }
            }
        }
        return self::BASED_ON_DEFAULT;
    }

    /**
     *
     * @param int|null $store
     * @return array
     */
    public function getBasedOn($store = null)
    {
        $basedOn = Mage::getStoreConfig(self::XML_PATH_BASED_ON, $store);
        $basedOn = unserialize($basedOn);
        return $basedOn;
    }

    /**
     *
     * @param Mage_Catalog_Model_Product $product
     * @return string deny|allow
     */
    public function getAccessControl(Mage_Catalog_Model_Product $product = null)
    {
        $useConfig = Conlabz_Rpsc_Model_Entity_Source_Accesscontrol::USE_CONFIG;
        if (null !== $product) {
            $productAccessControl = trim($product->getData(self::ACCESS_CONTROL_ATTR_CODE));
        }
        if ($productAccessControl !== $useConfig && $productAccessControl !== '') {
            return $productAccessControl;
        }
        return Mage::getStoreConfig(self::XML_PATH_ACCESS_CONTROL);
    }
}

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
 * @author     : Olivier ZIMMERMANN
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MDN_ProductReturn_Helper_MagentoVersionCompatibility extends Mage_Core_Helper_Abstract
{
    /**
     * Check if magento version uses SalesOrderGrid table
     *
     */
    public function useSalesOrderGrid()
    {
        switch ($this->getVersion()) {
            case '1.0':
            case '1.1':
            case '1.2':
            case '1.3':
            case '1.8':
                return false;
                break;
            case '1.4':
                switch ($this->getVersionMinor()) {
                    case '1.4.0':
                        return false;
                        break;
                    default:
                        return true;
                        break;
                }

                return true;
                break;
            case '1.9':
                return true;
                break;
            default :
                return false;
                break;
        }
    }

    /**
     * Return cost column name
     *
     */
    public function getSalesOrderItemCostColumnName()
    {
        switch ($this->getVersion()) {
            case '1.0':
            case '1.1':
            case '1.2':
            case '1.3':
                return 'cost';
                break;
            case '1.4':
                return 'base_cost';
                break;
            default :
                return 'base_cost';
                break;
        }
    }

    /**
     *
     * @return string <type> Return option group name for stock settings in system > configuration > inventory
     */
    public function getStockOptionsGroupName()
    {
        switch ($this->getVersion()) {
            case '1.0':
            case '1.1':
            case '1.2':
                return 'options';
                break;
            default:
                return 'item_options';
                break;
        }
    }

    /**
     * return version
     *
     * @return unknown
     */
    private function getVersion()
    {
        $version = mage::getVersion();
        $t       = explode('.', $version);

        return $t[0] . '.' . $t[1];
    }

    /**
     * return version
     *
     * @return unknown
     */
    private function getVersionMinor()
    {
        $version = mage::getVersion();
        $t       = explode('.', $version);

        return $t[0] . '.' . $t[1] . '.' . $t[2];
    }

    public function IsQty($productTypeId)
    {
        switch ($this->getVersion()) {
            case '1.0':
            case '1.1':
                if (($productTypeId == 'simple') || ($productTypeId == 'virtual'))
                    return true;
                break;
            case '1.2':
            case '1.3':
            case '1.4':
            default:
                return mage::helper('cataloginventory')->isQty($productTypeId);
                break;
        }
    }

    /**
     * return parents for one product
     */
    /*public function getProductParentIds($product)
    {
        switch ($this->getVersionMinor())
        {
            case '1.4.2':
            case '1.5.0':
                $parentIds = Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($product->getId());
                return $parentIds;
                break;
            default:
                $parentIds = $product->loadParentProductIds()->getData('parent_product_ids');
                return $parentIds;
                break;
        }
    }*/

    public function getProductParentIds($product)
    {

        $versionMinor = $this->getVersionMinor();
        $parentIds    = array();

        $tmp = explode(".", $versionMinor);

        if ($tmp[0] == 1) {

            if ($tmp[1] > 4 || ($tmp[1] == 4 && $tmp[2] >= 2)) {
                // after 1.4.2
                $parentIds = Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($product->getId());
            } else {
                // before 1.4.2
                $parentIds = $product->loadParentProductIds()->getData('parent_product_ids');
            }

        }

        return $parentIds;

    }

    public function canCheckQtyIncrements()
    {
        switch ($this->getVersion()) {
            case '1.0':
            case '1.1':
            case '1.2':
            case '1.3':
            case '1.8':
            case '1.4':
                switch (mage::getVersion()) {
                    case '1.4.0.0':
                    case '1.4.0.1':
                    case '1.4.1.0':
                        return false;
                        break;
                    default:
                        return true;
                        break;
                }
                break;
            default:
                return true;
                break;
        }
    }

}
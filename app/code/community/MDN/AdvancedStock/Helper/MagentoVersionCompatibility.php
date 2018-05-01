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
class MDN_AdvancedStock_Helper_MagentoVersionCompatibility extends Mage_Core_Helper_Abstract {

    private $_stockOptionsGroupName = null;
    private $_salesOrderItemCostColumnName = null;
    private $_magentoAppliedPatchesListFileBuffer = null;

    /**
     * Check if magento version uses SalesOrderGrid table
     *
     */
    public function useSalesOrderGrid() {
        return true;
    }

    /**
     * Return cost column name
     *
     */
    public function getSalesOrderItemCostColumnName() {
        if ($this->_salesOrderItemCostColumnName == null) {
            $sql = "select version from " . Mage::getConfig()->getTablePrefix() . "core_resource where code = 'sales_setup'";
            $catalogInventoryVersion = mage::getResourceModel('sales/order_item_collection')->getConnection()->fetchOne($sql);
            ;

            if ($catalogInventoryVersion < '0.9.40')
                return 'cost';
            else
                return 'base_cost';
        }

        return $this->_salesOrderItemCostColumnName;
    }

    /**
     *
     * @return <type> Return option group name for stock settings in system > configuration > inventory
     * depending of cataloginventory module version
     */
    public function getStockOptionsGroupName() {
        if ($this->_stockOptionsGroupName == null) {
            //get cataloginventory version
            $sql = "select version from " . Mage::getConfig()->getTablePrefix() . "core_resource where code = 'cataloginventory_setup'";
            $catalogInventoryVersion = mage::getResourceModel('sales/order_item_collection')->getConnection()->fetchOne($sql);
            ;

            if ($catalogInventoryVersion < '0.7.4')
                return 'options';
            else
                return 'item_options';
        }

        return $this->_stockOptionsGroupName;
    }

    /**
     * return version
     *
     * @return unknown
     */
    private function getVersion() {
        $version = mage::getVersion();
        $t = explode('.', $version);
        return $t[0] . '.' . $t[1];
    }

    /**
     * return version
     *
     * @return unknown
     */
    private function getVersionMinor() {
        $version = mage::getVersion();
        $t = explode('.', $version);
        return $t[0] . '.' . $t[1] . '.' . $t[2];
    }

    public function IsQty($productTypeId) {
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
    public function getProductParentIds($product) {
        $productId = null;

        if (is_object($product))
            $productId = $product->getId();
        else
            $productId = $product;

        $parentIds = Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($productId);

        return $parentIds;        
    }

    public function canCheckQtyIncrements() {
        return true;
    }

    /**
     * getAllIds on a collection on mage::getModel('sales/order') crash on version previous that 1.5
     * 
     * @return boolean
     */
    public function useGetAllIdsOnSaleOrderModelCollection() {
        switch ($this->getVersion()) {
            case '1.0':
            case '1.1':
            case '1.2':
            case '1.3':
            case '1.4':
              return false;
              break;
            default:
                return true;
                break;
        }
    }

    public function isMagentoPatchInstalled($pathCode){
        $magentoPatchInstalled = false;
        if(file_exists($this->getAppliedPatchFilePath())){
            $magentoPatchInstalled = (strpos($this->getAppliedPatchFile(),$pathCode) !== false)?true:false;
        }
        return $magentoPatchInstalled;
    }

    public function getAppliedPatchFile(){
        if(!$this->_magentoAppliedPatchesListFileBuffer){
            $this->_magentoAppliedPatchesListFileBuffer = file_get_contents($this->getAppliedPatchFilePath());
        }
        return $this->_magentoAppliedPatchesListFileBuffer;
    }

    public function getAppliedPatchFilePath(){
        return Mage::getBaseDir() . DS . 'app'  . DS . 'etc'. DS .'applied.patches.list';
    }


}
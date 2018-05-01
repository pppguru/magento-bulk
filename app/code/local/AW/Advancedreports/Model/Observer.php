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
 * @package    AW_Advancedreports
 * @version    2.5.3
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Advancedreports_Model_Observer
{
    /**
     * Retrieves helper
     *
     * @return AW_Advancedreports_Helper_Data
     */
    protected function _helper()
    {
        return Mage::helper('advancedreports');
    }

    /**
     * Retrieves cache collection
     *
     * @return AW_Advancedreports_Model_Mysql4_Aggregation_Collection
     */
    protected function _getCacheCollection()
    {
        return Mage::getModel('advancedreports/aggregation')->getCollection();
    }

    public function orderSaveAfter($event)
    {
        $order = $event->getOrder();
        $origData = new Varien_Object($order->getOrigData());
        $this->_getCacheCollection()->expirePeriodFor(
            $origData->getCreatedAt(), $origData->getUpdatedAt(), $order->getUpdatedAt()
        );
    }

    /**
     * Handle product delete
     *
     * @param $event
     *
     * @return AW_Advancedreports_Model_Observer
     */
    public function productDeleteBefore($event)
    {
        /* @var Mage_Catalog_Model_Product $product */
        $product = $event->getProduct();

        $searchSku = $product->getSku();
        $sku = $product->getSku;

        /* @var AW_Advancedreports_Model_Sku $skuRelevance */
        $sku = Mage::getModel('advancedreports/sku');

        $tableName = $sku->getResource()->getMainTable();
        $writeAdapter = $this->_helper()->getWriteAdapter();

        try {
            $writeAdapter->beginTransaction();
            $tableConnection = new Zend_Db_Table(
                array(
                     Zend_Db_Table::ADAPTER => $writeAdapter,
                     Zend_Db_Table::NAME    => $tableName,
                )
            );
            $tableConnection->delete("sku = '{$searchSku}'");
            $writeAdapter->commit();
        } catch (Exception $e) {
            Mage::logException($e);
        }
        return $this;
    }

    /**
     * Handle sku update
     *
     * @param $event
     *
     * @return void
     */
    public function productSaveAfter($event)
    {
        /* @var Mage_Catalog_Model_Product $product */
        $product = $event->getProduct();

        /* @var string $origSku Old sku */
        $sku = $product->getData('sku');
        /* @var string $origSku New sku */
        $origSku = $product->getOrigData('sku');
        if ($origSku && ($sku !== $origSku)) {
            /* @var AW_Advancedreports_Model_Sku $skuRelevance */
            $skuModel = Mage::getModel('advancedreports/sku')->load($origSku, 'sku');
            if ($skuModel->getId()) {
                $skuModel->setSku($sku);
                $skuModel->save();
            }
        }
    }

    public function checkPrototype($observer)
    {
        if ((($block = $observer->getBlock()) instanceof Mage_Page_Block_Html_Head)
            && (Mage::helper('advancedreports')->isNewPrototypeRequired())
        ) {
            $items = $block->getData('items');
            foreach ($items as $k => &$v) {
                if (strcmp($v['name'], 'prototype/prototype.js') === 0) {
                    $v['name'] = 'advancedreports/prototype.js';
                }
            }
            $block->setData('items', $items);
        }
    }
}

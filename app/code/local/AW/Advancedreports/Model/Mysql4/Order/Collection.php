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


class AW_Advancedreports_Model_Mysql4_Order_Collection extends Mage_Sales_Model_Mysql4_Order_Collection
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
     * Before load action
     *
     * @return Varien_Data_Collection_Db
     */
    protected function _beforeLoad()
    {
        parent::_beforeLoad();

        if ($this->_helper()->checkCatalogPermissionsActive()) {
            $wherePart = $this->getSelect()->getPart(Zend_Db_Select::SQL_WHERE);
            $this->getSelect()->reset(Zend_Db_Select::WHERE);
            $weHaveStoreId = false;
            foreach ($wherePart as $where) {
                if (strpos($where, "store_id") !== false) {
                    if (!$weHaveStoreId) {
                        if ($this->_helper()->getNeedMainTableAlias()) {
                            $this->getSelect()->where(
                                str_replace("AND ", "", str_replace("(store_id", "(main_table.store_id", $where))
                            );
                        } else {
                            $this->getSelect()->where(
                                str_replace("AND ", "", str_replace("(store_id", "(e.store_id", $where))
                            );
                        }
                        $weHaveStoreId = true;
                    }
                } else {
                    $this->getSelect()->where(str_replace("AND ", "", $where));
                }
            }
        }
        return $this;
    }

    /**
     * Set up store ids to filter collection
     *
     * @param int|array $storeIds
     *
     * @return AW_Advancedreports_Model_Mysql4_Collection_Abstract
     */
    public function setStoreFilter($storeIds)
    {
        if (is_integer($storeIds)) {
            $storeIds = array($storeIds);
        }
        if ($this->_helper()->checkSalesVersion('1.4.0.0')) {
            $this->getSelect()
                ->where("main_table.store_id in ('" . implode("','", $storeIds) . "')");
        } else {
            $this->getSelect()
                ->where("e.store_id in ('" . implode("','", $storeIds) . "')");
        }
        return $this;
    }
}

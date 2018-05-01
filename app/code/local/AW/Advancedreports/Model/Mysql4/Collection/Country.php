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


class AW_Advancedreports_Model_Mysql4_Collection_Country extends AW_Advancedreports_Model_Mysql4_Collection_Abstract
{
    /**
     * Add address data to Report Collection
     *
     * @return AW_Advancedreports_Model_Mysql4_Collection_Country
     */
    public function addAddress()
    {
        if ($this->_helper()->checkSalesVersion('1.4.0.0')) {
            # Community after 1.4.1.0 and Enterprise
            $salesFlatOrderAddress = $this->_helper()->getSql()->getTable('sales_flat_order_address');
            $this->getSelect()
                ->joinLeft(
                    array('flat_order_addr_ship' => $salesFlatOrderAddress),
                    "flat_order_addr_ship.parent_id = main_table.entity_id "
                    . "AND flat_order_addr_ship.address_type = 'shipping'",
                    array()
                )
                ->joinLeft(
                    array('flat_order_addr_bill' => $salesFlatOrderAddress),
                    "flat_order_addr_bill.parent_id = main_table.entity_id "
                    . "AND flat_order_addr_bill.address_type = 'billing'",
                    array()
                )
                ->columns(
                    array('country_id' => 'IFNULL(flat_order_addr_ship.country_id, flat_order_addr_bill.country_id)')
                )
                ->group('country_id');
        } else {
            # Old Community
            $entityValue = $this->_helper()->getSql()->getTable('sales_order_entity_varchar');
            $entityAtribute = $this->_helper()->getSql()->getTable('eav_attribute');
            $entityType = $this->_helper()->getSql()->getTable('eav_entity_type');
            $orderEntity = $this->_helper()->getSql()->getTable('sales_order_entity');

            $this->getSelect()
                ->joinLeft(array('_eavType' => $entityType), "_eavType.entity_type_code = 'order_address'", array())
                ->joinLeft(
                    array('_addrTypeAttr' => $entityAtribute),
                    "_addrTypeAttr.entity_type_id = _eavType.entity_type_id "
                    . "AND _addrTypeAttr.attribute_code = 'address_type'",
                    array()
                )
                ->joinLeft(
                    array('_addrValueAttr' => $entityAtribute),
                    "_addrValueAttr.entity_type_id = _eavType.entity_type_id "
                    . "AND _addrValueAttr.attribute_code = 'country_id'",
                    array()
                )
                # Shipping values
                ->joinRight(
                    array('_orderEntity_ship' => $orderEntity),
                    "_orderEntity_ship.entity_type_id = _eavType.entity_type_id "
                    . "AND _orderEntity_ship.parent_id = e.entity_id",
                    array()
                )
                ->joinRight(
                    array('_addrTypeVal_ship' => $entityValue),
                    "_addrTypeVal_ship.attribute_id = _addrTypeAttr.attribute_id "
                    . "AND _addrTypeVal_ship.entity_id = _orderEntity_ship.entity_id "
                    . "AND _addrTypeVal_ship.value = 'shipping'",
                    array()
                )
                ->joinRight(
                    array('_addrCountryVal_ship' => $entityValue),
                    "_addrCountryVal_ship.attribute_id = _addrValueAttr.attribute_id "
                    . "AND _addrCountryVal_ship.entity_id = _orderEntity_ship.entity_id",
                    array()
                )
                # Billing values
                ->joinRight(
                    array('_orderEntity_bill' => $orderEntity),
                    "_orderEntity_bill.entity_type_id = _eavType.entity_type_id "
                    . "AND _orderEntity_bill.parent_id = e.entity_id",
                    array()
                )
                ->joinRight(
                    array('_addrTypeVal_bill' => $entityValue),
                    "_addrTypeVal_bill.attribute_id = _addrTypeAttr.attribute_id "
                    . "AND _addrTypeVal_bill.entity_id = _orderEntity_bill.entity_id "
                    . "AND _addrTypeVal_bill.value = 'billing'",
                    array()
                )
                ->joinRight(
                    array('_addrCountryVal_bill' => $entityValue),
                    "_addrCountryVal_bill.attribute_id = _addrValueAttr.attribute_id "
                    . "AND _addrCountryVal_bill.entity_id = _orderEntity_bill.entity_id",
                    array()
                )
                ->columns(array('country_id' => 'IFNULL(_addrCountryVal_ship.value, _addrCountryVal_bill.value)'))
                ->group('country_id');
        }
        return $this;
    }

    /**
     * Add items to select request
     *
     * @return AW_Advancedreports_Model_Mysql4_Collection_Country
     */
    public function addOrderItemsCount()
    {
        $itemTable = $this->_helper()->getSql()->getTable('sales_flat_order_item');
        if ($this->_helper()->checkSalesVersion('1.4.0.0')) {
            $this->getSelect()
                ->join(
                    array('item' => $itemTable),
                    "(item.order_id = main_table.entity_id AND item.parent_item_id IS NULL)",
                    array(
                        'sum_qty'   => 'SUM(item.qty_ordered)',
                        'sum_total' => 'SUM(item.base_row_total)',
                    )
                )
                ->where("main_table.entity_id = item.order_id");
        } else {
            $this->getSelect()
                ->join(
                    array('item' => $itemTable), "(item.order_id = e.entity_id AND item.parent_item_id IS NULL)",
                    array(
                        'sum_qty'   => 'SUM(item.qty_ordered)',
                        'sum_total' => 'SUM(item.base_row_total)',
                    )
                )
                ->where("e.entity_id = item.order_id");
        }
        return $this;
    }
}

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


class AW_Advancedreports_Model_Mysql4_Collection_Hours extends AW_Advancedreports_Model_Mysql4_Collection_Abstract
{
    /**
     * Set up Hour filter
     *
     * @return AW_Advancedreports_Block_Advanced_Grid
     */
    public function setHourFilter()
    {
        $itemTable = $this->_helper()->getSql()->getTable('sales_flat_order_item');
        $filterField = $this->_helper()->confOrderDateFilter();
        $globalTz = $this->_helper()->getTimeZoneOffset(true);

        if ($this->_helper()->checkSalesVersion('1.4.0.0')) {
            $this->getSelect()
                ->join(
                    array('item' => $itemTable),
                    "main_table.entity_id = item.order_id AND item.parent_item_id IS NULL",
                    array(
                        'hour'       => "HOUR(CONVERT_TZ(main_table.{$filterField}, '+00:00', '{$globalTz}'))",
                         'sum_qty'   => 'SUM(item.qty_ordered)',
                         'sum_total' => 'SUM(item.base_row_total)',
                         'name'      => 'name',
                         'sku'       => 'sku',
                    )
                )
                ->group('hour');
        } else {
            $this->getSelect()
                ->join(
                    array('item' => $itemTable),
                    "e.entity_id = item.order_id AND item.parent_item_id IS NULL",
                    array(
                        'hour'      => "HOUR(CONVERT_TZ(e.{$filterField}, '+00:00', '{$globalTz}'))",
                        'sum_qty'   => 'SUM(item.qty_ordered)',
                        'sum_total' => 'SUM(item.base_row_total)',
                        'name'      => 'name',
                        'sku'       => 'sku',
                    )
                )
                ->group('hour');
        }
        return $this;
    }
}

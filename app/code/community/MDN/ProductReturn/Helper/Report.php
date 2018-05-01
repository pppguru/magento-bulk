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
class MDN_ProductReturn_Helper_Report extends Mage_Core_Helper_Abstract
{

    /**
     * Return used reason for period
     *
     * @param <type> $fromDate
     * @param <type> $toDate
     */
    public function getReasons($fromDate, $toDate)
    {
        //build sql query to get reasons
        //todo : use magento resource model instead of sql
        $prefix = Mage::getConfig()->getTablePrefix();
        $sql    = "
                select 
                    distinct rp_reason
                from
                    " . $prefix . "rma_products
                ";

        //return reasons
        $reasons = mage::getResourceModel('sales/order_item_collection')->getConnection()->fetchCol($sql);

        return $reasons;
    }

    /**
     * Return periods
     *
     * @param <type> $from
     * @param <type> $to
     * @param <type> $groupBy
     *
     * @return array
     */
    public function setPeriods($fromPeriod, $toPeriod, $groupBy)
    {
        $periods = array();

        //adjust from depending of group by
        $from = strtotime($fromPeriod);
        switch ($groupBy) {
            case 'month':
                $from = date('Y-m', $from) . '-01';
                break;
            case 'year':
                $from = date('Y', $from) . '-01-01';
                break;
        }
        $from = strtotime($from);


        //create periods
        while ($from < strtotime($toPeriod)) {
            $period     = array('from' => 0, 'to' => 0);
            $periodName = '';
            switch ($groupBy) {
                case 'month':
                    $periodName = date('m-Y', $from);
                    $to         = strtotime(date("Y-m-d", $from) . "+1 month"); //add one month
                    break;
                case 'year':
                    $periodName = date('Y', $from);
                    $to         = strtotime(date("Y-m-d", $from) . "+1 year"); //add one year
                    break;
            }

            $period    = array('name' => $periodName, 'from' => date('Y-m-d', $from), 'to' => date('Y-m-d', $to));
            $periods[] = $period;
            $from      = $to;
        }

        //truncate period table
        mage::getResourceModel('ProductReturn/Period')->TruncateTable();

        //insert periods in DB
        foreach ($periods as $period) {
            $model = Mage::getModel('ProductReturn/Period')
                ->setrrp_name($period['name'])
                ->setrrp_from($period['from'])
                ->setrrp_to($period['to'])
                ->save();
        }

        //return periods
        return $periods;
    }

    /**
     * return product ids
     *
     * @param $productId
     * @param $from
     * @param $to
     * @param $reason
     *
     * @return
     * @internal param $ <type> $from
     * @internal param $ <type> $to
     */
    public function getReasonCount($productId, $from, $to, $reason)
    {

        //build sql query to get reasons
        //todo : use magento resource model instead of sql
        $prefix = Mage::getConfig()->getTablePrefix();
        $sql    = "
                select
                    sum(rp_qty)
                from
                    " . $prefix . "rma_products,
                    " . $prefix . "rma
                where
                    rp_rma_id = rma_id
                    and rma_created_at >= '" . $from . "'
                    and rma_created_at <= '" . $to . "'
                    and rp_action <> ''
                    and rp_reason = '" . addslashes($reason) . "'
                ";
        if ($productId)
            $sql .= " and rp_product_id = " . $productId;


        //return
        $value = mage::getResourceModel('sales/order_item_collection')->getConnection()->fetchOne($sql);

        return $value;
    }

}
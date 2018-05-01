<?php

class MDN_SmartReport_Model_Source_Inventory_OldStock extends MDN_SmartReport_Model_Source_Abstract {

    public function getCacheKey($variables, $limit)
    {
        return 'MDN_SmartReport_Model_Source_Inventory_OldStock';
    }

    public function getData($variables, $limit)
    {
        $datas = array();


        $maxDays = Mage::getStoreConfig('smartreport/misc/days_for_all_stock');

        $items = $this->getProductsInStock();

        $maxDate = $this->getMaxDates();

        foreach($items as $item)
        {
            $key = $key = $item['stock_id'].'_'.$item['product_id'];
            if (isset($maxDate[$key]))
            {
                $days = (int)((time() - strtotime($maxDate[$key])) / (3600 * 24));
                if ($days > $maxDays) {
                    $item['last_movement'] = $maxDate[$key];
                    $item['days'] = $days;
                    $datas[] = $item;
                }
            }
        }

        //sort by days
        usort($datas, array("MDN_SmartReport_Model_Source_Inventory_OldStock", "sortPerDays"));

        if ($limit)
            $datas = array_slice($datas, 0, $limit);

        return $datas;
    }

    protected function getProductsInStock()
    {
        $prefix = Mage::getConfig()->getTablePrefix();
        $sql = 'select
                    tbl_stock.stock_name,
                    sku,
                    truncate(qty, 0) qty,
                    tbl_stock.stock_id,
                    tbl_product.entity_id product_id
                from
                    '.$prefix.'cataloginventory_stock_item tbl_stock_item
                    inner join '.$prefix.'catalog_product_entity tbl_product on (tbl_stock_item.product_id = tbl_product.entity_id)
                    inner join '.$prefix.'cataloginventory_stock tbl_stock on (tbl_stock_item.stock_id = tbl_stock.stock_id)
                where
                    qty > 0
                ';
        $connection = mage::getResourceModel('sales/order_item_collection')->getConnection();
        return $connection->fetchAll($sql);
    }

    protected function getMaxDates()
    {
        $prefix = Mage::getConfig()->getTablePrefix();

        $sql = 'select
                  sm_product_id product_id,
                  sm_target_stock stock_id,
                  max(sm_date) last_date
                from
                  '.$prefix.'stock_movement
                where
                    sm_target_stock = 1
                group by
                    sm_product_id,
                    sm_target_stock
                    ';
        $connection = mage::getResourceModel('sales/order_item_collection')->getConnection();
        $collection =  $connection->fetchAll($sql);


        $datas = array();
        foreach($collection as $item)
        {
            $key = $item['stock_id'].'_'.$item['product_id'];
            $datas[$key] = $item['last_date'];
        }


        return $datas;
    }

    public static function sortPerDays($a, $b) {

        if ($a['days']  > $b['days'])
            return -1;
        else
            return 1;
    }

}

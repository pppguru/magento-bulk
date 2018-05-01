<?php

class MDN_SmartReport_Model_Source_Product_StockHistory extends MDN_SmartReport_Model_Source_Abstract {


    public function getCacheKey($variables, $limit)
    {
            return 'MDN_SmartReport_Model_Source_Product_StockHistory_'.$variables['product_id'].'_'.$variables['date_from'];
    }

    public function getData($variables, $limit)
    {
        $datas = array();
        $dates = array();

        $stockMovements = $this->getStockMovements($variables['product_id'], $variables['date_from']);
        foreach($stockMovements as $sm)
        {
            $dates[$sm->getsm_date()] = 1;
        }

        $warehouses = Mage::helper('AdvancedStock/Product_Base')->getStocks($variables['product_id']);
        foreach($warehouses as $warehouse)
        {
            $warehouseId = $warehouse->getstock_id();

            //insert inital record
            $datas[] = array('x' => $this->formatDate($variables['date_from']),
                'w' => $warehouse->getStockName(),
                'y' => $this->getStockAtDate($variables['product_id'], $variables['date_from'], $warehouseId)
            );

            foreach($dates as $date => $nothing)
            {
                $datas[] = array('x' => $this->formatDate($date),
                    'w' => $warehouse->getStockName(),
                    'y' => $this->getStockAtDate($variables['product_id'], $date, $warehouseId)
                );

            }
        }

        return $datas;
    }

    protected function getStockAtDate($productId, $date, $warehouseId)
    {
        $stockItem = mage::getModel('cataloginventory/stock_item')->loadByProductWarehouse($productId, $warehouseId);
        return $stockItem->getQtyFromStockMovement($date);
    }

    protected function formatDate($dateString)
    {
        $t = strtotime($dateString);
        return date('Y-m-d', $t);
    }

    protected function getStockMovements($productId, $dateFrom)
    {
        return Mage::getModel('AdvancedStock/StockMovement')
                    ->getCollection()
                    ->addFieldToFilter('sm_product_id', $productId)
                    ->addFieldToFilter('sm_date', array('gt' => $dateFrom))
                    ->setOrder('sm_date', 'ASC');
    }
}

<?php

class MDN_SmartReport_Model_Source_Inventory_WarehouseValue extends MDN_SmartReport_Model_Source_Abstract {

    public function getCacheKey($variables, $limit)
    {
        return 'MDN_SmartReport_Model_Source_Inventory_WarehouseValue';
    }

    public function getData($variables, $limit)
    {
        $datas = array();

        $warehouses = Mage::getModel('AdvancedStock/Warehouse')->getCollection();
        foreach($warehouses as $warehouse)
        {

            $datas[] = array('x' => $warehouse->getStockName(),
                'y' => $warehouse->getStockValue()
            );
        }


        return $datas;
    }

}

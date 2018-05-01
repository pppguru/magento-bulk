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
 * @copyright  Copyright (c) 2016 Maison du Logiciel (http://www.maisondulogiciel.com)
 * @author : Guillaume SARRAZIN
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MDN_Purchase_Model_Mysql4_SupplyNeeds_NewCollection extends Mage_Catalog_Model_Resource_Product_Collection {

    CONST ROW_ID = 'entity_id';
    CONST JOIN_KEY = 'e.entity_id';

    private $_warehouseId = null;
   
    protected function _beforeLoad()
    {
        $this->_joinFields();
        return parent::_beforeLoad();
    }

    public function getWarehouseId(){
        return $this->_warehouseId;
    }

    public function setWarehouseId($warehouseId){
        return $this->_warehouseId = $warehouseId;
    }


    protected function _joinFields()
    {
        //SELECTS
        $this->selectAttributes();
        $this->addColumns();
        $this->addSpecificsColumns();

        //JOINS
        $this->joinStocks();
        $this->joinWarehouses();
        $this->joinProductAvailabilityStatus();
        $this->joinSalesHistory();
        $this->joinWaitingForDeliveryQtyByWarehouse();

        //FILTERING
        $this->applyWhere();
        $this->applyGroupBy();

        return $this;
    }

    public function getExpressionForField($fieldName){
        $expression = '';

        $SUM = (!$this->getWarehouseId())?'SUM':'';

        switch ($fieldName) {
            case 'warning_stock_level':
                $warningStockLevelConfValue = (int)Mage::getStoreConfig('cataloginventory/item_options/notify_stock_qty');
                $expression = 'if (cisi.use_config_notify_stock_qty = 1, GREATEST(IFNULL('.$warningStockLevelConfValue.',0),0), GREATEST(cisi.notify_stock_qty,0))';
                break;
            case 'ideal_stock_level':
                $idealStockLevelConfValue = (int)Mage::getStoreConfig('advancedstock/prefered_stock_level/ideal_stock_default_value');
                $expression = 'if (cisi.use_config_ideal_stock_level = 1, GREATEST(IFNULL('.$idealStockLevelConfValue.',0),0), GREATEST(cisi.ideal_stock_level,0))';
                break;
            case 'qty_needed_for_valid_orders':
                $expression = 'if (cisi.qty > cisi.stock_ordered_qty_for_valid_orders, 0, cisi.stock_ordered_qty_for_valid_orders - cisi.qty)';
                break;
            case 'qty_needed_for_orders':
                $expression = 'if (cisi.qty > cisi.stock_ordered_qty, 0, cisi.stock_ordered_qty - cisi.qty)';
                break;
            case 'available_qty':
                $expression = 'if (cisi.qty > cisi.stock_ordered_qty, cisi.qty - cisi.stock_ordered_qty, 0)';
                break;
            case 'qty_needed_for_ideal_stock':
                $expression = 'if ('.$this->getExpressionForField('available_qty').' < '.$this->getExpressionForField('warning_stock_level').', '.$this->getExpressionForField('ideal_stock_level').' - '.$this->getExpressionForField('available_qty') .', 0)';
                break;
            case 'qty_needed_for_manual_supply_needs':
                $expression = 'if (IFNULL({{manual_supply_need_qty}},0) > if (cisi.qty > cisi.stock_ordered_qty, cisi.qty - cisi.stock_ordered_qty, 0), IFNULL({{manual_supply_need_qty}},0) - if (cisi.qty > cisi.stock_ordered_qty, cisi.qty - cisi.stock_ordered_qty, 0) , 0)';;
                break;
            case 'qty_min':
                $expression = $SUM.'('.$this->getExpressionForField('qty_needed_for_valid_orders').') - '.$this->getExpressionForField('waiting_for_delivery_qty');
                break;
            case 'qty_max':
                $expression = $SUM.'('.$this->getExpressionForField('qty_needed_for_orders').' + '.$this->getExpressionForField('qty_needed_for_ideal_stock').' + '.$this->getExpressionForField('qty_needed_for_manual_supply_needs').') - '.$this->getExpressionForField('waiting_for_delivery_qty');
                break;
            case 'waiting_for_delivery_qty':
                $expression = 'IFNULL('.((!$this->getWarehouseId())?'{{waiting_for_delivery_qty}}':'waiting_for_delivery_qty').',0)';
                break;
            case 'sn_status':

                $validOrderExpression = $this->getExpressionForField('qty_needed_for_valid_orders').') > 0 and ('.$SUM.'('.$this->getExpressionForField('qty_needed_for_valid_orders').') >  '.$this->getExpressionForField('waiting_for_delivery_qty');
                $ordersExpression = $this->getExpressionForField('qty_needed_for_orders').') > 0 and ('.$SUM.'('.$this->getExpressionForField('qty_needed_for_orders').') - '.$SUM.'('.$this->getExpressionForField('qty_needed_for_valid_orders').') > ( '.$this->getExpressionForField('waiting_for_delivery_qty').' - '.$SUM.'('.$this->getExpressionForField('qty_needed_for_valid_orders').'))';
                $preferredStockLevelExpression = $this->getExpressionForField('qty_needed_for_ideal_stock').') > 0 and ('.$SUM.'('.$this->getExpressionForField('qty_needed_for_orders').') + '.$SUM.'('.$this->getExpressionForField('qty_needed_for_ideal_stock').') >  '.$this->getExpressionForField('waiting_for_delivery_qty');
                $manualSupplyNeedExpression = $this->getExpressionForField('qty_needed_for_manual_supply_needs');

                if($this->getWarehouseId()){
                    $ordersExpression = $this->getExpressionForField('qty_needed_for_orders').') > 0 
                                and (('.$this->getExpressionForField('qty_needed_for_orders').') > ('.$this->getExpressionForField('waiting_for_delivery_qty').' - '.$this->getExpressionForField('qty_needed_for_valid_orders').')';

                    $preferredStockLevelExpression = $this->getExpressionForField('qty_needed_for_ideal_stock').') > 0 
                    and (('.$this->getExpressionForField('qty_needed_for_ideal_stock').') >  ('.$this->getExpressionForField('waiting_for_delivery_qty').' - '.$this->getExpressionForField('qty_needed_for_valid_orders').' + '.$this->getExpressionForField('qty_needed_for_orders').' )';
                }

                $expression =  'if (
                  '.$SUM.'('.$validOrderExpression.'),\'1_valid_orders\',
                  if (
                      '.$SUM.'('.$ordersExpression.'),\'2_orders\',
                      if (
                          '.$SUM.'('.$preferredStockLevelExpression.' ),\'3_prefered_stock_level\',
                          if (
                              '.$SUM.'('.$manualSupplyNeedExpression.') > 0,\'4_manual_supply_need\',
                              \'5_pending_delivery\'
                             )
                          )
                      )
                  )';


                break;
            case 'avg_sales_week':
                $nbWeek1 = mage::getStoreConfig('advancedstock/sales_history/period_1');
                $nbWeek2 = mage::getStoreConfig('advancedstock/sales_history/period_2');
                $nbWeek3 = mage::getStoreConfig('advancedstock/sales_history/period_3');
                $nbWeeksTotal = $nbWeek1 + $nbWeek2 + $nbWeek3;
                $expression = 'round(((IFNULL(sh_period_1,0) + IFNULL(sh_period_2,0) + IFNULL(sh_period_3,0))/'.$nbWeeksTotal.'),4)';
                break;
            case 'run_out':
                $expression = 'round(IFNULL(pa_available_qty,0)/('.$this->getExpressionForField('avg_sales_week').'/7),4)';
                break;
            case 'purchase_before':
                $expression = 'round('.$this->getExpressionForField('run_out').' - IFNULL(pa_supply_delay,0),4)';
                break;
            case 'manufacturer_id':
                $expression = '{{'.$fieldName.'}}';
                break;
        }
        return $expression;
    }

    private function getExpressionWithAttributes($expression,$valueArray){
        $expressionPattern = $expression;
        foreach($valueArray as $key => $value){
            $expressionPattern = str_replace('{{'.$key.'}}', $value, $expressionPattern);
        }
        return $expressionPattern;
    }

    private function addColumnToSelectWithExpression($fieldName){
        $this->getSelect()->columns(array($fieldName => new Zend_Db_Expr($this->getExpressionForField($fieldName))));
    }

    protected function selectAttributes(){

        $this->addAttributeToSelect('name')
            ->addAttributeToSelect('sku')
            ->addAttributeToSelect('status')
            ->addAttributeToSelect('exclude_from_supply_needs')
            ->addAttributeToSelect('manual_supply_need_qty');

        $manufacturerCode = mage::getModel('AdvancedStock/Constant')->GetProductManufacturerAttributeCode();
        if ($manufacturerCode)
            $this->addAttributeToSelect($manufacturerCode);

        if(!$this->getWarehouseId())
             $this->addAttributeToSelect('waiting_for_delivery_qty');
    }

    protected function addColumns()
    {
        //WARNING STOCK LEVEL
        $this->addColumnToSelectWithExpression('warning_stock_level');

        //IDEAL STOCK LEVEL
        $this->addColumnToSelectWithExpression('ideal_stock_level');

        //QTY NEEDED FOR VALID ORDERS
        $this->addColumnToSelectWithExpression('qty_needed_for_valid_orders');

        //QTY NEEDED FOR ORDERS
        $this->addColumnToSelectWithExpression('qty_needed_for_orders');

        //QTY NEEDED FOR IDEAL STOCK
        $this->addColumnToSelectWithExpression('qty_needed_for_ideal_stock');

        //AVERAGE SALES
        $this->addColumnToSelectWithExpression('avg_sales_week');

        //RUN OUT
        $this->addColumnToSelectWithExpression('run_out');

        //PURCHASE BEFORE
        $this->addColumnToSelectWithExpression('purchase_before');

    }

    protected function addSpecificsColumns()
    {
        //MANUFACTURER ID
        $manufacturerCode = mage::getModel('AdvancedStock/Constant')->GetProductManufacturerAttributeCode();
        if ($manufacturerCode){
            $this->addExpressionAttributeToSelect('manufacturer_id','{{'.$manufacturerCode.'}}',array($manufacturerCode));
        }

        //QTY NEEDED FOR MANUAL SUPPLY NEED
        $this->addExpressionAttributeToSelect('qty_needed_for_manual_supply_needs', $this->getExpressionForField('qty_needed_for_manual_supply_needs'), array('manual_supply_need_qty'));

        if(!$this->getWarehouseId()) {
            //SUPPLY NEED STATUS
            $this->addExpressionAttributeToSelect('sn_status', $this->getExpressionForField('sn_status'), array('waiting_for_delivery_qty', 'manual_supply_need_qty'));

            //MIN
            $this->addExpressionAttributeToSelect('qty_min', 'GREATEST(' . $this->getExpressionForField('qty_min') . ', 0)', array('waiting_for_delivery_qty'));

            //MAX
            $this->addExpressionAttributeToSelect('qty_max', 'GREATEST(' . $this->getExpressionForField('qty_max') . ', 0)', array('waiting_for_delivery_qty', 'manual_supply_need_qty'));
        }else{
            //SUPPLY NEED STATUS
            $this->addExpressionAttributeToSelect('sn_status', $this->getExpressionForField('sn_status'), array('manual_supply_need_qty'));

            //MIN
            $this->addExpressionAttributeToSelect('qty_min', 'GREATEST((' . $this->getExpressionForField('qty_min') . '), 0)', array());

            //MAX
            $this->addExpressionAttributeToSelect('qty_max', 'GREATEST((' . $this->getExpressionForField('qty_max') . '), 0)', array('manual_supply_need_qty'));
        }
    }

    protected function joinStocks(){

        $joinExpression = 'cisi.product_id = '.self::JOIN_KEY.' AND (cisi.use_config_manage_stock = 1 OR cisi.manage_stock = 1)';

        if($this->getWarehouseId())
            $joinExpression .= ' AND cisi.stock_id = '.$this->getWarehouseId();

        $this->getSelect()
            ->join(array('cisi' => Mage::getSingleton('core/resource')->getTableName('cataloginventory_stock_item')),
                $joinExpression,
                array('*'));
    }

    protected function joinWarehouses(){
        $this->getSelect()
            ->join(array('cis' => Mage::getSingleton('core/resource')->getTableName('cataloginventory_stock')),
                'cisi.stock_id = cis.stock_id
                AND (cisi.use_config_manage_stock = 1 OR cisi.manage_stock = 1)
                AND cis.stock_disable_supply_needs <> 1',
                array('*'));
    }

    protected function joinProductAvailabilityStatus(){
        $this->getSelect()
            ->joinLeft(array('pa' => Mage::getSingleton('core/resource')->getTableName('product_availability')),
                'pa.pa_product_id = '.self::JOIN_KEY.' and pa_website_id = 0',
                array('*'));
    }

    protected function joinSalesHistory(){
        $tableDefinition = Mage::getSingleton('core/resource')->getTableName('erp_sales_history');
        $this->getSelect()
            ->joinLeft(array('sh' => $tableDefinition),
                'sh.sh_product_id = '.self::JOIN_KEY.' AND sh.sh_stock_id = cisi.stock_id',
                array('*'));
    }

    /**
     * Public because called in
     * MDN_Purchase_Adminhtml_Purchase_SupplyNeedsController::CreatePoFromStatsAction
     */
    public function joinSuppliersById($supplierId){
        $this->getSelect()
            ->join(array('pps' => Mage::getSingleton('core/resource')->getTableName('purchase_product_supplier')),
                'pps.pps_product_id = '.self::JOIN_KEY.' AND pps.pps_supplier_num ='.$supplierId ,
                array('*'));

        return $this;
    }

    protected function joinWaitingForDeliveryQtyByWarehouse(){

        if($this->getWarehouseId()) {

            $subTableName = 'waiting_for_delivery_qty_table';

            $subQueryExpression = '( SELECT 
            IFNULL(sum(GREATEST((pop_qty - pop_supplied_qty),0)),0) AS waiting_for_delivery_qty, 
            pop_product_id AS waiting_for_delivery_product_id
			FROM '.Mage::getSingleton('core/resource')->getTableName('purchase_order').', 
			 '.Mage::getSingleton('core/resource')->getTableName('purchase_order_product').'
			WHERE po_num  = pop_order_num			
			AND po_status = "'.MDN_Purchase_Model_Order::STATUS_WAITING_FOR_DELIVERY.'" 
			AND po_target_warehouse = '.$this->getWarehouseId().'
			GROUP BY pop_product_id  
			)';

            $joinExpression = '`'.$subTableName.'`.waiting_for_delivery_product_id = '.self::JOIN_KEY;

            $this->getSelect()
                ->joinLeft(array($subTableName => new Zend_Db_Expr($subQueryExpression)),
                    $joinExpression,
                    array('waiting_for_delivery_qty AS waiting_for_delivery_qty'));

        }
    }

    protected function applyWhere(){

        $this->getSelect()->where('
            exclude_from_supply_needs = 0
           AND (
                ('.$this->getExpressionForField('qty_needed_for_valid_orders').' > 0)
                OR
                ('.$this->getExpressionForField('qty_needed_for_orders').' > 0)
                OR
                ('.$this->getExpressionForField('qty_needed_for_ideal_stock').' > 0)
                OR
                ('.$this->getExpressionWithAttributes($this->getExpressionForField('qty_needed_for_manual_supply_needs'),
                    array('manual_supply_need_qty' => '`at_manual_supply_need_qty`.`value`')
                ).' > 0)                
           )
        ');

    }


    protected function applyGroupBy(){
        if(!$this->getWarehouseId())
            $this->getSelect()->group(new Zend_Db_Expr('entity_id,sku'));
    }


    // AVOID ORDER BY TO CRASH
    public function setOrder($attribute, $dir = 'DESC')
    {
        //List here the one that are not from the main table
        switch($attribute) {
            case 'stock_id':
            case 'product_id':
            case 'qty':
            case 'waiting_for_delivery_qty':
            case 'manual_supply_need_qty':
            case 'exclude_from_supply_needs':
            case 'run_out':
            case 'avg_sales_week':
            case 'pa_supply_delay':
            case 'purchase_before':
                $this->getSelect()->order($attribute . ' ' . $dir);
                break;
            default:
                parent::setOrder($attribute, $dir);
        }
        return $this;
    }

    //AVOID FILTER TO CRASH
    public function addAttributeToFilter($attribute, $condition = null, $joinType = 'inner')
    {
        //List here the one that are not from the main table
        switch($attribute) {
            case 'sn_status':
                if(!$this->getWarehouseId()) {
                    $replacementArray = array('manual_supply_need_qty' => '`at_manual_supply_need_qty`.`value`',
                        'waiting_for_delivery_qty' =>'`at_waiting_for_delivery_qty`.`value`');
                }else{
                    $replacementArray = array('manual_supply_need_qty' => '`at_manual_supply_need_qty`.`value`',
                        'waiting_for_delivery_qty' => 'waiting_for_delivery_qty');
                }

                $conditionSql = $this->_getConditionSql($this->getExpressionWithAttributes(
                    $this->getExpressionForField('sn_status'),
                    $replacementArray), $condition);

                //HAVING enable to use the SUM present in STATUS calculation (it is forbidden in the of where clause)
                $this->getSelect()->having($conditionSql);
                break;
            case 'manufacturer_id':
                $replacementArray = array($attribute => '`at_manufacturer`.`value`');
                $conditionSql = $this->_getConditionSql($this->getExpressionWithAttributes(
                    $this->getExpressionForField($attribute),
                    $replacementArray), $condition);
                $this->getSelect()->where($conditionSql);
                break;
            case 'run_out':
            case 'purchase_before':
            case 'avg_sales_week':
                $conditionSql = $this->_getConditionSql($this->getExpressionForField($attribute), $condition);
                $this->getSelect()->where($conditionSql);
                break;
            case 'pa_supply_delay':
            case 'stock_id':
            case 'product_id':
            case 'qty':
                $conditionSql = $this->_getConditionSql($attribute, $condition);
                $this->getSelect()->where($conditionSql);
                break;
            default:
                parent::addAttributeToFilter($attribute, $condition, $joinType);
                break;
        }
        return $this;
    }

    protected function _getSelectCountSql($select = null, $resetLeftJoins = true)
    {
        $this->_renderFilters();
        $countSelect = (is_null($select)) ?
            $this->_getClearSelect() :
            $this->_buildClearSelect($select);

        // Clear GROUP condition for count method
        $countSelect->reset(Zend_Db_Select::GROUP);

        if(count($this->getSelect()->getPart('having')) > 0) {
            $countSelect->group(new Zend_Db_Expr('product_id'));
            if(!$this->getWarehouseId()) {
                $countSelect->columns('COUNT(DISTINCT '.self::JOIN_KEY.'), `at_manual_supply_need_qty`.`value`, `at_waiting_for_delivery_qty`.`value`, `at_manufacturer`.`value` AS `manufacturer_id` ');
            }else{
                $countSelect->columns('COUNT(DISTINCT '.self::JOIN_KEY.'), `at_manual_supply_need_qty`.`value`, cisi.qty, cisi.stock_ordered_qty_for_valid_orders, waiting_for_delivery_qty, cisi.stock_ordered_qty, cisi.use_config_notify_stock_qty, cisi.notify_stock_qty, cisi.use_config_ideal_stock_level, cisi.ideal_stock_level, `at_manufacturer`.`value` AS `manufacturer_id` ');
            }
        }else{
            $countSelect->columns('COUNT(DISTINCT '.self::JOIN_KEY.')');
        }

        return $countSelect;
    }



    //- FOR stat screen
    /**
     * Return ids for suppliers used in supply needs
     */
    public function getSupplierIds() {

        $productSupplierTable = Mage::getSingleton('core/resource')->getTableName('Purchase/ProductSupplier');

        $this->getSelect()
            ->reset()
            ->from($this->getMainTable(), '')
            ->from($productSupplierTable, 'pps_supplier_num')
            ->where('pps_product_id = product_id');

        return array_unique($this->getConnection()->fetchCol($this->getSelect()));
    }

    /**
     * Return amount for one supplier / one status
     */
    public function getAmount($supplierId, $status, $mode) {


        $productSupplierTable = Mage::getSingleton('core/resource')->getTableName('Purchase/ProductSupplier');
        $this->getSelect()
            ->reset()
            ->from($this->getMainTable(), 'sum(' . $mode . ' * pps_last_unit_price)')
            ->from($productSupplierTable, '')
            ->where('pps_product_id = product_id')
            ->where('pps_supplier_num = ' . $supplierId)
        ;
        if ($status)
            $this->getSelect()->where("sn_status = '".$status."'");

        $value = $this->getConnection()->fetchOne($this->getSelect());
        return number_format($value, 0, '', '');
    }

}
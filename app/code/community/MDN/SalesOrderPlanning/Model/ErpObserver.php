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
class MDN_SalesOrderPlanning_Model_ErpObserver {

    /**
     * When product / supplier association change, update product availability
     */
    public function purchase_product_supplier_after_save(Varien_Event_Observer $observer) {

        //get object
        $object = $observer->getEvent()->getproduct_supplier();
        $mustRefreshProductAvailability = false;

        //if created or deleted
        if ($object->getOrigData('pps_num') == null)
            $mustRefreshProductAvailability = true;

        //if usefull information changed
        if (($object->getpps_can_dropship() != $object->getOrigData('pps_can_dropship'))
                || ($object->getpps_quantity_product() != $object->getOrigData('pps_quantity_product'))
                || ($object->getpps_supply_delay() != $object->getOrigData('pps_supply_delay'))
        )
            $mustRefreshProductAvailability = true;

        //if we have to refresh
        if ($mustRefreshProductAvailability) {
            $productId = $object->getpps_product_id();
            mage::helper('BackgroundTask')->AddTask('Update product availability status for product #' . $productId, 'SalesOrderPlanning/ProductAvailabilityStatus', 'RefreshForOneProduct', $productId
            );
        }
    }

    /**
     * Plan planning update when is_valid or stocks_updated change
     *
     */
    public function sales_order_save_after(Varien_Event_Observer $observer) {

        $order = $observer->getEvent()->getOrder();
        $orderId = $order->getId();

        //process planning
        if (!Mage::helper('SalesOrderPlanning')->planningIsEnabled())
            return false;

        if (($order->getis_valid() != $order->getOrigData('is_valid'))) {
            mage::helper('SalesOrderPlanning/Planning')->storePaymentdate($orderId);
            mage::helper('SalesOrderPlanning/Planning')->planPlanningUpdate($orderId);
        }
    }

    /**
     * Event called when order is considered by ERP cron
     * @param Varien_Event_Observer $observer
     */
    public function advancedstock_order_considered_by_cron(Varien_Event_Observer $observer) {

       $orderId = $observer->getEvent()->getorder_id();

       if (Mage::helper('SalesOrderPlanning')->planningIsEnabled()){
          //check if planning exists
          $planning = mage::getModel('SalesOrderPlanning/Planning')->load($orderId, 'psop_order_id');
          if (!$planning->getId()) {
              mage::helper('BackgroundTask')->AddTask('Create planning for order #' . $orderId,
                      'SalesOrderPlanning/Planning',
                      'createPlanningFromOrderId',
                      $orderId, null, false, 2);
          }
        }
    }

    /**
     * Save custom data from erp product sheet
     *
     * @param Varien_Event_Observer $observer
     */
    public function advancedstock_product_sheet_save(Varien_Event_Observer $observer) {
        
        if (Mage::getSingleton('admin/session')->isAllowed('admin/erp/products/view/outofstock_period'))
        {

            //init vars
            $data = $observer->getEvent()->getpost_data();
            $product = $observer->getEvent()->getproduct();

            //save custom data
            $outOfStockData = $data['outofstock'];

            $needUpdate = false;

            //manage magento check box bug
            if (!isset($outOfStockData['outofstock_period_enabled']))
                $outOfStockData['outofstock_period_enabled'] = '0';

            //check if field are necessary to be saved
            if($product->getoutofstock_period_enabled() != $outOfStockData['outofstock_period_enabled']){
                $product->setoutofstock_period_enabled($outOfStockData['outofstock_period_enabled']);
                $needUpdate = true;
            }

            if($product->getoutofstock_period_from() != $outOfStockData['outofstock_period_from']){
                $product->setoutofstock_period_from($outOfStockData['outofstock_period_from']);
                $needUpdate = true;
            }

            if($product->getoutofstock_period_to() != $outOfStockData['outofstock_period_to']){
                $product->setoutofstock_period_to($outOfStockData['outofstock_period_to']);
                $needUpdate = true;
            }

            //save only if out of stock period has changed
            if($needUpdate)
                $product->save();
        }
    }

    /**
     * Called when product delivery date changes
     */
    public function product_delivery_date_change(Varien_Event_Observer $observer) {
        //get datas
        $productId = $observer->getEvent()->getproduct_id();
        $deliveryDate = $observer->getEvent()->getnew_value();

        //Update pending orders planning
        $pendingOrdersIds = mage::helper('AdvancedStock/Sales_PendingOrders')->getPendingOrderIdsForProduct($productId);
        foreach ($pendingOrdersIds as $orderId) {
            mage::helper('SalesOrderPlanning/Planning')->planPlanningUpdate($orderId);
        }

        //update product availability status
        mage::helper('BackgroundTask')->AddTask('Update product availability status for product #' . $productId . ' (delivery date has changed)', 'SalesOrderPlanning/ProductAvailabilityStatus', 'RefreshForOneProduct', $productId, null, true
        );
    }

    /**
     * Waiting for delivery qty has changed for product
     * */
    public function product_waiting_for_delivery_qty_change(Varien_Event_Observer $observer) {
        //get datas
        $productId = $observer->getEvent()->getproduct_id();

        //update product availability status
        mage::helper('BackgroundTask')->AddTask('Update product availability status for product #' . $productId . ' (Waiting for delivery qty has changed)', 'SalesOrderPlanning/ProductAvailabilityStatus', 'RefreshForOneProduct', $productId, null, true
        );
    }

    /**
     * Product after save : update product availability status
     *
     * @param Varien_Event_Observer $observer
     */
    public function catalog_product_save_after(Varien_Event_Observer $observer) {
        $product = $observer->getEvent()->getproduct();
        $mustUpdateProductAvailabilityStatus = false;
        $reason = '';

        if ($this->objectDataHasChanged($product, 'default_supply_delay')) {
            $reason = '(Supply delay changed)';
            $mustUpdateProductAvailabilityStatus = true;
        }

        if ($this->objectDataHasChanged($product, 'outofstock_period_enabled')) {
            $reason = '(Out of stock enabled changed)';
            $mustUpdateProductAvailabilityStatus = true;
        }

        if ($this->objectDataHasChanged($product, 'outofstock_period_from')) {
            $reason = '(Out of stock from changed)';
            $mustUpdateProductAvailabilityStatus = true;
        }

        if ($this->objectDataHasChanged($product, 'outofstock_period_to')) {
            $reason = '(Out of stock to changed)';
            $mustUpdateProductAvailabilityStatus = true;
        }

        if ($mustUpdateProductAvailabilityStatus) {
            $productId = $product->getId();
            mage::helper('BackgroundTask')->AddTask('Update product availability status for product #' . $productId . ' ' . $reason,
                    'SalesOrderPlanning/ProductAvailabilityStatus',
                    'RefreshForOneProduct',
                    $productId,
                    null,
                    true
            );
        }
    }

    /**
     * Plan planning update when product reservation change
     *
     * @param Varien_Event_Observer $observer
     */
    public function advancedstock_order_item_reserved_qty_changed(Varien_Event_Observer $observer) {

        if (!Mage::helper('SalesOrderPlanning')->planningIsEnabled())
            return false;

        $orderItem = $observer->getEvent()->getorder_item();
        $orderId = $orderItem->getorder_id();

        //store real fullstock date
        mage::helper('BackgroundTask')->AddTask('Store real fullstock date for order #' . $orderId, 'SalesOrderPlanning/Planning', 'storeRealFullStockDate', $orderId, null, true, 5);

        //plan planning update
        mage::helper('SalesOrderPlanning/Planning')->planPlanningUpdate($orderId);
    }

    /**
     * Plan planning update when shipment is created
     *
     * @param Varien_Event_Observer $observer
     */
    public function sales_order_shipment_save_after(Varien_Event_Observer $observer) {

        if (!Mage::helper('SalesOrderPlanning')->planningIsEnabled())
            return false;

        $shipment = $observer->getEvent()->getshipment();
        $orderId = $shipment->getorder_id();

        $groupTask = null;
        $skipIfAlreadyPlanned = true;
        $priority = 4;
        mage::helper('BackgroundTask')->AddTask(
            'Update planning for order #' . $orderId.' (on shipment)',
            'SalesOrderPlanning/Planning',
            'updatePlanning',
            $orderId,
            $groupTask,
            $skipIfAlreadyPlanned,
            $priority
        );
    }

    public function sales_order_creditmemo_save_after(Varien_Event_Observer $observer) {

        if (!Mage::helper('SalesOrderPlanning')->planningIsEnabled())
            return false;

        $creditMemo = $observer->getEvent()->getcreditmemo();
        $order = $creditMemo->getOrder();
        $orderId = $order->getId();       

        $groupTask = null;
        $skipIfAlreadyPlanned = true;
        $priority = 4;
        mage::helper('BackgroundTask')->AddTask(
            'Update planning for order #' . $orderId.' (on credit memo)',
            'SalesOrderPlanning/Planning',
            'updatePlanning',
            $orderId,
            $groupTask,
            $skipIfAlreadyPlanned,
            $priority
        );
    }

    /**
     * Add planning column to sales order grid
     *
     * @param Varien_Event_Observer $observer
     */
    public function advancedstock_sales_order_grid_before_render(Varien_Event_Observer $observer) {

        if (!Mage::helper('SalesOrderPlanning')->planningIsEnabled())
            return false;

        $grid = $observer->getEvent()->getgrid();
        if ($grid)
        {
            $grid->addColumnAfter('planning', array(
                'header' => Mage::helper('SalesOrderPlanning')->__('Planning'),
                'index' => 'planning',
                'renderer' => 'MDN_SalesOrderPlanning_Block_Widget_Grid_Column_Renderer_OrderPlanning',
                'align' => 'center',
                'filter' => false,
                'sortable' => false,
                'width' => '150px'
            ), 'status');
        }
    }

    /**
     * Add planning to pending sales order grid
     *
     * @param Varien_Event_Observer $observer
     */
    public function advancedstock_pendingsalesorders_grid_preparecolumns(Varien_Event_Observer $observer) {

        if (!Mage::helper('SalesOrderPlanning')->planningIsEnabled())
            return false;

        $grid = $observer->getEvent()->getgrid();

        $grid->addColumn('planning', array(
            'header' => Mage::helper('SalesOrderPlanning')->__('Planning'),
            'index' => 'planning',
            'renderer' => 'MDN_SalesOrderPlanning_Block_Widget_Grid_Column_Renderer_OrderPlanning',
            'align' => 'center',
            'filter' => false,
            'sortable' => false
        ));
    }

    /**
     * Add planning column to selected, stockless, fullstock, ignored orders grid
     *
     */
    public function orderpreparartion_createcolums(Varien_Event_Observer $observer) {

        if (!Mage::helper('SalesOrderPlanning')->planningIsEnabled())
            return false;

        $grid = $observer->getEvent()->getgrid();

        $grid->addColumn('planning', array(
            'header' => Mage::helper('SalesOrderPlanning')->__('Planning'),
            'index' => 'planning',
            'renderer' => 'MDN_SalesOrderPlanning_Block_Widget_Grid_Column_Renderer_OrderPlanning',
            'align' => 'center',
            'filter' => false,
            'sortable' => false
        ));
    }

    /**
     * Add custom tabs to erp product sheet
     *
     */
    public function advancedstock_product_edit_create_tabs(Varien_Event_Observer $observer) {
        //init vars
        $tab = $observer->getEvent()->gettab();
        $product = $observer->getEvent()->getproduct();
        $layout = $observer->getEvent()->getlayout();

        //add out of stock perio tabs
        if (Mage::getSingleton('admin/session')->isAllowed('admin/erp/products/view/outofstock_period')) {
            $tab->addTab('tab_outofstock_period', array(
                'label' => Mage::helper('SalesOrderPlanning')->__('Out Of Stock Period'),
                'content' => $layout->createBlock('SalesOrderPlanning/Product_Edit_Tabs_OutOfStockPeriod')
                        ->setTemplate('SalesOrderPlanning/Product/Edit/Tab/OutOfStockPeriod.phtml')
                        ->setProduct($product)
                        ->toHtml(),
            ));
        }

        //add availability status tab
        if (Mage::getSingleton('admin/session')->isAllowed('admin/erp/products/view/availability_status')) {
            $tab->addTab('tab_availability_status', array(
                'label' => Mage::helper('SalesOrderPlanning')->__('Availability Status'),
                'content' => $layout->createBlock('SalesOrderPlanning/Product_Edit_Tabs_AvailabilityStatus')
                        ->setTemplate('SalesOrderPlanning/Product/Edit/Tab/AvailabilityStatus.phtml')
                        ->setProduct($product)
                        ->toHtml(),
            ));
        }
    }

    /**
     * Supplier information change
     *
     * @param Varien_Event_Observer $observer
     */
    public function purchase_supplier_aftersave(Varien_Event_Observer $observer) {

        //init vars
        $supplier = $observer->getEvent()->getsupplier();

        //if supply delay changes, update product availability status for linked products
        if ($this->objectDataHasChanged($supplier, 'sup_supply_delay') || $this->objectDataHasChanged($supplier, 'sup_shipping_delay')) {

            foreach ($supplier->getProducts() as $product) {
                $productId = $product->getId();
                mage::helper('BackgroundTask')->AddTask('Update product availability status for product #' . $productId . ' (supplier supply delay changed)',
                        'SalesOrderPlanning/ProductAvailabilityStatus',
                        'RefreshForOneProduct',
                        $productId,
                        null,
                        true
                );
            }
        }
    }

    /**
     * Stock information changed
     *
     * @param Varien_Event_Observer $observer
     */
    public function advancedstock_stock_aftersave(Varien_Event_Observer $observer) {
        $stock = $observer->getEvent()->getstock();
        $productId = $stock->getproduct_id();

        $mustUpdateProductAvailabilityStatus = false;
        $reason = '(Stock information has changed)';

        if ($this->objectDataHasChanged($stock, 'qty')){
            $reason = '(Stock qty has changed)';
            $mustUpdateProductAvailabilityStatus = true;
        }
        
        if ($this->objectDataHasChanged($stock, 'manage_stock')){
            $reason = '(Manage stock setting has changed)';
            $mustUpdateProductAvailabilityStatus = true;
        }

        if ($this->objectDataHasChanged($stock, 'backorders')){
            $reason = '(Use backorders setting has changed)';
            $mustUpdateProductAvailabilityStatus = true;
        }

        if ($this->objectDataHasChanged($stock, 'use_config_backorders')){
            $reason = '(Use default backorders setting has changed)';
            $mustUpdateProductAvailabilityStatus = true;
        }

        if ($this->objectDataHasChanged($stock, 'is_in_stock')){
            $reason = '(Is in stock has changed)';
            $mustUpdateProductAvailabilityStatus = true;
        }
        
        if ($this->objectDataHasChanged($stock, 'stock_ordered_qty')){
            $reason = '(Stock ordered qty has changed)';
            $mustUpdateProductAvailabilityStatus = true;
        }

        if ($mustUpdateProductAvailabilityStatus) {
            mage::helper('BackgroundTask')->AddTask(
                    'Update product availability status for product #' . $productId . ' '.$reason,
                    'SalesOrderPlanning/ProductAvailabilityStatus',
                    'RefreshForOneProduct',
                    $productId,
                    null,
                    true
            );
        }
    }

    /**
     * Add out of stock range column in erp > products
     *
     * @param Varien_Event_Observer $observer
     */
    public function advancedstock_product_grid_preparecolumns(Varien_Event_Observer $observer) {
        $grid = $observer->getEvent()->getgrid();

        $grid->addColumn('outofstock_range', array(
            'header' => Mage::helper('SalesOrderPlanning')->__('Out of stock range'),
            'renderer' => 'MDN_SalesOrderPlanning_Block_Widget_Grid_Column_Renderer_ProductOutOfStockPeriod',
            'align' => 'center',
            'filter' => false,
            'sortable' => false
        ));
    }

    //*********************************************************************************************************************************************************************************
    //*********************************************************************************************************************************************************************************
    // Tools
    //*********************************************************************************************************************************************************************************
    //*********************************************************************************************************************************************************************************

    /**
     * Return true if data object changed (compare data and origdata)
     *
     * @param unknown_type $object
     * @param unknown_type $dataName
     */
    protected function objectDataHasChanged($object, $dataName) {
        $origValue = $object->getOrigData($dataName);
        $currentValue = $object->getData($dataName);

        return ($origValue != $currentValue);
    }

    /**
     * Refresh product avaibility status for each products of the order when order is cancelled
     * Especially usefull when Credit card failed and product was not yest reserved
     *
     * @param Varien_Event_Observer $observer
     */
    public function salesorder_just_cancelled(Varien_Event_Observer $observer) {

        try {

            $order = $observer->getEvent()->getorder();

            //refresh product avaibility status for each products
            if($order != null && $order->getId()){

                foreach ($order->getAllItems() as $item) {

                    try {

                        $pid = $item->getproduct_id();
                        if($pid){
                            mage::helper('SalesOrderPlanning/ProductAvailabilityStatus')->RefreshForOneProduct($pid);
                        }

                    } catch (Exception $salesOrderPlanningEx) {
                        mage::log('Error in ProductAvailabilityStatus RefreshForOneProduct : ' . $salesOrderPlanningEx->getMessage());
                    }

                }
            }

        } catch (Exception $ex) {
            mage::log('Error on salesorder_just_cancelled : ' . $ex->getMessage());
        }
    }

}
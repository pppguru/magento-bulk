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
class MDN_AdvancedStock_Model_Observer {

    private $_maxOrder;

    
    /**
     * Collect orders with stocks_updated = 0 and status not finished (complete or canceled)
     * 
     * @return type
     */
    public function getOrdersNotYetConsidered(){

        $collection = mage::getModel('sales/order')
                        ->getCollection()
                        ->addFieldToFilter('stocks_updated', '0')
                        ->addAttributeToSelect('*')
                        ->addAttributeToFilter('state', array('nin' => array('complete', 'canceled', 'closed')));

        //TODO : CONF TO EXCLUDE AFTER A DATE

        return $collection;
    }

    /**
     * THIS IS THE ENTRY POINT OF ERP
     *
     * Process orders and update ordered qty, reserved qty ....
     *
     */
    public function UpdateStocksForOrders($refuseDebug = false, $forceExecution = false) {

        $debug = '';    

        if ($forceExecution ||  (Mage::getStoreConfig('healthyerp/erp/enabled') == 1) && (Mage::getStoreConfig('healthyerp/erp/disable_cron') == 0)) {

            $debug = '<h1>Update stocks for orders</h1>';

            $collection = $this->getOrdersNotYetConsidered();

            //max orders
            $this->_maxOrder = (int)mage::getStoreConfig('advancedstock/cron/order_update_stocks_max');
            if ($this->_maxOrder > 0) {
                $collection->getSelect()->limit($this->_maxOrder);
            }

            $count = 0;
            foreach ($collection as $order) {

                $debug .= $this->UpdateStocksForOneOrder($order, $refuseDebug);

                //execute X orders at once
                if ($count > $this->_maxOrder) {
                    $debug .= '<br>Exit after ' . $this->_maxOrder . ' loops';
                    break;
                }
                $count++;
            }

            //print debug information
            if ($refuseDebug == false) {
                if (Mage::getStoreConfig('advancedstock/cron/debug')) {
                    echo $debug;
                }
            }
        }

        return $debug;
    }

    /**
     * Process 1 order and update ordered qty, reserved qty ....
     *
     */
    public function UpdateStocksForOneOrder($order, $refuseDebug = false) {

        $debug = '<br/><b>Processing Order #' . $order->getId() . ' (' . $order->getIncrementId() . ')</b> at ' . date('Y-m-d H:i:s');

        try {
            //parse each product
            $productReservationIsFullySuccessful = true;

            foreach ($order->getAllItems() as $item) {
                $debug .= '<br/>--> Process product '.$item->getName().' : ';

                if(!$item->getproduct_id()){
                    $debug .= '<br/>--> Skipped product '.$item->getName().' with no product id';
                    continue;
                }

                if (!Mage::helper('AdvancedStock/Product_Base')->productExists($item->getproduct_id()))
                {
                    $debug .= '<br/>--> Skipped product '.$item->getName().' cause does not exist';
                    continue;
                }

                //get preparation warehouse
                $preparationWarehouseId = mage::helper('AdvancedStock/Router')->getWarehouseForOrderItem($item, $order);
                if (!$preparationWarehouseId)
                    $preparationWarehouseId = 1;

                $debug .= 'Affect warehouse #'.$preparationWarehouseId;

                //Affect order item to warehouse
                try
                {
                    Mage::helper('AdvancedStock/Router')->affectWarehouseToOrderItem(array('order_item_id' => $item->getId(), 'warehouse_id' => $preparationWarehouseId));
                }
                catch(Exception $ex)
                {
                    $exMessage = $ex->getMessage();
                    if($exMessage){
                        $productReservationIsFullySuccessful = false;
                        $debug .= '<br/>--> Error updating stocks  because of product : '.$item->getsku().' (Id='.$item->getproduct_id().') for OrderItem #' . $item->getId() . ' : ' . $exMessage;
                    }
                }
            }

            //update stocks_updated
            if($productReservationIsFullySuccessful){ // enable to retry later if there was a deadlock issue
                if ($order->getPayment()) {
                    $this->setStocksAsUpdated($order);
                    $debug .= '<br/>-> Set stocks updated = 1 for order #' . $order->getId();
                    Mage::dispatchEvent('advancedstock_order_considered_by_cron', array('order_id' => $order->getId()));
                }else{
                    $debug .= '<br>-> Unable to retrieve payment for order #' . $order->getIncrementId();
                }
            }else{
                $debug .= '<br>-> Unable to affect warehouse and reserve stock for order #' . $order->getIncrementId();
            }
        } catch (Exception $ex) {
            Mage::logException($ex);
            $debug .= '<br/>-> Error updating stocks for order #' . $order->getIncrementId() . ' : ' . $ex->getMessage();
        }

        mage::log($debug, null, 'erp_new_orders_consideration.log');

        //print debug information
        if ($refuseDebug == false) {
            if (Mage::getStoreConfig('advancedstock/cron/debug')) {
                echo $debug;
            }
        }
        return $debug;
    }

    /**
     * Set stocks udpate to 1 in order
     * Use sql query instead of models to avoid to rewrite data as this process is executed by the cron
     */
    protected function setStocksAsUpdated($order)
    {
        $prefix = Mage::getConfig()->getTablePrefix();
        $sql = 'update '.$prefix.'sales_flat_order set stocks_updated = 1 where entity_id = '.$order->getId();
        mage::getResourceModel('catalog/product')->getReadConnection()->query($sql);
    }

    /**
     * Set payment validated to true when invoice is created
     *
     */
    public function sales_order_invoice_pay(Varien_Event_Observer $observer) {
        if (Mage::getStoreConfig('healthyerp/erp/enabled') == 1) {
            if (Mage::getStoreConfig('advancedstock/general/auto_validate_payment') == 1) {
                try {
                    $order = $observer->getEvent()->getInvoice()->getOrder();
                    $order->setpayment_validated(1);    //intentionnaly do not save, save is done by the event dispatcher !!!
                    mage::log('payment_validated set to true for order #' . $order->getId());
                } catch (Exception $ex) {
                    mage::log('Error when validating payment_validated: ' . $ex->getMessage());
                }
            }
        }
    }

    /**
     * Called when an order is placed
     *
     * @param Varien_Event_Observer $observer
     * @return none
     */
    public function sales_order_afterPlace(Varien_Event_Observer $observer) {

        if (Mage::getStoreConfig('healthyerp/erp/enabled') == 1) {
            try {
                $order = $observer->getEvent()->getOrder();

                //init payment validated
                if ($order->getpayment_validated() != 1)
                    $order->setpayment_validated(0);

                //copy cost
                $storeId = $order->getStore()->getId();
                $costColumnName = mage::helper('purchase/MagentoVersionCompatibility')->getSalesOrderItemCostColumnName();

                foreach ($order->getAllItems() as $item) {
                    $productId = $item->getproduct_id();
                    $product = mage::getModel('catalog/product')
                        ->setStoreId($storeId)
                        ->load($productId);
                    if ($product) {

                        //store cost
                        switch ($product->gettype_id()) {
                            case 'simple':
                                $item->setData($costColumnName, $product->getcost());
                                break;
                            case 'configurable':
                            case 'bundle':
                                $item->setData($costColumnName, $this->computeCostFromSubProducts($item, $order->getAllItems(), $storeId));
                                break;
                        }
                    }
                }

            } catch (Exception $ex) {
                Mage::logException($ex);
            }
        }
    }

    /**
     * Change stock column in rma product reservation to display information for every warehouse
     *
     * @param Varien_Event_Observer $observer
     */
    public function productreturn_reservationgrid_preparecolumns(Varien_Event_Observer $observer) {
        $grid = $observer->getEvent()->getgrid();

        $grid->addColumn('qty', array(
            'header' => Mage::helper('ProductReturn')->__('Stock'),
            'index' => 'entity_id',
            'renderer'	=> 'MDN_AdvancedStock_Block_Product_Widget_Grid_Column_Renderer_StockSummary',
            'filter' => 'MDN_AdvancedStock_Block_Product_Widget_Grid_Column_Filter_StockSummary',
            'sortable'	=> false
        ));
    }

    /**
     * Compute cost from the sum of the costs of subproducts
     *
     * @param unknown_type $parentItem
     * @param unknown_type $items
     */
    private function computeCostFromSubProducts($parentItem, $items, $storeId) {
        $cost = 0;
        $parentQuoteItemId = $parentItem->getquote_item_id();
        $parentItemQty = $parentItem->getqty_ordered();

        foreach ($items as $item) {
            if ($item->getquote_parent_item_id() == $parentQuoteItemId) {
                $product = mage::getModel('catalog/product')
                    ->setStoreId($storeId)
                    ->load($item->getproduct_id());

                $cost += $product->getCost() * ($item->getqty_ordered() / $parentItemQty);
            }
        }

        return $cost;
    }

    /**
     * Update sales history for every products
     * Added here just to get an entry in models for cron
     * Called every sunday night
     */
    public function updateAllSalesHistory() {
        if (Mage::getStoreConfig('healthyerp/erp/enabled') == 1) {
            if (mage::getStoreConfig('advancedstock/sales_history/enable_auto_update') == 1) {
                $helper = mage::helper('AdvancedStock/Sales_History');
                if($helper->isSalesHistoryInitialized()) {
                    $helper->scheduleUpdateForRecentSales();
                }else{
                    $helper->scheduleUpdateForAllProducts();
                }
            }
        }
    }

    /**
     * Called when sales history is updated
     */
    public function advancedstock_sales_history_change(Varien_Event_Observer $observer) {
        $salesHistory = $observer->getEvent()->getsales_history();

        //if auto calculate preferred stock level is enabled, refresh it
        if (mage::getStoreConfig('advancedstock/prefered_stock_level/enable_auto_calculation') == 1) {
            $productId = $salesHistory->getsh_product_id();
            mage::helper('AdvancedStock/Product_PreferedStockLevel')->updateForProduct($productId);
        }
    }

    /**
     * Display if an order is not considered by ERP or not
     */
    public function controller_action_predispatch_adminhtml_sales_order_view(Varien_Event_Observer $observer) {

        if (Mage::getStoreConfig('healthyerp/erp/enabled') == 1) {
            $data = $observer->getEvent()->getData();
            if ($data && is_array($data)) {
                $controllerAction = $data["controller_action"];
                $orderId = $controllerAction->getRequest()->getParam('order_id');

                if ($orderId && $orderId > 0) {
                    $order = Mage::getModel('sales/order')->load($orderId);

                    if (($order->getstocks_updated() == 0) && ($order->getState() != 'canceled') && ($order->getState() != 'complete'))
                        Mage::getSingleton('adminhtml/session')->addError(mage::helper('AdvancedStock')->__('Order not yet considered by ERP (ERP will consider it during the next cron execution)'));

                    if ($operator = Mage::getSingleton('AdvancedStock/Sales_Order_Tool')->isBeingDispatched($order))
                        Mage::getSingleton('adminhtml/session')->addSuccess(mage::helper('AdvancedStock')->__('This order is being dispatched by %s (you can not cancel it).', $operator));
                }
            }
        }
    }

    /**
     * Clean ERP datas before we delete an order
     *
     * @param Varien_Event_Observer $observer
     */
    public function sales_order_delete_before(Varien_Event_Observer $observer)
    {
        $order = $observer->getEvent()->getorder();

        //clean product reservation
        foreach ($order->getAllItems() as $item) {

            if($item->getproduct_id()) {
                //release stock in all cases
                mage::helper('AdvancedStock/Product_Reservation')->releaseProduct($order, $item);

                //force stock update
                mage::helper('AdvancedStock/Product_Base')->planUpdateStocksWithBackgroundTask($item->getproduct_id(), ' on delete order #'.$order->getIncrementId());
            }
        }

        //clean selected order
        $selectedOrderItem = Mage::getModel('Orderpreparation/ordertoprepare')->getCollection()->addFieldToFilter('order_id', $order->getId())->getFirstItem();
        if ($selectedOrderItem->getId()){
            $selectedOrderItem->delete();
        }

        //clean full stock, stock less, ignored orders tabs
        $orderPreparationItem = Mage::getModel('Orderpreparation/ordertopreparepending')->getCollection()->addFieldToFilter('opp_order_id', $order->getId())->getFirstItem();
        if ($orderPreparationItem->getId()){
            $orderPreparationItem->delete();
        }
    }

    /**
     * Add erp view button in product view
     *
     * @param Varien_Event_Observer $observer
     */
    public function controller_action_layout_render_before_adminhtml_catalog_product_edit(Varien_Event_Observer $observer)
    {
        $block = Mage::getSingleton('core/layout')->getBlock('product_edit');
        $productId = Mage::app()->getRequest()->getParam('id');
        if ($productId && $block)
        {
            $block->setChild('reset_button',
                Mage::getSingleton('core/layout')->createBlock('adminhtml/widget_button')
                    ->setData(array(
                        'label' => Mage::helper('AdvancedStock')->__('ERP View'),
                        'onclick' => 'setLocation(\'' . $block->getUrl('adminhtml/AdvancedStock_Products/Edit', array('product_id' => $productId)) . '\')'
                    ))
            );
        }
    }

    /**
     * Dispatch event to add columns to sales order grid
     *
     * @param Varien_Event_Observer $observer
     */
    public function controller_action_layout_render_before_adminhtml_sales_order_index(Varien_Event_Observer $observer)
    {
        $gridBlock = Mage::getSingleton('core/layout')->getBlock('sales_order.grid');
        Mage::dispatchEvent('advancedstock_sales_order_grid_before_render', array('grid'=>$gridBlock));
        return $this;
    }

    /**
     * Dispatch event to add columns to catalog product grid
     *
     * @param Varien_Event_Observer $observer
     */
    public function controller_action_layout_render_before_adminhtml_catalog_product_index(Varien_Event_Observer $observer)
    {
        $gridBlock = Mage::getSingleton('core/layout')->getBlock('admin.product.grid');

        if (!$gridBlock){
            if(Mage::getSingleton('core/layout')->getBlock('products_list')){
                $gridBlock = Mage::getSingleton('core/layout')->getBlock('products_list')->getChild('grid');
            }
        }

        if ($gridBlock)
            Mage::dispatchEvent('advancedstock_catalog_product_grid_before_render', array('grid'=>$gridBlock));

        return $this;
    }

    /**
     * Add custom columns to sales order grid
     *
     * @param Varien_Event_Observer $observer
     * @return bool
     */
    public function advancedstock_sales_order_grid_before_render(Varien_Event_Observer $observer) {

        $grid = $observer->getEvent()->getgrid();


        $grid->addColumnAfter('payment_validated', array(
            'header'=> Mage::helper('AdvancedStock')->__('Payment<br>validated'),
            'width' => '40px',
            'index' => 'payment_validated',
            'align' => 'center',
            'type' => 'options',
            'options' => array(
                '1' => Mage::helper('purchase')->__('Yes'),
                '0' => Mage::helper('purchase')->__('No'),
            ),
        ),'status');

        return $this;
    }

    /**
     * Add custom columns to catalog product grid
     *
     * @param Varien_Event_Observer $observer
     * @return bool
     */
    public function advancedstock_catalog_product_grid_before_render(Varien_Event_Observer $observer) {

        $grid = $observer->getEvent()->getgrid();

        $grid->addColumnAfter('erp_qty', array(
            'header'=> Mage::helper('AdvancedStock')->__('Stock Summary'),
            'index' => 'entity_id',
            'renderer'	=> 'MDN_AdvancedStock_Block_Product_Widget_Grid_Column_Renderer_StockSummary',
            'filter' => 'MDN_AdvancedStock_Block_Product_Widget_Grid_Column_Filter_StockSummary',
            'sortable'	=> false
        ), 'status');

        $grid->removeColumn('qty');

        return $this;
    }

    /**
     * Delete relative data when a product is deleting in magento
     *
     * @param Varien_Event_Observer $observer
     */
    public function catalog_product_delete_before(Varien_Event_Observer $observer) {
        $product = $observer->getEvent()->getProduct();
        $productId = $product->getId();
        if ($productId > 0) {
            //Delete ERP Product's barcodes
            $pbCollection = mage::getModel('AdvancedStock/ProductBarcode')
                ->getCollection()
                ->addFieldToFilter('ppb_product_id', $productId);
            foreach ($pbCollection as $pb) {
                $pb->delete();
            }

            //Delete Stock Movements
            $smCollection = mage::getModel('AdvancedStock/StockMovement')
                ->getCollection()
                ->addFieldToFilter('sm_product_id', $productId);
            foreach ($smCollection as $sm) {
                $sm->delete();
            }

            //Delete erp_sales_history
            $shCollection = mage::getModel('AdvancedStock/SalesHistory')
                ->getCollection()
                ->addFieldToFilter('sh_product_id', $productId);
            foreach ($shCollection as $sh) {
                $sh->delete();
            }

            //Delete Product-Supplier Associations
            $psCollection = mage::getModel('Purchase/ProductSupplier')
                ->getCollection()
                ->addFieldToFilter('pps_product_id', $productId);
            foreach ($psCollection as $ps) {
                $ps->delete();
            }
        }
    }

    /**
     * Update is valid
     */
    public function sales_order_save_before(Varien_Event_Observer $observer)
    {
        if (Mage::getStoreConfig('healthyerp/erp/enabled') == 1) {
            $order = $observer->getEvent()->getOrder();
            Mage::helper('AdvancedStock/Sales_ValidOrders')->updateIsValid($order);
        }
    }

    /**
     * Update reservation & stock levels after credit memo
     *
     * @param Varien_Event_Observer $observer
     */
    public function sales_order_creditmemo_save_after(Varien_Event_Observer $observer)
    {
        if (Mage::getStoreConfig('healthyerp/erp/enabled') != 1)
            return;

        $creditMemo = $observer->getEvent()->getcreditmemo();
        foreach ($creditMemo->getAllItems() as $item) {

            $productId = $item->getproduct_id();
            if (!$productId)
                return;

            $productStockManagement = Mage::getModel('cataloginventory/stock_item')->loadByProduct($productId);
            if ($productStockManagement->getManageStock()) {

                $orderItem = $item->getOrderItem();
                if ($orderItem) {
                    $erpOrderItem = $orderItem->getErpOrderItem();
                    if ($erpOrderItem) {

                        $oldReservedQty = $orderItem->getreserved_qty();
                        $newReservedQty = $oldReservedQty - $item->getqty();
                        if ($newReservedQty < 0)
                            $newReservedQty = 0;
                        $erpOrderItem->setreserved_qty($newReservedQty)->save();
                        mage::helper('AdvancedStock/Product_Base')->planUpdateStocksWithBackgroundTask($productId, 'from credit memo aftersave');

                        //because if a customer cancel an item non reserved, ERP must re Dispatch the order
                        if ($oldReservedQty == 0 && $newReservedQty == 0) {
                            Mage::dispatchEvent('advancedstock_order_item_reserved_qty_changed', array('order_item' => $orderItem));
                        }
                    }
                }
            }
        }
    }

    /**
     * Various checks when order is saved
     *
     * @param Varien_Event_Observer $observer
     */
    public function sales_order_save_after(Varien_Event_Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();

        //if order just being created, exit
        if (!$order->getOrigData('entity_id'))
            return;

        //if erp is disabled
        if (Mage::getStoreConfig('healthyerp/erp/enabled') != 1)
            return;

        //if order is_valid change, update stock information for products
        if ($order->getis_valid() != $order->getOrigData('is_valid')) {
            foreach ($order->getAllItems() as $item) {
                if (mage::getStoreConfig('advancedstock/valid_orders/do_not_consider_invalid_orders_for_stocks')) {
                    if (!mage::helper('AdvancedStock/Sales_ValidOrders')->orderIsValid($order)) {
                        mage::helper('AdvancedStock/Product_Reservation')->releaseProduct($order, $item);
                    }
                }
                mage::helper('AdvancedStock/Product_Base')->planUpdateStocksWithBackgroundTask($item->getproduct_id(), ' from order validity change ');
            }
        }

        //state change
        if ($order->getstate() != $order->getOrigData('state')) {

            //if order has been canceled, update products stocks and reserved qties
            if ($order->getstate() == Mage_Sales_Model_Order::STATE_CANCELED) {
                foreach ($order->getAllItems() as $item) {
                    mage::helper('AdvancedStock/Product_Reservation')->releaseProduct($order, $item);
                    mage::helper('AdvancedStock/Product_Base')->planUpdateStocksWithBackgroundTask($item->getproduct_id(), 'from order is cancel event');
                    Mage::dispatchEvent('salesorder_just_cancelled', array('order' => $order));
                }
            }

            //case partial cancel order on cancel order after partial shipment
            if ($order->getstate() == Mage_Sales_Model_Order::STATE_COMPLETE) {
                foreach ($order->getAllItems() as $item) {
                    if ($item->getStatusId() == Mage_Sales_Model_Order_Item::STATUS_CANCELED) {
                        mage::helper('AdvancedStock/Product_Reservation')->releaseProduct($order, $item);
                        mage::helper('AdvancedStock/Product_Base')->planUpdateStocksWithBackgroundTask($item->getproduct_id(), 'from order item is cancel event');
                    }
                }
            }

            //to make order modified in Magento to disappear from order preparation on some important state change
            if ($order->getstate() == Mage_Sales_Model_Order::STATE_COMPLETE
                || $order->getstate() == Mage_Sales_Model_Order::STATE_CANCELED
                || $order->getstate() == Mage_Sales_Model_Order::STATE_CLOSED
            ) {

                if (!Mage::getSingleton('AdvancedStock/Sales_Order_Tool')->isBeingDispatched($order)) {
                    mage::helper('BackgroundTask')->AddTask(
                        'Dispatch order #' . $order->getId() . ' (on Order State Changed)',
                        'Orderpreparation',
                        'dispatchOrder',
                        $order->getId()
                    );
                }
            }
        }
    }
    
    

}


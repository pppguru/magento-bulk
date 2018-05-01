<?php

/**
 * 
 *
 */
class MDN_Orderpreparation_Model_OrderToPrepare extends Mage_Core_Model_Abstract {

    const filterStockless = 'stockless';
    const filterIgnored = 'ignored';
    const filterFullstock = 'fullstock';
    const filterSelected = 'selected';

    private $_SelectedOrders = null;
    private $_FullStockOrdersFromCache = null;
    private $_StockLessOrdersFromCache = null;
    private $_IgnoredOrdersFromCache = null;
    private $_SelectedOrdersIds = null;

    protected $_eventPrefix = 'order_preparation';

    public function _construct() {
        parent::_construct();
        $this->_init('Orderpreparation/ordertoprepare');
    }

    /*
     * Return selected order ids (for current preparation warehouse / operator)
     *
     * @return unknown
     */

    public function getSelectedOrdersIds() {

        if ($this->_SelectedOrdersIds == null) {
            $this->_SelectedOrdersIds = array();
            $this->_SelectedOrdersIds[] = 0;
            $preparationWarehouseId = mage::helper('Orderpreparation')->getPreparationWarehouse();
            $operatorId = mage::helper('Orderpreparation')->getOperator();
            $collection = mage::getModel('Orderpreparation/ordertoprepare')->getCollection();
            foreach ($collection as $order) {
                //if order doesn't belong to current warehouse, skip it
                if ($order->getpreparation_warehouse() != $preparationWarehouseId)
                    continue;
                //if order doesn't concern current operator, skip it
                if ($order->getuser() != $operatorId)
                    continue;
                $this->_SelectedOrdersIds[] = $order->getorder_id();
            }
        }

        return $this->_SelectedOrdersIds;
    }

    /**
     * Return selected orders collection
     *
     * @return unknown
     */
    public function getSelectedOrders($forceListSelected = null) {
        if ($this->_SelectedOrders == null) {

            //Load Selected order List
            $list_selected = $this->getSelectedOrdersIds();
            if($forceListSelected){
                $list_selected = $forceListSelected;
            }

            if (mage::helper('AdvancedStock/FlatOrder')->ordersUseEavModel()) {
                $this->_SelectedOrders = Mage::getResourceModel('sales/order_collection')
                                ->addAttributeToSelect('shipping_method')
                                ->addAttributeToSelect('shipping_description')
                                ->addFieldToFilter('entity_id', array('in' => $list_selected))                                
                                ->joinTable(
                                        mage::getModel('Purchase/Constant')->getTablePrefix() . 'order_to_prepare',
                                        'order_id=entity_id',
                                        array(
                                            'order_to_prepare_id' => 'id',
                                            'order_id' => 'order_id',
                                            'real_weight' => 'real_weight',
                                            'ship_mode' => 'ship_mode',
                                            'package_count' => 'package_count',
                                            'custom_values' => 'custom_values',
                                            'details' => 'details',
                                            'preparation_warehouse' => 'preparation_warehouse'
                                        )
                );
              
                $this->addAdditionalAttributes($this->_SelectedOrders);
            } else {                
                $this->_SelectedOrders = Mage::getResourceModel('sales/order_collection')
                                ->addAttributeToSelect('shipping_method')
                                ->addAttributeToSelect('increment_id')
                                ->addAttributeToSelect('entity_id')
                                ->addAttributeToSelect('status')
                                ->addAttributeToSelect('shipping_description')
                                ->addFieldToFilter('main_table.entity_id', array('in' => $list_selected))   
                                ->join('Orderpreparation/ordertoprepare', 'order_id=main_table.entity_id');

                $this->addAdditionalAttributes($this->_SelectedOrders);
            }
        }

        //add filter regarding preparation warehouse and user
        $preparationWarehouseId = mage::helper('Orderpreparation')->getPreparationWarehouse();
        $operatorId = mage::helper('Orderpreparation')->getOperator();
        $this->_SelectedOrders->addFieldToFilter('preparation_warehouse', $preparationWarehouseId);
        $this->_SelectedOrders->addFieldToFilter('user', $operatorId);

        return $this->_SelectedOrders;
    }

    /**
     * Optimization to reduce memory and performance impact on order prepration screen display
    */
    public function countOrders($type,$preparationWarehouseId = 0, $operatorId = 0){

        $count = 0;


        if(!$preparationWarehouseId)
            $preparationWarehouseId = mage::helper('Orderpreparation')->getPreparationWarehouse();
        
        if(!$operatorId)
            $operatorId = mage::helper('Orderpreparation')->getOperator();

        if($preparationWarehouseId >0 && $operatorId >0){

            $prefix = Mage::getConfig()->getTablePrefix();

            //case Selected Order
            $table = 'order_to_prepare';
            $id = 'id';
            $filter = '';
            $fieldWarehouse = 'preparation_warehouse';
            $filter2 = ' AND user = ' . $operatorId;

            //case FullStock, Ignored and Stockless orders
            if($type != 'selected'){
                $table = 'order_to_prepare_pending';
                $id = 'opp_order_id';
                $filter = ' AND opp_type = "' . $type.'" ';
                $fieldWarehouse = 'opp_preparation_warehouse';
                $filter2 = '';
            }

            $sql = 'SELECT count('.$id.') from ' . $prefix . $table;
            $sql .=' WHERE '.$fieldWarehouse.' = ' . $preparationWarehouseId;
            $sql .= $filter;
            $sql .= $filter2;
          
            
            $count = mage::getResourceModel('catalog/product')->getReadConnection()->fetchOne($sql);
        }

        return $count;
        
        
    }
    /**
     * Add additional attributeToSelect for selected orders collection
     *
     * @param unknown_type $collection
     */
    protected function addAdditionalAttributes(&$collection) {
        $attributes = Mage::getConfig()->getNode('backend/selectedorders/collection/attributes');
        if ($attributes) {
            $attributes = $attributes->asArray();
            $attributes = array_keys($attributes);

            $collection->addAttributeToSelect($attributes);
        }
    }

    /**
     * Return full stock order collection (for current warehouse)
     *
     */
    public function getFullStockOrdersFromCache() {
        if ($this->_FullStockOrdersFromCache == null) {
            $preparationWarehouseId = mage::helper('Orderpreparation')->getPreparationWarehouse();
            $this->_FullStockOrdersFromCache = mage::getModel('Orderpreparation/ordertopreparepending')
                            ->getCollection()
                            ->addFieldToFilter('opp_type', self::filterFullstock)
                            ->addFieldToFilter('opp_preparation_warehouse', $preparationWarehouseId);
        }
        return $this->_FullStockOrdersFromCache;
    }

    /**
     * Return ignored orders
     *
     */
    public function getIgnoredOrdersFromCache() {
        if ($this->_IgnoredOrdersFromCache == null) {
            $preparationWarehouseId = mage::helper('Orderpreparation')->getPreparationWarehouse();
            $this->_IgnoredOrdersFromCache = mage::getModel('Orderpreparation/ordertopreparepending')
                            ->getCollection()
                            ->addFieldToFilter('opp_type', self::filterIgnored)
                            ->addFieldToFilter('opp_preparation_warehouse', $preparationWarehouseId);
        }
        return $this->_IgnoredOrdersFromCache;
    }

    /**
     * Return stockless orders (for current warehouse)
     *
     */
    public function getStockLessOrdersFromCache() {
        if ($this->_StockLessOrdersFromCache == null) {
            $preparationWarehouseId = mage::helper('Orderpreparation')->getPreparationWarehouse();
            $this->_StockLessOrdersFromCache = mage::getModel('Orderpreparation/ordertopreparepending')
                            ->getCollection()
                            ->addFieldToFilter('opp_type', self::filterStockless)
                            ->addFieldToFilter('opp_preparation_warehouse', $preparationWarehouseId);
        }
        return $this->_StockLessOrdersFromCache;
    }

    /**
     * Add order to selected orders
     *
     * @param unknown_type $order
     */
    public function AddSelectedOrder($orderId, $check = true) {

        $logs = 'Add order "'.$orderId.'" to selected orders : ';
        
        //check
        if (!$this->CanAddOrder($orderId))
        {
            $logs .= ' can not add order !';
            mage::log($logs, null, 'erp_add_order_to_selected.log');
            return false;
        }

        //add order
        $order = Mage::getModel('sales/order')->load($orderId);
        
        $OrderItem = Mage::getModel('Orderpreparation/ordertoprepare')
                        ->setorder_id($orderId)
                        ->setuser(Mage::helper('Orderpreparation')->getOperator());

        //Add order details
        $OrderItem->setshipping_method($order->getshipping_method());

        //get Customer Name to be ship (if no shipping adress, get Billing name as Possible
        $ShipToName = '';
        $orderShippingAddress = $order->getShippingAddress();
        if ($orderShippingAddress != null){
            $ShipToName = $orderShippingAddress->getName();
        }else{
            $orderBillingAddress = $order->getBillingAddress();
            if ($orderBillingAddress != null){
                $ShipToName = $orderBillingAddress->getName();
            }
        }
        $OrderItem->setshipping_address($ShipToName);

        //set preparation warehouse
        $warehouseId = mage::helper('Orderpreparation')->getPreparationWarehouse();
        $OrderItem->setpreparation_warehouse($warehouseId);

        //Check for invoice
        if (!Mage::getStoreConfig('orderpreparation/create_shipment_and_invoices_options/invoice_only_shipped_items'))
        {
            $invoices = $order->getInvoiceCollection();
            if (sizeof($invoices) > 0) {
                foreach ($invoices as $invoice) {
                    $OrderItem->setinvoice_id($invoice->getincrement_id());
                    $logs .= ' invoice id =  '.$invoice->getincrement_id();
                }
            }
        }

        //add products        
        $addedProducts = array();
        $pickingListHelper = mage::helper('Orderpreparation/PickingList');
        foreach ($order->getItemsCollection() as $item) {

            $logs .= "\n".' add product '.$item->getName().' : ';
            
            //if product doesn't belong to the current warehouse, skip it
            if ($item->getpreparation_warehouse() != $warehouseId)
            {
                $logs .= ' wrong warehouse';
                continue;
            }

            $remaining_qty = ($item->getqty_ordered() - $item->getRealShippedQty());
            $logs .= ' remaining qty is '.$remaining_qty;
            if ($remaining_qty > 0) {
                $productid = $item->getproduct_id();
                //$product = mage::getModel('catalog/product')->load($productid);//was not used

                //if product manages stocks, 
                if (Mage::getModel('cataloginventory/stock_item')->loadByProduct($productid)->getManageStock()) {
                    $logs .= ', product manages stock ';
                    $logs .= ', reserved qty is '.$item->getreserved_qty();
                    $remaining_qty = $item->getreserved_qty();
                            /*
                    if ($item->getreserved_qty() == 0) {
                        $productStock = mage::helper('AdvancedStock/Product_Base')->getAvailableQty($product->getId(), $websiteId);
                        $logs .= ', product stock is '.$productStock;
                        if ($remaining_qty > $productStock)
                            $remaining_qty = $productStock;
                    }
                    else {
                        if ($item->getreserved_qty() < $remaining_qty)
                            $remaining_qty = $item->getreserved_qty();
                    }
                             * 
                             */
                }

                //if product is configurable, check that sub item is available
                if ($item->getproduct_type() == 'configurable') {
                    foreach ($order->getItemsCollection() as $subItem) {
                        if ($subItem->getparent_item_id() == $item->getId()) {
                            //$subProductRemainingQty = ($subItem->getqty_ordered() - $subItem->getRealShippedQty());//was not used
                            if ($subItem->getreserved_qty() == 0)
                                $remaining_qty = 0;
                            else
                                $remaining_qty = $subItem->getreserved_qty();
                        }
                    }
                }

                $logs .= ', remaining qty (2) is '.$remaining_qty;
                
                //add product
                if ($remaining_qty > 0) {
                    $SubItem = Mage::getModel('Orderpreparation/ordertoprepareitem')
                                    ->setorder_id($orderId)
                                    ->setproduct_id($productid)
                                    ->setqty($remaining_qty)
                                    ->setqty_custom(($item->isShipSeparately() ? 1 : 0))
                                    ->setorder_item_id($item->getId())
                                    ->setdisplay_in_picking_list($pickingListHelper->isItemDisplayedInPickingList($item, $order))
                                    ->setpreparation_warehouse($warehouseId)
                                    ->setuser(Mage::helper('Orderpreparation')->getOperator())
                                    ->save();

                    //Fill added product array (to compute weight)
                    $addedProducts[] = array('product_id' => $productid, 'qty' => $remaining_qty);
                }
            }
        }


        //Compute order weight
        $model = mage::getModel('Orderpreparation/OrderWeightCalculation');
        $weight = $model->calculateOrderWeight($addedProducts);
        $OrderItem->setreal_weight($weight);

        Mage::dispatchEvent('order_preparation_added_to_selected', array('order_item' => $OrderItem, 'order' => $order));

        //store payment validated and shipping method
        $shippingMethod = $order->getshipping_description();
        $OrderItem->setcarrier($shippingMethod);
        $OrderItem->save();

        //remove order from cache
        mage::helper('Orderpreparation/Dispatcher')->removeOrderFromOrderToPreparePending($order, $warehouseId);

        mage::log($logs, null, 'erp_add_order_to_selected.log');

        return true;
    }

    /**
     * Check if we can add this order to order preparation
     *
     * @param unknown_type $orderId
     */
    public function CanAddOrder($orderId) {
        $debug = '';

        //prevent duplicate by checking if the order is already selected for this this warehouse
        $alreadyAdded = mage::getModel('Orderpreparation/ordertoprepare')
                ->getCollection()
                ->addFieldToFilter('order_id', $orderId)
                ->addFieldToFilter('preparation_warehouse', mage::helper('Orderpreparation')->getPreparationWarehouse())
                ->getFirstItem();

        if($alreadyAdded && $alreadyAdded->getId()){
             return false;
        }


        $NbAddedProducts = 0;

        $order = Mage::getModel('sales/order')->load($orderId);

        //prevent order deleted added to selection
        if(!$order->getId()){
            return false;
        }

        foreach ($order->getItemsCollection() as $item) {
            $productId = $item->getproduct_id();
            $debug .= '<br>Product ' . $productId;

            if (!Mage::helper('AdvancedStock/Product_Base')->productExists($productId))
            {
                $debug .= '<br/>--> Skipped because product does not exist';
                continue;
            }

            //si il reste une qte de ce produit � livrer et qu'il gere les stocks
            $remaining_qty = ($item->getqty_ordered() - $item->getRealShippedQty());
            $debug .= ' - remaining_qty ' . $remaining_qty;
            $ManageStock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($productId)->getManageStock();
            if ($ManageStock) {
                if (($remaining_qty > 0)) {

                    //if product reserved
                    if ($item->getreserved_qty() > 0) {
                        $NbAddedProducts += 1;
                    }
                }
            } else {
                if (($item->getProductType() != 'configurable') && ($item->getProductType() != 'bundle') && ($item->getProductType() != 'grouped'))
                    $NbAddedProducts += 1;
            }
        }

        if ($NbAddedProducts == 0)
            return false;
        
        return true;
    }

    /**
     * Remove order from selected orders list
     *
     * @param unknown_type $order
     */
    public function RemoveSelectedOrder($orderId, $raiseEvent = true) {

        if($orderId > 0){
            //get order
            $orderToPrepare = mage::helper('Orderpreparation')->getOrderToPrepareForCurrentContext($orderId);
            $orderToPrepare->delete();

            //dispatch event
            if ($raiseEvent)
                Mage::dispatchEvent('orderpreparartion_remove_selected_order', array('order_id' => $orderId));

            //Dispatch order
            $order = mage::getModel('sales/order')->load($orderId);
            $this->DispatchOrder($order);
        }
    }

    /**
     * Delete order to prepare items
     *
     */
    protected function _afterDelete() {
        $preparationWarehouseId = mage::helper('Orderpreparation')->getPreparationWarehouse();
        $operatorId = mage::helper('Orderpreparation')->getOperator();

        //supprime les lignes 'item'
        $collection = Mage::getModel('Orderpreparation/ordertoprepareitem')
                        ->getCollection()
                        ->addFieldToFilter('preparation_warehouse', $preparationWarehouseId)
                        ->addFieldToFilter('order_id', $this->getorder_id())
                        ->addFieldToFilter('user', $operatorId);

        foreach ($collection as $item) {
            $item->delete();
        }
    }

    /*
     * Retourne la commande associee a cet order_to_prepare
     *
     */

    public function GetOrder() {
        $order = Mage::getModel('sales/order')->load($this->getorder_id());
        return $order;
    }

    /*
     * Retourne la qte totale ajoutee dans la preparation de produit pour un produit donne
     *
     * @param unknown_type $ProductId
     */

    public function GetTotalAddedQtyForProduct($ProductId) {
        $collection = Mage::getModel('Orderpreparation/ordertoprepareitem')
                        ->getCollection()
                        ->addFieldToFilter('product_id', $ProductId);

        $retour = 0;
        foreach ($collection as $item) {
            $retour += $item->getqty();
        }

        return $retour;
    }

    /*
     * Retourne la qte totale ajoutee dans la preparation de produit pour un produit donne pour une commande selectionnee
     *
     * @param unknown_type $ProductId
     */
    public function GetTotalAddedQtyForProductForSelectedOrder($ProductId,$OrderId,$preparationWarehouseId,$operatorId) {
        $collection = Mage::getModel('Orderpreparation/ordertoprepareitem')
                        ->getCollection()
                        ->addFieldToFilter('product_id', $ProductId)
                        ->addFieldToFilter('order_id', $OrderId)
                        ->addFieldToFilter('preparation_warehouse', $preparationWarehouseId)
                        ->addFieldToFilter('user', $operatorId);

        $retour = 0;
        foreach ($collection as $item) {
            $retour += $item->getqty();
        }

        return $retour;
    }

    /**
     * Return items to ship for one order considering current user / warehouse
     *
     * @param unknown_type $OrderId
     */
    public function GetItemsToShip($OrderId) {
        $preparationWarehouseId = mage::helper('Orderpreparation')->getPreparationWarehouse();
        $operatorId = mage::helper('Orderpreparation')->getOperator();
        $collection = Mage::getModel('Orderpreparation/ordertoprepareitem')
                        ->getCollection()
                        ->addFieldToFilter('order_id', $OrderId)
                        ->addFieldToFilter('preparation_warehouse', $preparationWarehouseId)
                        ->addFieldToFilter('user', $operatorId)
                        ->setOrder('order_item_id', 'asc');

        return $collection;
    }

    /**
     * Create shipments for selected orders
     * return created shipments count
     */
    public function CreateShipments() {

        $preparationWarehouseId = mage::helper('Orderpreparation')->getPreparationWarehouse();
        $operatorId = mage::helper('Orderpreparation')->getOperator();

        //parcourt les commandes
        $orders = $this->getSelectedOrders();
        $createdShipmentCount = 0;
        $shipmentHelper = Mage::helper('Orderpreparation/Shipment');
        foreach ($orders as $order) {
            if (!$shipmentHelper->ShipmentCreatedForOrder($order->getid())) {
                $shipmentHelper->CreateShipment($order, $preparationWarehouseId, $operatorId);
            }
        }

        return $createdShipmentCount;
    }

    /**
     * Cree les Factures pour les commandes selectionnees
     * Seulement si la commande a ete totalement expediees
     * et retourne le nombre de factures creees
     */
    public function CreateInvoices() {
        //parcourt les commandes
        $orders = $this->getSelectedOrders();
        $createdInvoicesCount = 0;
        $invoiceHelper = Mage::helper('Orderpreparation/Shipment');
        foreach ($orders as $order) {
            if (!$invoiceHelper->InvoiceCreatedForOrder($order->getid())) {
                if (Mage::getSingleton('AdvancedStock/Sales_Order_Tool')->IsCompletelyShipped($order)) {
                    $invoiceHelper->CreateInvoice($order);
                }
            }
        }

        return $createdInvoicesCount;
    }

    /**
     * Retourne la liste des shipments (en rajoutant les poids r�els saisis)
     *
     */
    public function GetShipments($carrier, $carrier2 = null) {
        //met dans un array les shipment
        $shipments = array();
        $OrderToPrepare = $this->getCollection()->setOrder('order_id', 'asc');
        foreach ($OrderToPrepare as $item) {
            //recupere le shipment
            $obj = Mage::getModel('sales/order_shipment')->loadByIncrementId($item->getshipment_id());
            $order = $obj->getOrder();
            $t = explode('_', strtolower($order->getshipping_method()));
            $realShippingMethod = $t[0];
            if (($realShippingMethod == $carrier) || ($realShippingMethod == $carrier2)) {
                //rajoute le poids reel
                $obj->setreal_weight($item->getreal_weight());
                $obj->setship_mode($item->getship_mode());
                $obj->setpackage_count($item->getpackage_count());
                //le rajoute a la liste
                $shipments[] = $obj;
            }
        }

        return $shipments;
    }

    /**
     * Plan customer notification
     *
     */
    public function NotifyCustomers() {

        $bgTaskPriority = 5;

        //pour chaque envoi
        foreach ($this->getSelectedOrders() as $item) {
            try {

                //Shipment notification
                if (Mage::getStoreConfig('orderpreparation/notify_step/notify_shipment')) {
                  if ($item->getshipment_id()) {
                      $shipment = Mage::getModel('sales/order_shipment')->loadByIncrementId($item->getshipment_id());
                      if (!$shipment->getEmailSent()) {
                          mage::helper('BackgroundTask')->AddTask('Notify Shipment #' . $shipment->getId(),
                                  'Orderpreparation',
                                  'notifyShipment',
                                  $shipment->getId(),
                                  null,
                                  false,
                                  $bgTaskPriority
                          );
                      }
                  }
                }

                //invoice notification
                if (Mage::getStoreConfig('orderpreparation/notify_step/notify_invoice')) {
                  if ($item->getinvoice_id()) {
                      $invoice = Mage::getModel('sales/order_invoice')->loadByIncrementId($item->getinvoice_id());
                      if (!$invoice->getEmailSent()) {
                          mage::helper('BackgroundTask')->AddTask('Notify Invoice #' . $invoice->getId(),
                                  'Orderpreparation',
                                  'notifyInvoice',
                                  $invoice->getId(),
                                  null,
                                  false,
                                  $bgTaskPriority
                          );
                      }
                  }
                }

            } catch (Exception $ex) {

                $message = 'Error while notifying for order ' . $item->getorder_id();
                if ($item->getinvoice_id()) {
                  $message.= '(invoice id: ' . $item->getinvoice_id() . ') ';
                }
                if ($item->getshipment_id()) {
                  $message.= '(Shipment id: ' . $item->getshipment_id() . ') ';
                }
                $message.= ' : ' . $ex->getMessage();

                Mage::getSingleton('adminhtml/session')->addError($message);
            }
        }
    }

    /**
     * Remove all order from selected orders
     * Note : remove orders for current warehouse / user
     *
     */
    public function Finish() {

        $preparationWarehouseId = mage::helper('Orderpreparation')->getPreparationWarehouse();
        $operatorId = mage::helper('Orderpreparation')->getOperator();

        if($preparationWarehouseId >0 && $operatorId>0){

            $collection = $this->getCollection()
                               ->addFieldToFilter('preparation_warehouse', $preparationWarehouseId)
                               ->addFieldToFilter('user', $operatorId);

            foreach ($collection as $item) {

                $this->RemoveSelectedOrder($item->getorder_id(), false);

                //dispatch order if invoice not created
                if ($item->getinvoice_id() == '') {
                    $order = mage::getModel('sales/order')->load($item->getorder_id());
                    $this->DispatchOrder($order);
                }
            }
        }
    }

    /**
     * Return order details
     *
     * @param unknown_type $order
     */
    public function getDetailsForOrder($order, $ShowInvoiceShipment = true) {

        $retour = '';
        $retour .= '' . mage::helper('Orderpreparation')->__('Total') . ': ' . number_format($order->getgrand_total(), 2);
        $retour .= "<br>Date: " . mage::helper('core')->formatDate($order->getcreated_at(), 'long');

        //payment status
        $retour .= "<br>" . mage::helper('Orderpreparation')->__('Payment') . ": ";
        if ($order->getpayment_validated() == 1)
            $retour .= '<font color="green">' . mage::helper('Orderpreparation')->__('Yes') . '</font>';
        else
            $retour .= '<font color="red">' . mage::helper('Orderpreparation')->__('No') . '</font>';

        $retour .= "<br>" . mage::helper('Orderpreparation')->__('Status') . ": " . $order->getstatus();
        $retour .= "<br>" . mage::helper('Orderpreparation')->__('Carrier') . ": " . $order->getshipping_description();


        try {
            $payment = $order->getPayment();
            if($payment != null && $payment->getMethodInstance()){
               $retour .= "<br>" . mage::helper('Orderpreparation')->__('Payment') . ": " . $payment->getMethodInstance()->gettitle();
            }
        } catch (Exception $ex) {
            //payment method cant be found
            $retour .= '<br>Unable to load payment method';
        }

        if ($ShowInvoiceShipment) {
            $OrderToPrepare = mage::getModel('Orderpreparation/ordertoprepare')->load($order->getId(), 'order_id');
            if (($OrderToPrepare)) {
                $invoice = Mage::getModel('sales/order_invoice')->loadByIncrementId($OrderToPrepare->getinvoice_id());
                $shipment = Mage::getModel('sales/order_shipment')->loadByIncrementId($OrderToPrepare->getshipment_id());
                if ($OrderToPrepare->getinvoice_id() != '')
                    $retour .= '<br>' . mage::helper('Orderpreparation')->__('Invoice') . ': <a target="_new" href="' . Mage::helper('adminhtml')->getUrl('adminhtml/sales_order_invoice/print', array('invoice_id' => $invoice->getId())) . '"> ' . $OrderToPrepare->getinvoice_id() . '</a>';
                if ($OrderToPrepare->getshipment_id())
                    $retour .= '<br>' . mage::helper('Orderpreparation')->__('Shipment') . ': <a target="_new" href="' . Mage::helper('adminhtml')->getUrl('adminhtml/sales_order_shipment/print', array('invoice_id' => $shipment->getId())) . '"> ' . $OrderToPrepare->getshipment_id() . '</a>';
            }

            //tracking numbers
            $tracking_txt = '';
            foreach ($order->getTracksCollection() as $track) {

                $obj = $track->getNumberDetail();
                if (is_object($obj))
                    $tracking_txt .= $track->getNumberDetail()->gettracking() . '<br>';
                else {
                    if (is_array($obj))
                        $tracking_txt .= $obj["number"] . '<br>';
                    else
                        $tracking_txt .= $obj . '<br>';
                }
            }
            if ($tracking_txt != '') {
                $retour .= "<br>Tracking: " . $tracking_txt;
            }
        }
        return $retour;
    }

    /**
     * Dispatch order in fullstock or stockless tabs
     *
     */
    public function DispatchOrder($order) {
        //moved to dispatcher helper
        mage::helper('Orderpreparation/Dispatcher')->DispatchOrder($order);
    }

    /**
     * Return an array with pending orders ids
     *
     */
    public function getPendingOrdersIds() {
      
        $orderIdList = array();

        $collection = Mage::getResourceModel('sales/order_collection')
                        ->addAttributeToFilter('state', array('nin' => array('canceled', 'complete', 'closed')))
                        ->addFieldToFilter('entity_id', array('nin' => $this->getSelectedOrdersIds()))   //on ne prend pas en compte les commandes d�ja s�lectionn�es
                        ->addAttributeToSort('increment_id', 'asc');

        //browse collection differently depending of magento version to avoid crash and to fast up process
        if(mage::helper('AdvancedStock/MagentoVersionCompatibility')->useGetAllIdsOnSaleOrderModelCollection()){
          $orderIdList = $collection->getAllIds();
        }else{
          foreach ($collection as $order) {
              $orderIdList[] = $order->getId();
          }
        }

        return $orderIdList;
    }

    /**
     * Return items
     *
     * @return unknown
     */
    public function getItems() {
        $collection = mage::getModel('Orderpreparation/ordertoprepareitem')
                        ->getCollection()
                        ->addFieldToFilter('order_id', $this->getorder_id());
        return $collection;
    }

    /**
     * Link product serials to shipment
     *
     */
    public function linkSerialsToShipment() {
        //remove association between serials & shipment & order id
        $shipment = mage::getModel('sales/order_shipment')->loadByIncrementId($this->getshipment_id());
        if ($shipment->getId()) {
            mage::helper('purchase/ProductSerials')->unlinkSerialsToShipment($shipment);
            $salesOrder = mage::getModel('sales/order')->load($this->getorder_id());

            //create new associations
            foreach ($this->getItems() as $orderToPrepareItem) {
                if ($orderToPrepareItem->getserials() != '')
                    mage::helper('purchase/ProductSerials')->linkSalesOrderToSerial($salesOrder, $orderToPrepareItem->getserials(), $orderToPrepareItem->getproduct_id(), $shipment->getId());
            }
        }
    }
    
    /**
     * return shipment associated to this order 
     */
    public function getShipment()
    {
        $shipmentIncrementId = $this->getshipment_id();
        $shipment = Mage::getModel('sales/order_shipment')->load($shipmentIncrementId, 'increment_id');

        if ($shipment->getId())
            return $shipment;
        else
            return null;
    }

    public function getCustomValueFromKey($key)
    {
        $values = $this->getcustom_values();
        $rows = explode(';', $values);
        foreach ($rows as $row) {
            if($row){
                $fields = explode('=', $row);
                if (count($fields) == 2){
                    if ($fields[0] == $key)
                        return $fields[1];
                }
            }
        }

        return false;
    }

}
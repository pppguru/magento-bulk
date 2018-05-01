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
class MDN_Purchase_Model_Order extends Mage_Core_Model_Abstract {

    const roundingDecimalCount = 2;

    private $_products = null;
    private $_currency = null;
    private $_supplier = null;
    private $_weight = null;
    private $_statusChangeAddedToHistory = false;

    //Purchase order statuses
    const STATUS_NEW = 'new';
    const STATUS_INQUIRY = 'inquiry';
    const STATUS_WAITING_FOR_SUPPLIER = 'waiting_for_supplier';
    const STATUS_WAITING_FOR_PAYMENT = 'waiting_for_payment';
    const STATUS_WAITING_FOR_DELIVERY = 'waiting_for_delivery';
    const STATUS_COMPLETE = 'complete';

    public function _construct() {
        parent::_construct();
        $this->_init('Purchase/Order');
    }

    //*******************************************************************************************************************************************
    //*******************************************************************************************************************************************
    // Related objects / collection
    //*******************************************************************************************************************************************
    //*******************************************************************************************************************************************

    /**
     * Return supplier
     *
     */
    public function getSupplier() {
        if ($this->_supplier == null) {
            $this->_supplier = mage::getModel('Purchase/Supplier')->load($this->getpo_sup_num());
        }
        return $this->_supplier;
    }

    /**
     *
     *
     */
    public function getStockMovements() {
        $collection = mage::getModel('AdvancedStock/StockMovement')
                        ->getCollection()
                        ->addFieldToFilter('sm_po_num', $this->getId());

        return $collection;
    }

    /**
     * return statuses
     *
     */
    public function getStatuses() {
        $retour = array();
        $retour[MDN_Purchase_Model_Order::STATUS_NEW] = mage::helper('purchase')->__('New');
        $retour[MDN_Purchase_Model_Order::STATUS_INQUIRY] = mage::helper('purchase')->__('Inquiry');
        $retour[MDN_Purchase_Model_Order::STATUS_WAITING_FOR_PAYMENT] = mage::helper('purchase')->__('Waiting for payment');
        $retour[MDN_Purchase_Model_Order::STATUS_WAITING_FOR_SUPPLIER] = mage::helper('purchase')->__('Waiting for supplier');
        $retour[MDN_Purchase_Model_Order::STATUS_WAITING_FOR_DELIVERY] = mage::helper('purchase')->__('Waiting for delivery');
        $retour[MDN_Purchase_Model_Order::STATUS_COMPLETE] = mage::helper('purchase')->__('Complete');
        return $retour;
    }

    /**
     * Return products
     *
     */
    public function getProducts() {
        if ($this->_products == null) {
            //load product collection (and add small_image and product_price attributes)
            $this->_products = mage::getModel('Purchase/OrderProduct')
                            ->getCollection()
                            ->addFieldToFilter('pop_order_num', $this->getId())
                            ->join('catalog/product',
                                    'entity_id=pop_product_id')
                            ->join('Purchase/CatalogProductDecimal',
                                    '`catalog/product`.entity_id=`Purchase/CatalogProductDecimal`.entity_id and `Purchase/CatalogProductDecimal`.store_id = 0 and `Purchase/CatalogProductDecimal`.attribute_id = ' . mage::getModel('Purchase/Constant')->GetProductPriceAttributeId(),
                                    array('sale_price' => 'value'));

            //add small image
            $smallImageTableName = mage::getModel('Purchase/Constant')->getTablePrefix() . 'catalog_product_entity_varchar';
            $this->_products->getSelect()->joinLeft($smallImageTableName,
                    '`catalog/product`.entity_id=`' . $smallImageTableName . '`.entity_id and `' . $smallImageTableName . '`.store_id = 0 and `' . $smallImageTableName . '`.attribute_id = ' . mage::getModel('Purchase/Constant')->GetProductSmallImageAttributeId(),
                    array('small_image' => 'value'));
        }
        return $this->_products;
    }

    /**
     * Reset internal collection
     *
     */
    public function resetProducts() {
        $this->_products = null;
    }

    //*******************************************************************************************************************************************
    //*******************************************************************************************************************************************
    // Price functions
    //*******************************************************************************************************************************************
    //*******************************************************************************************************************************************

    /**
     * Return total excluding taxes
     *
     */
    public function getTotalHt() {
        $total = 0;

        foreach ($this->getProducts() as $item) {
            $item->setPurchaseOrder($this);
            $total += $item->getRowTotal();
        }

        //add shipping costs
        $total += $this->getShippingAmountHt();

        //add douane/custom/zoll costs
        $total += $this->getZollAmountHt();

        return round($total, self::roundingDecimalCount);
    }

    /**
     * Return total including taxes
     *
     */
    public function getTotalTtc() {
        $total = 0;

        foreach ($this->getProducts() as $item) {
            $item->setPurchaseOrder($this);
            $total += $item->getRowTotalWithTaxes();
        }

        //add shipping costs
        $total += $this->getShippingAmountTtc();

        //add douane/custom/zoll costs
        $total += $this->getZollAmountTtc();

        return round($total, self::roundingDecimalCount);
    }

    /**
     * Return total for a product with base currency
     *
     */
    public function getProductTotalBase() {
        $orderAmount = 0;

        foreach ($this->getProducts() as $item) {
            $item->setPurchaseOrder($this);
            $orderAmount += $item->getRowTotal_base();
        }

        return round($orderAmount, self::roundingDecimalCount);
    }

     /**
     * Return total for a product with PO currency
     *
     */
    public function getProductTotal() {
        $orderAmount = 0;

        foreach ($this->getProducts() as $item) {
            $item->setPurchaseOrder($this);
            $orderAmount += $item->getRowTotal();
        }

        return round($orderAmount, self::roundingDecimalCount);
    }    


    
    /**
     *
     *
     */
    public function getTaxAmount() {
        return $this->getTotalTtc() - $this->getTotalHt();
    }

    /**
     *
     *
     */
    public function getShippingAmountHt() {
        return $this->getpo_shipping_cost();
    }

    /**
     *
     *
     */
    public function getShippingAmountTtc() {
        $value = $this->getpo_shipping_cost() * (1 + $this->getpo_tax_rate() / 100);
        return round($value, self::roundingDecimalCount);
    }

    /**
     *
     *
     */
    public function getZollAmountHt() {
        return $this->getpo_zoll_cost();
    }

    /**
     *
     *
     */
    public function getZollAmountTtc() {
        $value = $this->getpo_zoll_cost() * (1 + $this->getpo_tax_rate() / 100);
        return round($value, self::roundingDecimalCount);
    }

    /**
     *
     *
     */
    public function getCurrency() {
        if ($this->_currency == null) {
            $this->_currency = mage::getModel('directory/currency')->load($this->getpo_currency());
        }
        return $this->_currency;
    }

    /**
     *
     *
     */
    public function getEuroCurrency() {
        return mage::getModel('directory/currency')->load('EUR');
    }

    /**
     * Return order weight
     */
    public function getWeight() {
        if ($this->_weight == null) {
            $this->_weight = 0;
            foreach ($this->getProducts() as $item) {
                $this->_weight += $item->getRowWeight();
            }
        }
        return $this->_weight;
    }

    //*******************************************************************************************************************************************
    //*******************************************************************************************************************************************
    // Function to update related datas
    //*******************************************************************************************************************************************
    //*******************************************************************************************************************************************

    /**
     *
     *
     */
    public function updateProductSupplierAssociation($productId = null) {
        //parcourt les produits
        foreach ($this->getProducts() as $item) {
            //if a product id is set and current product doesn't match, continue
            if (($productId != null) && ($productId != $item->getpop_product_id()))
                continue;

            //Verifie si l'association existe d�ja
            $ProductSupplier = null;
            $Collection = mage::getModel('Purchase/ProductSupplier')
                            ->getCollection()
                            ->addFieldToFilter('pps_product_id', $item->getpop_product_id())
                            ->addFieldToFilter('pps_supplier_num', $this->getpo_sup_num());

            //Si existe pas
            if (sizeof($Collection) == 0) {
                $ProductSupplier = mage::getModel('Purchase/ProductSupplier');
                $ProductSupplier->setpps_product_id($item->getpop_product_id());
                $ProductSupplier->setpps_supplier_num($this->getpo_sup_num());
            } else {
                //Si existe on recupere
                foreach ($Collection as $item2) {
                    $ProductSupplier = $item2;
                    break;
                }
            }

            //met a jour (si date est inf�rieure a la date de notre commande)
            if ((strtotime($this->getpo_date()) >= strtotime($ProductSupplier->getpps_last_order_date())) || ($ProductSupplier->getpps_last_order_date() == null)) {
                if ($item->getpop_supplier_ref() != '')
                    $ProductSupplier->setpps_reference($item->getpop_supplier_ref());
                $ProductSupplier->setpps_last_order_date($this->getpo_date());
                
                $ProductSupplier->setpps_last_price($item->getUnitPriceWithExtendedCosts_base());
                $ProductSupplier->setpps_last_unit_price($item->getpop_price_ht_base());
                $ProductSupplier->setpps_last_unit_price_supplier_currency($item->getpop_price_ht());

                //save
                $ProductSupplier->save();
            }
        }
    }

    /**
     * Calculate landing cost depending of the method choosen in configuration (purchase/purchase_order/cost_repartition_method)
     */
    public function dispatchExtendedCosts() {

       //Dispatch extended costs
       $TotalExtendedCosts = $this->getpo_shipping_cost() + $this->getpo_zoll_cost();
       $TotalExtendedCosts_base = $this->getpo_shipping_cost_base() + $this->getpo_zoll_cost_base();

       if($TotalExtendedCosts >0 && $TotalExtendedCosts_base >0){

          //calculate amounts
          $TotalProductHt = 0;
          $TotalProductHt_base = 0;
          $TotalQty = 0;
          $TotalWeight = 0;
          foreach ($this->getProducts() as $item) {
              $TotalProductHt += $item->getRowTotal();
              $TotalProductHt_base += $item->getRowTotal_base();              
              $TotalQty += $item->getpop_qty();
              $TotalWeight += ($item->getpop_weight() * $item->getpop_qty());
          }

          if ($TotalProductHt == 0)
            return;

          $RepartitionMode = Mage::getStoreConfig('purchase/purchase_order/cost_repartition_method');

          foreach ($this->getProducts() as $item) {
            switch ($RepartitionMode) {
                case 'by_qty':
                    if($TotalQty >0){
                        $item->setpop_extended_costs($TotalExtendedCosts / $TotalQty);
                        $item->setpop_extended_costs_base($TotalExtendedCosts_base / $TotalQty);
                        $item->save();
                    }
                    break;
                case 'by_amount':
                    $item->setpop_extended_costs($TotalExtendedCosts / $TotalProductHt * ($item->getpop_price_ht() + $item->getpop_eco_tax()));
                    $item->setpop_extended_costs_base($TotalExtendedCosts_base / $TotalProductHt_base * ($item->getpop_price_ht_base() + $item->getpop_eco_tax_base()));
                    $item->save();
                    break;
                case 'by_weight': 
                    if($TotalWeight >0){
                      $productWeight = $item->getpop_weight();
                      if(!empty($productWeight) && $productWeight >0){
                        $weightRatio = $TotalWeight/$productWeight;                        
                        $item->setpop_extended_costs($TotalExtendedCosts / $weightRatio);
                        $item->setpop_extended_costs_base($TotalExtendedCosts_base / $weightRatio);
                        $item->save();
                      }
                    }
                    break;
                default:
                    throw new Exception('Repartition Cost Method not set');
              }
           }
        }      
   }
        
    

    /**
     *
     *
     */
    public function UpdateProductsDeliveryDate($productId = null) {
        //plan update product delivery date for each product in order
        foreach ($this->getProducts() as $item) {
            if (($productId != null) && ($item->getpop_product_id() != $productId))
                continue;

            //plan task
            mage::helper('BackgroundTask')->AddTask('Update product delivery date for product #' . $item->getpop_product_id(),
                    'purchase',
                    'updateProductDeliveryDate',
                    $item->getpop_product_id()
            );
        }
    }

    /**
     * Update costs for every products
     *
     */
    public function UpdateProductsCosts($productId = null) {
        if (Mage::getStoreConfig('purchase/purchase_order/store_product_cost')) {
            if ($this->getpo_status() == MDN_Purchase_Model_Order::STATUS_COMPLETE) {
                foreach ($this->getProducts() as $item) {
                    if (($productId != null) && ($item->getpop_product_id() != $productId))
                        continue;

                    //plan product cost update
                    $productId = $item->getpop_product_id();
                    mage::helper('BackgroundTask')->AddTask('Update cost for product #' . $productId,
                            'purchase/Product',
                            'updateProductCost',
                            $productId,
                            null,
                            true,
                            5
                    );
                }
            }
        }
    }

    /**
     *
     *
     */
    public function UpdateProductsWaitingForDeliveryQty($productId = null) {
        foreach ($this->getProducts() as $item) {
            if (($productId != null) && ($item->getpop_product_id() != $productId))
                continue;

            //plan task
            mage::helper('BackgroundTask')->AddTask('Update waiting for delivery qty for product #' . $item->getpop_product_id(),
                    'purchase',
                    'updateProductWaitingForDeliveryQty',
                    $item->getpop_product_id()
            );
        }
    }

    /**
     * 
     *
     * @param unknown_type $ProductId
     * @param unknown_type $order
     */
    public function AddProduct($ProductId, $qty = 1) {
        
        $purchaseOrderProduct = $this->getPurchaseOrderItem($ProductId);

        //if product is not present
        if ($purchaseOrderProduct == null) {
            $ProductSupplierModel = mage::getModel('Purchase/ProductSupplier');
            $Product = mage::getModel('catalog/product')->load($ProductId);
            $ref = $this->getSupplier()->getProductReference($ProductId);
            $supplierId = $this->getpo_sup_num();

            $productName = $this->getSupplier()->getProductName($ProductId);
            if (!$productName)
                $productName = $Product->getname();

            //check if product is associated to the supplier (is enabled)
            if (mage::helper('purchase')->requireProductSupplierAssociationToAddProductInPo())
            {
                $productSupplier = mage::getModel('Purchase/ProductSupplier')->getProductForSupplier($ProductId, $supplierId);
                if (!$productSupplier)
                    throw new Exception(mage::helper('purchase')->__('This product (%s) is not associated to the supplier)', $Product->getName()));
            }

            //product price
            $price = 0;
            if (Mage::getStoreConfig('purchase/purchase_order/auto_fill_price'))
            {
                $productSupplier = $ProductSupplierModel->getProductForSupplier($ProductId, $supplierId);
                if ($productSupplier)
                    $price = $productSupplier->getpps_last_unit_price_supplier_currency();
            }
            if (Mage::getStoreConfig('purchase/purchase_order/use_product_cost'))
                $price = $Product->getCost();

            //discount
            $discountLevel = $this->getSupplier()->getProductDiscountLevel($ProductId, $qty);

            $orderProduct = mage::getModel('Purchase/OrderProduct')
                            ->setpop_order_num($this->getId())
                            ->setpop_product_id($ProductId)
                            ->setpop_product_name($productName)
                            ->setpop_qty($qty)
                            ->setpop_supplier_ref($ref)
                            ->setpop_price_ht($price)
                            ->setpop_price_ht_base($price)
                            ->setpop_discount($discountLevel)
                            ->setpop_weight($Product->getweight()) //ERP-138
                            ->setpop_tax_rate($this->getPurchaseTaxRate($Product));

            //process packaging (if enabled)
            $packagingHelper = mage::helper('purchase/Product_Packaging');
            if ($packagingHelper->isEnabled()) {
                $purchasePackaging = $packagingHelper->getDefaultPurchasePackaging($ProductId);
                if ($purchasePackaging->getId()) {
                    $packageCount = $purchasePackaging->convertUnitToPackage($qty);
                    $qty = $packageCount * $purchasePackaging->getpp_qty();
                    $orderProduct->setpop_packaging_id($purchasePackaging->getId())
                            ->setpop_packaging_value($purchasePackaging->getpp_qty())
                            ->setpop_packaging_name($purchasePackaging->getpp_name())
                            ->setpop_qty($qty);
                }
            }

            //Save
            $orderProduct->setPurchaseOrder($this);
            $orderProduct->save();

            $this->_products = null;
            
            return $orderProduct;
        } else {
            //if product already belong to the PO, increase qty
            $purchaseOrderProduct->setpop_qty($purchaseOrderProduct->getpop_qty() + $qty)->save();
            $this->_products = null;
            return $purchaseOrderProduct;
        }
    }

    /**
     * Return purchase tax rate for product
     *
     */
    private function getPurchaseTaxRate($product) {

        $TaxId = $product->getpurchase_tax_rate();
        if (($TaxId == 0) || ($TaxId == ''))
            $TaxId = $this->getSupplier()->getTaxRate()->getId();
        if (($TaxId == 0) || ($TaxId == ''))
            $TaxId = mage::getStoreConfig('purchase/purchase_order/products_default_tax_rate');

        //recupere et retourne la valeur
        return mage::getModel('Purchase/TaxRates')->load($TaxId)->getptr_value();
    }

    /**
     * Compute deliveries progress
     *
     */
    public function computeDeliveryProgress() {
        $progress = 0;
        $qtyCount = 0;
        $deliveredCount = 0;

        foreach ($this->getProducts() as $item) {
            $qtyCount += $item->getpop_qty();
            $deliveredCount += $item->getpop_supplied_qty();
        }
        if ($qtyCount > 0)
            $progress = $deliveredCount / $qtyCount * 100;

        $this->setpo_delivery_percent($progress)->save();
    }

    /**
     * After save
     *
     */
    protected function _afterSave() {
        parent::_afterSave();

        $statusBeforeSave = $this->getOrigData('po_status');
        $statusAfterSave = $this->getData('po_status');
        
        if ($statusAfterSave != $statusBeforeSave)
        {
            Mage::dispatchEvent('purchase_order_status_changed', array('purchase_order' => $this));
            if (!$this->_statusChangeAddedToHistory)
            {
                $this->addHistory(Mage::helper('purchase')->__('Status changed from %s to %s', $statusBeforeSave, $statusAfterSave));
                $this->_statusChangeAddedToHistory = true;
            }
        }
    }

    /**
     * Before save
     *
     */
    protected function _beforeSave() {
        parent::_beforeSave();        

        //convert shipping cost & Custom (zoll) cost to base currency
        $fields = array('po_shipping_cost', 'po_zoll_cost');
        $changeRate = ($this->getpo_currency_change_rate() > 0 ? $this->getpo_currency_change_rate() : 1);
        foreach ($fields as $field) {
            $baseValue = $this->getData($field) / $changeRate;
            $this->setdata($field . '_base', $baseValue);
        }

        //updated date
        $this->setpo_updated_at(date('Y-m-d H:i:s'));
    }

    //*********************************************************************************************************************************************************************
    //*********************************************************************************************************************************************************************
    // UPDATES
    //*********************************************************************************************************************************************************************
    //*********************************************************************************************************************************************************************

    /**
     * Update datas and associations when order is complete
     *
     */
    public function updateDataWhenComplete() {
        die('updateDataWhenComplete deprecated');
    }


    /**
     * Check if products qty changed
     *
     * @return unknown
     */
    private function checkIfProductQtyChanged() {
        $value = false;

        foreach ($this->getProducts() as $item) {
            if ($item->getpop_qty() != $item->getOrigData('pop_qty'))
                $value = true;
        }

        return $value;
    }

    //*********************************************************************************************************************************************************************
    //*********************************************************************************************************************************************************************
    // MISC
    //*********************************************************************************************************************************************************************
    //*********************************************************************************************************************************************************************

    /**
     * generate the next PO number
     *
     */
    public function GenerateOrderNumber() {
        $new = Mage::getModel('core/date')->date('Ymd') . 'PO';

        //find next with a limit off 3000 per day
        for ($i = 1; $i < 3000; $i++) {

            //define next number
            $current = $new . $i;

            //check if exist
            $collection = Mage::getModel('Purchase/Order')
                            ->getCollection()
                            ->addFieldToFilter('po_order_id', $current);

            //if not return current
            if (sizeof($collection) == 0){
                return $current;
            }
        }
        return $new;
    }

    

    /**
     * Return a purchase order item for a product id
     *
     * @param unknown_type $productId
     */
    public function getPurchaseOrderItem($productId) {
        foreach ($this->getProducts() as $product) {
            if ($product->getpop_product_id() == $productId)
                return $product;
        }
        return null;
    }

    /**
     * Method to check if product prices are missing
     *
     */
    public function hasMissingPrices() {
        foreach ($this->getProducts() as $item) {
            if ($item->getpop_price_ht() == 0)
                return true;
        }

        return false;
    }

    /**
     * Send order per email to supplier
     *
     */
    public function notifySupplier($msg) {

        //retrieve information
        $email = trim($this->getSupplier()->getsup_mail());
        if (empty($email))
            return false;

        $cc = Mage::getStoreConfig('purchase/notify_supplier/cc_to');
        $identity = Mage::getStoreConfig('purchase/notify_supplier/email_identity');
        $emailTemplate = Mage::getStoreConfig('purchase/notify_supplier/email_template');

        if ($emailTemplate == '')
           throw new Exception('Email template is not set (system > configuration > ERP > Purchase > Notify Supplier > EmailTempalte)');


        $poId = $this->getpo_order_id();

        //get pdf
        $Attachment = null;
        $pdf = Mage::getModel('Purchase/Pdf_Order')->getPdf(array($this));
        
        $Attachment = array();
        $Attachment['name'] = mage::helper('purchase')->__('Purchase Order #') . $poId . '.pdf';
        $Attachment['content'] = $pdf->render();

        $Attachments = array();
        $Attachments[] = $Attachment;

        //message carriage returns
        $msg = str_replace("\n",'<br/>',$msg);

        //definies datas
        $data = array
            (
            'company_name' => Mage::getStoreConfig('purchase/notify_supplier/company_name'),
            'message' => $msg,
            'subject' => Mage::helper('purchase')->__('Purchase Order #'. $poId.' Notification'),
            'order_id' => $poId
        );

        //send email
        $translate = Mage::getSingleton('core/translate');
        $translate->setTranslateInline(false);


        //SEND EMAIL and CC
        
        //default separator for multiple email
        $separator = ';';

        //group all emails together
        if(!empty($cc)){
          $email = trim($email).$separator.trim($cc);
        }

        //Clean ',' separator
        $email = trim(str_replace(',',$separator,$email));

        //get all emails possibles
        $emails = explode($separator, trim($email));
        if ($emails && count($emails) > 0) {
          foreach ($emails as $emailAddress) {
            if(!empty($emailAddress)){

              //send an email to each address detected
              Mage::getModel('Purchase/Core_Email_Template')
                  ->setDesignConfig(array('area' => 'adminhtml'))
                  ->sendTransactional(
                          $emailTemplate,
                          $identity,
                          $emailAddress,
                          $emailAddress,
                          $data,
                          null,
                          $Attachments);
            }
          }
        }       

        $translate->setTranslateInline(true);
        $this->setpo_supplier_notification_date(date('y-m-d H:i'))->save();

        $this->addHistory(Mage::helper('purchase')->__('Supplier notified'));

        return $email;
    }

    /**
     * Return true if all products are delivered
     *
     */
    public function isCompletelyDelivered() {
        $totalQty = 0;

        foreach ($this->getProducts() as $item) {
            if ($item->getpop_qty() > $item->getpop_supplied_qty())
                return false;
            $totalQty += $item->getpop_qty();
        }

        if ($totalQty > 0)
            return true;
        else
            return false;
    }

    /**
     * Create a delivery
     *
     */
    public function createDelivery($OrderProductItem, $qty, $date, $description, $targetWarehouse, $toggleOrderStatus = false) {
        //init vars
        $productId = $OrderProductItem->getpop_product_id();
        $purchasedQty = $qty;

        //if packaging is enabled, edit qty to match to default packagings
        if (mage::helper('purchase/Product_Packaging')->isEnabled()) {
            $purchasedQty = $qty * $OrderProductItem->getpop_packaging_value();
            $qty = mage::helper('purchase/Product_Packaging')->convertToSalesUnit($productId, $purchasedQty);
        }

        //create stock movement
        $stockMovementModel = mage::getModel('AdvancedStock/StockMovement');
        $additionalData = array('sm_po_num' => $this->getId(), 'sm_type' => 'supply','sm_date' => $date);
        $stockMovementModel->validateStockMovement($productId, null, $targetWarehouse, $qty);
        $stockMovementModel->createStockMovement($productId, null, $targetWarehouse, $qty, $description, $additionalData);

        //Update product delivered qty
        $OrderProductItem->updateDeliveredQty();

        //save changes
        if ($toggleOrderStatus) {
            if ($this->isCompletelyDelivered())
                $this->setpo_status(MDN_Purchase_Model_Order::STATUS_COMPLETE);
        }
    }

    /**
     * Return order product item from Product Id
     *
     * @param unknown_type $productId
     */
    public function getOrderProductItem($productId) {
        foreach ($this->getProducts() as $orderProduct) {
            if ($orderProduct->getpop_product_id() == $productId)
                return $orderProduct;
        }

        return null;
    }

    /**
     * Return target warehouse
     */
    public function getTargetWarehouse() {
        $warehouseId = $this->getpo_target_warehouse();
        if (!$warehouseId)
            $warehouseId = 1;
        $warehouse = mage::getModel('AdvancedStock/Warehouse')->load($warehouseId);
        return $warehouse;
    }

    /**
     * Method to define il a field value has changed
     *
     * @param unknown_type $fieldname
     * @return unknown
     */
    protected function fieldHasChanged($fieldname) {
        if ($this->getData($fieldname) != $this->getOrigData($fieldname))
            return true;
        else
            return false;
    }

    //*******************************************************************************************************************************************
    //*******************************************************************************************************************************************
    // Free carriage, order mini
    //*******************************************************************************************************************************************
    //*******************************************************************************************************************************************

    /**
     * Return true if order fullfill supplier's free carriag weight
     */
    public function isFreeCarriageForWeight() {
        $minWeight = $this->getSupplier()->getsup_free_carriage_weight();
        if (($minWeight == '') || ($minWeight == 0))
            return true;

        $orderWeight = $this->getWeight();
        return ($orderWeight >= $minWeight);
    }

    /**
     * Return true if order fullfill supplier's free carriag amount
     */
    public function isFreeCarriageForAmount() {
        $minAmount = $this->getSupplier()->getsup_free_carriage_amount();
        if (($minAmount == '') || ($minAmount == 0))
            return true;

        $orderAmount = $this->getProductTotalBase();

        return ($orderAmount >= $minAmount);
    }

    /**
     * Return true if order fullfill supplier's minimum amount
     */
    public function reachOrderMinimumAmount() {
        $minAmount = $this->getSupplier()->getsup_order_mini();
        if (($minAmount == '') || ($minAmount == 0))
            return true;

        $orderAmount = $this->getProductTotalBase();

        return ($orderAmount >= $minAmount);
    }

    /**
     * Rturn invoice du date
     */
    public function getInvoiceDueDate()
    {
        $invoiceDate = $this->getpo_invoice_date();
        if ($invoiceDate)
        {
            $paymentDelay = $this->getSupplier()->getsup_payment_delay();
            $invoiceTimeStamp = strtotime($invoiceDate);
            $dueDateTimeStamp = $invoiceTimeStamp + $paymentDelay * 24 * 3600;
            return date('Y-m-d', $dueDateTimeStamp);
        }

        return null;
    }
    
    /**
     * Check if current user can edit order
     * @return type
     */
    public function currentUserCanEdit()
    {
        if (!Mage::getSingleton('admin/session')->isAllowed('erp/purchasing/purchase_orders/can_edit_locked_po'))
        {
            return $this->getpo_is_locked() == false;
        }
        else
            return true;
    }

    /**
     * Check if current orderIs locked or not
     * @return type
     */
    public function isLocked()
    {
        $locked = true;

        if($this->getpo_is_locked() == false)
            $locked = false;

        return $locked;
    }

    /**
     * Return all history
     *
     * @return mixed
     */
    public function getHistory()
    {
        return Mage::getModel('Purchase/Order_History')
                            ->getCollection()
                            ->addFieldToFilter('poh_po_id', $this->getId())
                            ->setOrder('poh_created_at', 'desc');
    }

    /**
     * Add an history entry
     *
     * @param $msg
     * @param null $date
     */
    public function addHistory($msg, $timestamp = null)
    {
        if ($timestamp == null)
            $timestamp = mage::getModel('core/date')->timestamp();
        $history = Mage::getModel('Purchase/Order_History')
                        ->setpoh_po_id($this->getId())
                        ->setpoh_created_at($timestamp)
                        ->setpoh_message($msg)
                        ->save();
        return $history;
    }

    public function generateEstimatedDateOfArrival(){

        $dayTimestamp = strtotime(Mage::getModel('core/date')->date('Y-m-d'));

        $supplier = $this->getSupplier();

        if ($supplier->getsup_supply_delay() != null && $supplier->getsup_supply_delay() > 0) {
            $dayTimestamp += $supplier->getsup_supply_delay() * 24 *3600;
        }

        if ($supplier->getsup_shipping_delay() != null && $supplier->getsup_shipping_delay() > 0) {
            $dayTimestamp += $supplier->getsup_shipping_delay() * 24 *3600;
        }
        return date('Y-m-d',$dayTimestamp);
    }

    public function getEstimatedDateOfArrival(){
        return ($this->getpo_supply_date())?$this->getpo_supply_date():$this->generateEstimatedDateOfArrival();
    }


    public function getTotalInvoiced(){
        $totalInvoiced = 0;

        $collection = Mage::getModel('Purchase/PurchaseSupplierInvoice')
            ->getPurchaseOrderSupplierInvoicesCollection($this->getId());
        foreach($collection as $supplierInvoice){
            $totalInvoiced +=  $supplierInvoice->getpsi_amount();
        }

        return  $totalInvoiced;
    }

    public function getTotalPayed(){
        $totalInvoiced = 0;

        $collection = Mage::getModel('Purchase/PurchaseSupplierInvoice')
            ->getPurchaseOrderSupplierInvoicesCollection($this->getId())
            ->addFieldToFilter('psi_status', MDN_Purchase_Model_PurchaseSupplierInvoice::STATUS_PAID);

        foreach($collection as $supplierInvoice){
            $totalInvoiced +=  $supplierInvoice->getpsi_amount();
        }

        return  $totalInvoiced;
    }

}
<?php

class MDN_Orderpreparation_Adminhtml_OrderPreparation_PackingController extends Mage_Adminhtml_Controller_Action {

    /**
     * Main screen for packing
     */
    public function indexAction() {
        $this->loadLayout();

        $this->_setActiveMenu('erp');
        $this->getLayout()->getBlock('head')->setTitle($this->__('Packing'));

        $this->renderLayout();
    }

    public function OrderInformationAction() {
        
        //init var
        $response = array();
        $response['error'] = false;
        $response['message'] = '';

        $barcode = $this->getRequest()->getParam('barcode');
        $needRedirect = false;

        try {
            
            //get order
            $order = Mage::getModel('sales/order')->load($barcode, 'increment_id');
            if (!$order->getId())
                throw new Exception($this->__('Unable to find order #%s', $barcode));

            $state = $order->getstate();
            if ($state == 'canceled' || $state == 'closed')
                throw new Exception($this->__('Unable to ship order #%s, order is already %s', $barcode,$state));

            //get order to prepare
            $orderToPrepare = Mage::getModel('Orderpreparation/ordertoprepare')->getCollection()
                ->addFieldToFilter('order_id', $order->getId())
                ->addFieldToFilter('user', mage::helper('Orderpreparation')->getOperator())
                ->addFieldToFilter('preparation_warehouse',  mage::helper('Orderpreparation')->getPreparationWarehouse())
                ->getFirstItem();

            if (!$orderToPrepare->getId())
            {
                if (Mage::getStoreConfig('orderpreparation/packing/automatically_add_order_in_selected_orders'))
                {
                    Mage::getModel('Orderpreparation/ordertoprepare')->AddSelectedOrder($order->getId());
                    $orderToPrepare = Mage::getModel('Orderpreparation/ordertoprepare')->load($order->getId(), 'order_id');
                    if (!$orderToPrepare->getId())
                       throw new Exception($this->__('This order is not in the selected orders'));
                }
            }

            //check that order is not shipped
            if (Mage::getStoreConfig('orderpreparation/packing/prevent_packing_if_order_already_shipped')) {
                if ($orderToPrepare->getshipment_id()) {
                    $needRedirect = true;
                    throw new Exception($this->__('This order is already packed !'));
                }
            }


            //check that order is invoiced
            if (Mage::getStoreConfig('orderpreparation/packing/prevent_packing_if_no_invoice')) {
                if (!$orderToPrepare->getinvoice_id())
                    throw new Exception($this->__('This order is not invoiced !'));
            }
            
            //return order information
            $block = $this->getLayout()->createBlock('Orderpreparation/Packing_Products');
            $block->setTemplate('Orderpreparation/Packing/Products.phtml');
            $block->setOrder($order);
            $orderInformation = $block->toHtml();
            $response['order_html'] = $orderInformation;
            $response['order_id'] = $order->getId();

            $response['products_json'] = $this->getProductJson($order);

            $response['group_ids'] = $this->getProductJson($order, true);
        } catch (Exception $ex) {
            $response['error'] = true;
            $response['message'] = $ex->getMessage();
            if($needRedirect) {
                $response['redirectUrl'] = Mage::helper('adminhtml')->getUrl('adminhtml/OrderPreparation_Packing', array('order_id' => $order->getId()));
            }
        }


        //return response
        $response = Zend_Json::encode($response);
        $this->getResponse()->setBody($response);
    }

    /**
     * return product or groups json array
     * 
     * @param <type> $order
     */
    protected function getProductJson($order, $group = false) {
        $array = array();

        $orderId = $order->getId();
        $products = Mage::getModel('Orderpreparation/ordertoprepare')->GetItemsToShip($orderId);
        foreach ($products as $product) {
            if ($this->productManageStock($product)) {
                $item = array();
                $item['name'] = $product->getSalesOrderItem()->getName();
                $item['id'] = $product->getId();
                $item['qty_scanned'] = 0;
                $item['serials'] = $product->getSalesOrderItem()->getErpOrderItem()->getserials();
                $item['qty'] = $product->getqty();
                $item['barcode'] = Mage::helper('AdvancedStock/Product_Barcode')->getBarcodeForProduct($product->getproduct_id());
                $currentItemGroup = $product->getSalesOrderItem()->getparent_item_id();
                if (!$currentItemGroup)
                    $currentItemGroup = 'simple';
                $item['group_id'] = $currentItemGroup;
                
                //manage additional barcodes
                $productId = $product->getproduct_id();
                $item['additional_barcodes'] = '';
                //add other barcodes
                $barcodes = Mage::helper('AdvancedStock/Product_Barcode')->getBarcodesForProduct($productId);
                foreach($barcodes as $barcode)
                {
                    if ($item['barcode'] != $barcode->getppb_barcode())
                    {
                        $item['additional_barcodes'] .= $barcode->getppb_barcode().',';
                    }
                }                
                
                if ($group == false)
                    $array[] = $item;
                else
                {
                    if (!in_array($currentItemGroup, $array))
                            $array[] = $currentItemGroup;
                }
            }
        }

        return $array;
    }

    /**
     * return true if product manage stocks
     * 
     * @param type $orderToPrepareItem
     * @return type 
     */
    public function productManageStock($orderToPrepareItem) {
        $productId = $orderToPrepareItem->getproduct_id();
        $product = Mage::getModel('catalog/product')->load($productId);
        return $product->getStockItem()->getManageStock();
    }

    /**
     * Commit packing 
     */
    public function CommitAction() {
        $orderId = $this->getRequest()->getPost('order_id');
        $order = Mage::getModel('sales/order')->load($orderId);
        $weight = $this->getRequest()->getPost('weight');
        $parcelCount = $this->getRequest()->getPost('parcel_count');

        try {

            //update weight
            $orderToPrepare = mage::getModel('Orderpreparation/ordertoprepare')->load($orderId, 'order_id');
            $orderToPrepare->setreal_weight($weight);
            $orderToPrepare->setpackage_count($parcelCount);

            //update carrier template fields
            $carrierTemplateData = $this->getRequest()->getPost('carriertemplatedata');
            $carrierTemplateData = str_replace('carriertemplatedata[', '', $carrierTemplateData);
            $carrierTemplateData = str_replace(']', '', $carrierTemplateData);
            $orderToPrepare->setcustom_values($carrierTemplateData);
            $orderToPrepare->save();

            //update serials
            $serials = $this->getRequest()->getPost('serials');
            if ($serials)
            {
                $tSerials = explode(';', $serials);
                foreach ($tSerials as $tSerial)
                {
                    //extract get datas
                    $t = explode('=', $tSerial);
                    if (count($t) != 2)
                        continue;
                    list($orderToPrepareItemId, $serialNumbers) = $t;

                    //insert into erp_sales_flat_order_item
                    $orderToPrepareItem = Mage::getModel('Orderpreparation/ordertoprepareitem')->load($orderToPrepareItemId);
                    $orderItem = $orderToPrepareItem->getSalesOrderItem();
                    $erpSalesFlatOrderItem = Mage::getModel('AdvancedStock/SalesFlatOrderItem')->load($orderItem->getId());
                    $erpSalesFlatOrderItem->setserials($serialNumbers)->save();
                }
            }

            //Create shipment
            $shipment = null;
            if (Mage::getStoreConfig('orderpreparation/packing/create_shipment_on_commit')) {
                $preparationWarehouseId = mage::helper('Orderpreparation')->getPreparationWarehouse();
                $operatorId = mage::helper('Orderpreparation')->getOperator();

                //create invoice (if set)
                if (mage::getStoreConfig('orderpreparation/packing/create_invoice_on_commit') == 1) {
                    if (!Mage::helper('Orderpreparation/Invoice')->InvoiceCreatedForOrder($order->getid())) {
                        Mage::helper('Orderpreparation/Invoice')->CreateInvoice($order);
                    }
                }

                //check that order is invoiced
                if (Mage::getStoreConfig('orderpreparation/packing/prevent_packing_if_no_invoice')) {
                    if (!$orderToPrepare->getinvoice_id())
                        throw new Exception($this->__('This order is not invoiced !'));
                }

                $shipment = Mage::helper('Orderpreparation/Shipment')->CreateShipment($order, $preparationWarehouseId, $operatorId);
            }

            //reload order to prepare (to consider new shipment / invoice)
            $orderToPrepare = mage::getModel('Orderpreparation/ordertoprepare')->load($orderId, 'order_id');

            //Print packing slip (if configured)
            if (Mage::getStoreConfig('orderpreparation/packing/print_packing_slip_when_order_packed')) {
                if ($shipment) {
                    switch (Mage::getStoreConfig('orderpreparation/order_preparation_step/print_method')) {
                        case 'send_to_printer':
                            $pdf = Mage::getModel('sales/order_pdf_shipment')->getPdf(array($shipment));
                            $fileName = 'shipment_' . $shipment->getincrement_id() . '.pdf';
                            mage::helper('ClientComputer')->printDocument($pdf->render(), $fileName, $fileName);
                            break;
                    }
                }
            }

            //Print invoice (if configured)
            if (Mage::getStoreConfig('orderpreparation/packing/print_invoice_when_order_packed')) {
                $invoiceIncrementId = $orderToPrepare->getinvoice_id();
                if ($invoiceIncrementId) {
                    $invoice = Mage::getModel('sales/order_invoice')->load($invoiceIncrementId, 'increment_id');
                    switch (Mage::getStoreConfig('orderpreparation/order_preparation_step/print_method')) {
                        case 'send_to_printer':
                            $pdf = Mage::getModel('sales/order_pdf_invoice')->getPdf(array($invoice));
                            $fileName = 'invoice_' . $invoice->getincrement_id() . '.pdf';
                            mage::helper('ClientComputer')->printDocument($pdf->render(), $fileName, $fileName);
                            break;
                    }
                }
                else
                    Mage::getSingleton('adminhtml/session')->addError($this->__('No invoice to print'));
            }

            //print shipping label (if configured)
            if (Mage::getStoreConfig('orderpreparation/packing/print_shipping_label')) {
                $template = mage::helper('Orderpreparation/CarrierTemplate')->getTemplateForOrder($order);
                if ($template != null) {
                    $collection = mage::getModel('Orderpreparation/ordertoprepare')
                            ->getCollection()
                            ->addFieldToFilter('order_id', $orderId);
                    $content = $template->createExportFile($collection);
                    switch (Mage::getStoreConfig('orderpreparation/order_preparation_step/print_method')) {
                        case 'send_to_printer':
                            mage::helper('ClientComputer')->copyFile($content, $template->getct_export_filename(), 'directory_' . $template->getct_shipping_method(), 'Print shipping labels for order #' . $order->getIncrementId());
                            if ($template->getct_export_witness_filename() != '') {
                                mage::helper('ClientComputer')->copyFile('XXX', $template->getct_export_witness_filename(), 'directory_' . $template->getct_shipping_method(), 'Witness file for order #' . $order->getIncrementId());
                            }
                            break;
                    }
                }
                else
                {
                    //if we cant find a carrier template
                    Mage::getSingleton('adminhtml/session')->addError($this->__('No carrier template available for this order, unable to print the shipping label'));
                }
            }

            Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Packing commited'));
        } catch (Exception $ex) {
            Mage::getSingleton('adminhtml/session')->addError($this->__($ex->getMessage()));
        }

        //redirect
        $this->_redirect('adminhtml/OrderPreparation_Packing', array('order_id' => $orderId));
    }

    /**
     * Print shipment PDF 
     */
    public function printShipmentAction() {
        
        //load shipment
        $shipmentId = $this->getRequest()->getParam('shipment_id');
        $shipmentIncrementId = $this->getRequest()->getParam('shipment_increment_id');
        if ($shipmentId)
            $shipment = Mage::getModel('sales/order_shipment')->load($shipmentId);
        else
            $shipment = Mage::getModel('sales/order_shipment')->loadByIncrementId($shipmentIncrementId);
            
        //generate & download PDF
        $pdf = Mage::getModel('sales/order_pdf_shipment')->getPdf(array($shipment));
        $fileName = 'shipment_' . $shipment->getIncrementId() . '.pdf';
        $this->_prepareDownloadResponse($fileName, $pdf->render(), 'application/pdf');
    }

    /**
     * Print invoice PDF 
     */
    public function printInvoiceAction() {
        
        //load invoice
        $invoiceId = $this->getRequest()->getParam('invoice_id');
        $invoiceIncrementId = $this->getRequest()->getParam('invoice_increment_id');
        
        if ($invoiceId)
            $invoice = Mage::getModel('sales/order_invoice')->load($invoiceId);
        else
            $invoice = Mage::getModel('sales/order_invoice')->loadByIncrementId($invoiceIncrementId);

        //generate & download PDF
        $pdf = Mage::getModel('sales/order_pdf_invoice')->getPdf(array($invoice));
        $fileName = 'invoice_' . $invoice->getIncrementId() . '.pdf';
        $this->_prepareDownloadResponse($fileName, $pdf->render(), 'application/pdf');
    }
    
    /**
     * Download file for shipping software
     */
    public function downloadShippingLabelFileAction()
    {
        //load invoice
        $orderId = $this->getRequest()->getParam('order_id');
        $orderToPrepare = mage::getModel('Orderpreparation/ordertoprepare')->load($orderId, 'order_id');
        $order = Mage::getModel('sales/order')->load($orderId);
        $carrierTemplate = mage::helper('Orderpreparation/CarrierTemplate')->getTemplateForOrder($order);
        if ($carrierTemplate == null)
        {
            $fileName = 'error_no_template_found_for_order.csv';
            $this->_prepareDownloadResponse($fileName, '', 'text/csv');
        }
        else
        {
            $collection = mage::getModel('Orderpreparation/ordertoprepare')
                    ->getCollection()
                    ->addFieldToFilter('order_id', $orderId);
            $content = $carrierTemplate->createExportFile($collection);

            $fileName = $order->getIncrementId() . ' - '.$carrierTemplate->getct_export_filename();
            $this->_prepareDownloadResponse($fileName, $content);
        }
        
    }

    /**
     * Change shipping method
     */
    public function ChangeShippingMethodAction()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        $newMethod = $this->getRequest()->getParam('new_method');

        mage::helper('Orderpreparation/ShippingMethods')->changeForOrder($orderId, $newMethod);

        $this->_redirect('adminhtml/OrderPreparation_Packing', array('force_order' => 1,  'order_id' => $orderId));
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('admin/erp/order_preparation');
    }
}

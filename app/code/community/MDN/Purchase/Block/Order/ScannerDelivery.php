<?php

class MDN_Purchase_Block_Order_ScannerDelivery extends Mage_Adminhtml_Block_Widget_Form {

    protected $_purchaseOrder = null;

    /**
     * Return current purchase order
     * @return <type>
     */
    public function getPurchaseOrder() {
        if ($this->_purchaseOrder == null) {
            $poId = $this->getRequest()->getParam('po_num');
            $this->_purchaseOrder = Mage::getModel('Purchase/Order')->load($poId);
        }
        return $this->_purchaseOrder;
    }
    
    public function getTranslateJson() {
        $translations = array(
            'Assign barcode to product' => $this->__('Assign barcode to product'),
            'Scan product barcode' => $this->__('Scan product barcode'),
            'Unknown barcode : ' => $this->__('Unknown barcode : '),
            'Expected Qty' => $this->__('Expected Qty'),
            'Location' => $this->__('Location'),
            'Scan serial number (press enter to skip)' => $this->__('Scan serial number (press enter to skip)'),
            'Barcode' => $this->__('Barcode'),
            'Scanned Qty' => $this->__('Scanned Qty'),
            'Missing Qty' => $this->__('Missing Qty'),
            ' scanned' => $this->__(' scanned'),
            'Location assigned' => $this->__('Location assigned'),
            'Serial number assigned' => $this->__('Serial number assigned'),
            'Do you confirm ?' => $this->__('Do you confirm ?'),
            'Assign' => $this->__('Assign'),
            'Name' => $this->__('Name'),
            'Scan location' => $this->__('Scan location'),
            'Serial numbers' => $this->__('Serial numbers'),
            ' is unknown, do you want to assign it to a product :' => $this->__(' is unknown, do you want to assign it to a product :'),
            );
        return Mage::helper('core')->jsonEncode($translations);
    }

    /**
     * Return PO data as json
     */
    public function getJsonData() {
        $data = array();
        $warehouse = $this->getPurchaseOrder()->getTargetWarehouse();

        foreach ($this->getPurchaseOrder()->getProducts() as $product) {
            try {
                $productId = $product->getpop_product_id();
                $productObj = Mage::getModel('catalog/product')->load($productId);
                if(!$productObj->getId()){
                    continue;
                }
                if (!Mage::helper('AdvancedStock/Product_Base')->productExists($productId)){
                    continue;
                }
                $item = array();
                $item['pop_id'] = $product->getpop_num();
                $item['name'] = $product->getpop_product_name();
                $item['sku'] = $product->getsku();
                $item['expected_qty'] = $product->getRemainingQty();
                $item['scanned_qty'] = 0;
                $item['image_url'] = $this->getImageUrl($productObj);
                $packagingLabel = '';
                if ($product->getpop_packaging_value() > 0 && $product->getpop_packaging_name()) {
                    $packagingLabel = $product->getpop_packaging_name() . ' (' . $product->getpop_packaging_value() . 'x)';;
                }
                $item['packaging'] = $packagingLabel;
                $item['serials'] = '';
                $item['barcode'] = Mage::helper('AdvancedStock/Product_Barcode')->getBarcodeForProduct($productId);

                //manage additional barcodes
                $item['additional_barcodes'] = '';
                //add other barcodes
                $barcodes = Mage::helper('AdvancedStock/Product_Barcode')->getBarcodesForProduct($productId);
                foreach ($barcodes as $barcode) {
                    if ($item['barcode'] != $barcode->getppb_barcode()) {
                        $item['additional_barcodes'] .= $barcode->getppb_barcode() . ',';
                    }
                }

                $item['new_barcode'] = '';
                $item['location'] = $warehouse->getProductLocation($productId);
                $item['new_location'] = '';

                $data[] = $item;
            }catch(Exception $ex){
                mage::logException($ex);
            }
        }

        return Zend_Json::encode($data);
    }

    /**
     * Return image url for product
     * @param <type> $product
     * @return <type>
     */
    protected function getImageUrl($product) {
        if ($product->getSmallImage()) {
            return Mage::getBaseUrl('media') . DS . 'catalog' . DS . 'product' . $product->getSmallImage();
        } else {
            //try to find image from configurable product
            $configurableProduct = Mage::helper('AdvancedStock/Product_ConfigurableAttributes')->getConfigurableProduct($product);
            if ($configurableProduct) {
                if ($configurableProduct->getSmallImage()) {
                    return Mage::getBaseUrl('media') . DS . 'catalog' . DS . 'product' . $configurableProduct->getSmallImage();
                }
            }
        }

        return '';
    }

    /**
     * Scan location setting
     * @return <type>
     */
    public function scanLocation() {
        return (Mage::getStoreConfig('purchase/scanner/location') ? '1' : '0');
    }

    /**
     * Scan serial setting
     * @return <type>
     */
    public function scanSerial() {
        return (Mage::getStoreConfig('purchase/scanner/serial') ? '1' : '0');
    }

    /**
     * product packaging setting
     * @return <type>
     */
    public function displayPackaging() {
        return (mage::helper('purchase/Product_Packaging')->isEnabled() ? '1' : '0');
    }

    /**
     * Allow assign barcode
     * @return <type>
     */
    public function assignBarcode()
    {
        return (Mage::getStoreConfig('purchase/scanner/allow_barcode_assigment') ? '1' : '0');
    }


    /**
     *
     */
    public function getFormUrl() {
        return Mage::helper('adminhtml')->getUrl('*/*/CommitScannerDelivery');
    }

    public function getBackUrl() {
        return Mage::helper('adminhtml')->getUrl('adminhtml/Purchase_Orders/Edit', array('po_num' => $this->getPurchaseOrder()->getId()));
    }

}

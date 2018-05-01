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
class MDN_Orderpreparation_Helper_PickingList extends Mage_Core_Helper_Abstract {

    /**
     * Return picking list PDF
     */
    public function getPdf() {
        
        //generate PDF depending of the method
        if (!mage::getModel('Orderpreparation/ordertoprepare')->countOrders(MDN_Orderpreparation_Model_OrderToPrepare::filterSelected))
                throw new Exception('There is no orders in selected orders');

        if (Mage::getStoreConfig('orderpreparation/picking_list/mode') == MDN_Orderpreparation_Model_Source_PickingListMode::kMerged) {
            $pdf  = $this->generateMergedPickingList();
        }

        if (Mage::getStoreConfig('orderpreparation/picking_list/mode') == MDN_Orderpreparation_Model_Source_PickingListMode::kOnePagePerOrder) {
            $pdf  = $this->generateOrderByOrderPickingList();
        }

        if (Mage::getStoreConfig('orderpreparation/picking_list/mode') == MDN_Orderpreparation_Model_Source_PickingListMode::kBoth) {
            $pdf  = $this->generateMergedPickingList();
            $pdf  = $this->generateOrderByOrderPickingList($pdf);
        }

        return $pdf;
    }

    public function generateMergedPickingList($parentPdf = null){
        //merged mode, all products are displayed in the same document (mass picking)
        $obj = mage::getModel('Orderpreparation/Pdf_PickingList');
        $products = $this->GetProductsSummary();
        if($parentPdf){
            $obj->pdf = $parentPdf;
        }
        return $obj->getPdf(array('comments' => '', 'products' => $products));
    }

    public function generateOrderByOrderPickingList($parentPdf = null){
        //single document per order
        $orders = Mage::getModel('Orderpreparation/Ordertoprepare')->getSelectedOrders();
        $pdf = new Zend_Pdf();
        if($parentPdf){
            $pdf = $parentPdf;
        }
        foreach ($orders as $order) {
            $obj = mage::getModel('Orderpreparation/Pdf_OrderPreparationCommentsPdf');
            $obj->pdf = $pdf;
            $obj->getPdfWithMode($order, MDN_Orderpreparation_Model_Pdf_OrderPreparationCommentsPdf::MODE_ORDER_PREPRATION_SELECTED_TAB);
        }
        return $pdf;
    }


    public function getPdfFromOrderList($orderIds) {
        $pdf = null;
        $preparationWarehouse = mage::helper('Orderpreparation')->getPreparationWarehouse();

        if (Mage::getStoreConfig('orderpreparation/picking_list/mode') == MDN_Orderpreparation_Model_Source_PickingListMode::kMerged) {
            $data = $this->getProductsSummaryFromOrderIds($orderIds, $preparationWarehouse);
            $pdf = mage::getModel('Orderpreparation/Pdf_PickingList')->getPdf($data);
        }else{
            $orders = Mage::getModel('Orderpreparation/Ordertoprepare')->getSelectedOrders($orderIds);
            $pdf = new Zend_Pdf();
            foreach ($orders as $order) {
                $obj = mage::getModel('Orderpreparation/Pdf_OrderPreparationCommentsPdf');
                $obj->pdf = $pdf;
                $obj->getPdfWithMode($order, MDN_Orderpreparation_Model_Pdf_OrderPreparationCommentsPdf::MODE_ORDER_PREPRATION_NOT_SELECTED_TAB);
            }
        }
        return $pdf;
    }

    /**
     *
     * Define if we display product in picking list
     *
     * @param <type> $item
     * @param <type> $order
     * @return <type>
     */
    public function isItemDisplayedInPickingList($item, $order) {
        //manage setting orderpreparation/picking_list/display_product_without_stock_management
        if (mage::getStoreConfig('orderpreparation/picking_list/display_product_without_stock_management') == 0) {
            $productId = $item->getproduct_id();
            $stockItem = mage::getModel('cataloginventory/stock_item')->loadByProduct($productId);
            if ($stockItem->getId()) {
                if (!$stockItem->ManageStock())
                    return 0;
            }
        }

        //todo implement 'orderpreparation/picking_list/display_sub_products' logic
        return 1;
    }

    /*
     * Return products
     * associative array : key = product_id, value = qty
     *
     */

    public function GetProductsSummary() {
        $retour = array();

        $warehouseId = mage::helper('Orderpreparation')->getPreparationWarehouse();
        $collection = Mage::getModel('Orderpreparation/ordertoprepareitem')
                ->getCollection()
                ->addFieldToFilter('display_in_picking_list', 1)
                ->addFieldToFilter('preparation_warehouse', $warehouseId)
                ->addFieldToFilter('user', mage::helper('Orderpreparation')->getOperator());
        

        //avoid crash if manufacturer attribute does not exist
        $manufacturerAttributeExists = false;
        $manufacturerCode = mage::getModel('AdvancedStock/Constant')->GetProductManufacturerAttributeCode();
        if($manufacturerCode && $manufacturerCode == 'manufacturer'){
          $manufacturerAttributeExists = true;
        }

        $warehouse = mage::getModel('AdvancedStock/Warehouse')->load($warehouseId);
        foreach ($collection as $item) {
        
            //load product
            $product_id = $item->getproduct_id();
            $qty = $item->getqty();

            //add product
            if (isset($retour[$product_id])){
                //QTY
                $retour[$product_id]->setqty($retour[$product_id]->getqty() + $qty);

                //ORDER LIST
                $orderListTemp = $retour[$product_id]->getorder_list();
                $orderListTemp[$item->getorder_id()] = $qty;
                $retour[$product_id]->setorder_list($orderListTemp);

            }else {

                //BEGIN
                $product = mage::getmodel('catalog/product')->load($product_id);

                //QTY
                $product->setqty($qty);
                $retour[$product_id] = $product;

                //ORDERS
                $orderList = array();
                $orderList[$item->getorder_id()] = $qty;
                $retour[$product_id]->setorder_list($orderList);

                //define additional information
                $picturePath = '';
                if ($product->getSmallImage())
                    $picturePath = Mage::getBaseDir() . DS . 'media' . DS . 'catalog' . DS . 'product' . $product->getSmallImage();
                $product->setpicture_path($picturePath);
                $product->setbarcode(mage::helper('AdvancedStock/Product_Barcode')->getBarcodeForProduct($product));

                //retrieve warehouse
                $product->setlocation($warehouse->getProductLocation($product->getId()));
                if($manufacturerAttributeExists){
                  $product->setmanufacturer($product->getAttributeText('manufacturer'));
                }
            }
        }

        //order the list
        usort($retour, array("MDN_Orderpreparation_Helper_PickingList", "sortProductPerLocationAndManufacturer"));

        return $retour;
    }

    /**
     * Create picking list from a list of orders
     *
     */
    public function getProductsSummaryFromOrderIds(array $orderIds, $warehouseId) {
        $comments = '';
        $products = array();


        //parse orders collection
        $collection = mage::getModel('sales/order')
                ->getCollection()
                ->addAttributeToSelect('*')
                ->addFieldToFilter('entity_id', array('in' => $orderIds));


        //avoid crahs if manufacturer does not exist
        $manufacturerAttributeExists = false;
        $manufacturerCode = mage::getModel('AdvancedStock/Constant')->GetProductManufacturerAttributeCode();
        if($manufacturerCode && $manufacturerCode == 'manufacturer'){
          $manufacturerAttributeExists = true;
        }

        foreach ($collection as $order) {
            $comments .= mage::helper('Orderpreparation')->__('Order #%s : %s', $order->getIncrementId(), $order->getCustomerName()) . "\n";
            foreach ($order->getAllItems() as $orderItem) {

                //skip product if doesn't belong to the right warehouse
                if ($orderItem->getpreparation_warehouse() != $warehouseId)
                    continue;

                //skip product if bundle product
                if ($orderItem->getProductType() == 'bundle'){
                    continue;
                }

                //add product
                $qty = $orderItem->getRemainToShipQty();

                $product_id = $orderItem->getproduct_id();
                if (isset($products[$product_id])){

                    $products[$product_id]->setqty($products[$product_id]->getqty() + $qty);

                    //ORDER LIST
                    $orderListTemp = $products[$product_id]->getorder_list();
                    $orderListTemp[$orderItem->getorder_id()] = $qty;
                    $products[$product_id]->setorder_list($orderListTemp);

                } else {
                    $product = mage::getmodel('catalog/product')->load($product_id);

                    //check orderpreparation/picking_list/display_product_without_stock_management setting
                    if (mage::getStoreConfig('orderpreparation/picking_list/display_product_without_stock_management') == 0) {
                        $stockItem = mage::getModel('cataloginventory/stock_item')->loadByProduct($product_id);
                        if ($stockItem->getId()) {
                            if (!$stockItem->ManageStock())
                                continue;
                        }
                    }

                    //QTY
                    $product->setqty($qty);
                    $products[$product_id] = $product;

                    //ORDERS
                    $orderList = array();
                    $orderList[$orderItem->getorder_id()] = $qty;
                    $products[$product_id]->setorder_list($orderList);


                    

                    //define additional information
                    $picturePath = '';
                    if ($product->getSmallImage())
                        $picturePath = Mage::getBaseDir() . DS . 'media' . DS . 'catalog' . DS . 'product' . $product->getSmallImage();
                    $product->setpicture_path($picturePath);
                    $product->setbarcode(mage::helper('AdvancedStock/Product_Barcode')->getBarcodeForProduct($product));

                    $warehouse = $orderItem->getPreparationWarehouse();
                    $product->setlocation($warehouse->getProductLocation($product->getId()));
                    if($manufacturerAttributeExists){
                      $product->setmanufacturer($product->getAttributeText('manufacturer'));
                    }
                }
            }
        }

        //order the list
        usort($products, array("MDN_Orderpreparation_Helper_PickingList", "sortProductPerLocationAndManufacturer"));

        //return datas
        $retour = array();
        $retour['comments'] = $comments;
        $retour['products'] = $products;

        
        return $retour;
    }

    /**
     * Sort product by manufacturers using paramater in system > configuration > order prepration > picking list > sort mode
     *
     */
    public static function sortProductPerLocationAndManufacturer($a, $b) {

        $sortbyManufacturer = false;//avoid crash is no manufacturer attribute exist

        $manufacturerCode = mage::getModel('AdvancedStock/Constant')->GetProductManufacturerAttributeCode();        
        
        if($manufacturerCode && $manufacturerCode == 'manufacturer'){
          $sortbyManufacturer = true;
        }

        if (mage::getStoreConfig('orderpreparation/picking_list/sort_mode') == 'location') {

            if ($a->getlocation() != $b->getlocation()) {
                //strtolower fix the fact that in ascii Table Upper case letter are before lower cases letter
                //so the sort was doing that if we don't use strtolower : AAA BBB aaa BBB
                //avec Strtolower, ça fait AAA aaa BBB bbb
                if (strtolower($a->getlocation()) < strtolower($b->getlocation()))
                    return -1;
                else
                    return 1;
            }
            else {
                if($sortbyManufacturer){
                  if ($a->getmanufacturer() < $b->getmanufacturer())
                      return -1;
                  else
                      return 1;
                }else{
                   return 1;
                }
            }
        }
        else {
            if($sortbyManufacturer){
              if ($a->getmanufacturer() < $b->getmanufacturer())
                  return -1;
              else
                  return 1;
            }else{
               return 1;
            }
        }
    }

}
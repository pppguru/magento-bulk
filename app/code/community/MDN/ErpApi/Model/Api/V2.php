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
 * @copyright  Copyright (c) 2014 Maison du Logiciel (http://www.maisondulogiciel.com)
 * @author : Guillaume SARRAZIN
 * @license :  http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MDN_ErpApi_Model_Api_V2 extends Mage_Api_Model_Resource_Abstract {

    const DEFAULT_LIST_LIMIT = 100;

    const API_VERSION = '1.1.2';

    //To Push Abstract
    private function formatStringForSoap($string){
        return utf8_encode(html_entity_decode(trim($string)));
    }


    /*
     * get  API version
     *
    */
    public function getErpApiVersion(){

        return self::API_VERSION;

    }

    /*
     * get magento users
     *
    */
    public function getOperators(){

        $operators = array();

        $users = mage::getModel('admin/user')->getCollection();

        foreach ($users as $user) {
            $operators[$user->getId()] = $user->getusername();
        }

        return $operators;

    }


    //------------------------------ WRITE

    //----------- Advanced Stock

    public function updateProductQtyByWarehouse($productId,$warehouseId,$qty)
    {

        if($productId == null || $productId <= 0){
            throw new Exception('Invalid product id='.$productId);
        }

        if($warehouseId == null || $warehouseId <= 0){
            throw new Exception('Invalid warehouse id='.$warehouseId);
        }

        if($qty == null || $qty < 0){
            throw new Exception('Invalid qty='.$qty);
        }

        $stockItem = mage::getModel('cataloginventory/stock_item')->loadByProductWarehouse($productId, $warehouseId);

        if(!$stockItem){
            throw new Exception('Failed to laod Stock item for Pid='.$productId.' and Stockid='.$warehouseId);
        }

        try {
            $stockItem->setQty($qty)->save();
        } catch (Mage_Core_Exception $e) {
            throw new Exception('Qty not updated ', $e->getMessage());
        }

        return true;
    }

    public function updateProductSelfLocationByWarehouse($productId,$warehouseId,$shelfLocation)
    {

        if($productId == null || $productId<=0){
            throw new Exception('Invalid product id='.$productId);
        }

        if($warehouseId == null || $warehouseId<=0){
            throw new Exception('Invalid warehouse id='.$warehouseId);
        }

        $shelfLocation = trim($shelfLocation);

        $stockItem = mage::getModel('cataloginventory/stock_item')->loadByProductWarehouse($productId, $warehouseId);

        if(!$stockItem){
            throw new Exception('Failed to laod Stock item for Pid='.$productId.' and Stockid='.$warehouseId);
        }

        try {
            $stockItem->setshelf_location($shelfLocation)->save();
        } catch (Mage_Core_Exception $e) {
            throw new Exception('Shelf Location not updated ', $e->getMessage());
        }

        return true;
    }

    public function updateProductWarningStockLevelByWarehouse($productId,$warehouseId,$warningStockLevel)
    {

        if($productId == null || $productId<=0){
            throw new Exception('Invalid product id='.$productId);
        }

        if($warehouseId == null || $warehouseId<=0){
            throw new Exception('Invalid warehouse id='.$warehouseId);
        }

        if($warningStockLevel == null || $warningStockLevel<0){
            throw new Exception('Invalid warningStockLevel='.$warningStockLevel);
        }

        $stockItem = mage::getModel('cataloginventory/stock_item')->loadByProductWarehouse($productId, $warehouseId);

        if(!$stockItem){
            throw new Exception('Failed to laod Stock item for Pid='.$productId.' and Stockid='.$warehouseId);
        }

        try {
            $stockItem->setnotify_stock_qty($warningStockLevel)->setuse_config_notify_stock_qty(0)->save();
        } catch (Mage_Core_Exception $e) {
            throw new Exception('warningStockLevel not updated ', $e->getMessage());
        }

        return true;
    }

    public function updateProductIdealStockLevelByWarehouse($productId,$warehouseId,$idealStockLevel)
    {

        if($productId == null || $productId<=0){
            throw new Exception('Invalid product id='.$productId);
        }

        if($warehouseId == null || $warehouseId<=0){
            throw new Exception('Invalid warehouse id='.$warehouseId);
        }

        if($idealStockLevel == null || $idealStockLevel<0){
            throw new Exception('Invalid idealStockLevel='.$idealStockLevel);
        }

        $stockItem = mage::getModel('cataloginventory/stock_item')->loadByProductWarehouse($productId, $warehouseId);

        if(!$stockItem){
            throw new Exception('Failed to laod Stock item for Pid='.$productId.' and Stockid='.$warehouseId);
        }


        try {
            $stockItem->setideal_stock_level($idealStockLevel)->setuse_config_ideal_stock_level(0)->save();
        } catch (Mage_Core_Exception $e) {
            throw new Exception('Qty not updated ', $e->getMessage());
        }

        return true;
    }

    //------------------ LIB
    public function convertStdClassToArray($soapObjectRespsonse){
        $a = array();

        if($soapObjectRespsonse && is_object($soapObjectRespsonse)){

            $main = $soapObjectRespsonse->item;
            var_dump($main);
            if(is_object($main)){
                $a[$main->key] = $main->value;

            }
            if(is_array($main)){
                foreach($main as $key => $object) {
                    $a[$object->key] = $object->value;
                }
            }
        }
        return $a;
    }

    //----------- Purchase


    /*
     * create a delivery for a purchase order
     *
     * @param int $purchaseOrderId
     * @param int $targetWarehouseId
     * @param array $itemsDelivered
     *
     * itemsDelivered structure
     *
     * array[productId]['delivery_qty'];
     * array[productId]['delivery_defect_qty'];
     *
     *
     *
     * @return boolean
     */
    public function purchaseOrderDelivery($purchaseOrderId,$targetWarehouseId, $itemsDelivered, $itemsDeliveredDefect){

            $itemsDelivered = $this->convertStdClassToArray($itemsDelivered);
            $itemsDeliveredDefect = $this->convertStdClassToArray($itemsDeliveredDefect);

            $order = mage::getModel('Purchase/Order')->load($purchaseOrderId);

            $processed = 0;

            if($order && $order->getpo_num()>0){

                $purchaseOrderUpdater = mage::getModel('Purchase/Order_Updater')->init($order);

                $deliveryDate = date('Ymd His');
                $deliveryDescription = mage::helper('purchase')->__('Purchase Order #') . $order->getpo_order_id() . mage::helper('purchase')->__(' from ') . $order->getSupplier()->getsup_name();
                $defectWarehouseId = mage::getStoreConfig('purchase/purchase_order/defect_products_warehouse');

                $purchaseOrderProducts = $order->getProducts();

                if(count($itemsDelivered)>0 || count($itemsDeliveredDefect)>0){
                    foreach ($purchaseOrderProducts as $purchaseOrderProduct) {

                        $productId = $purchaseOrderProduct->getpop_product_id();

                        //skip product if no delivery information
                        if (isset($itemsDelivered[$productId])){

                            //check develiery datas
                            $qty = (int) (isset($itemsDelivered[$productId]) ? $itemsDelivered[$productId] : 0);

                            if ($qty == 0)
                                continue;

                            //create deliveries
                            if ($qty > 0){
                                $order->createDelivery($purchaseOrderProduct, $qty, $deliveryDate, $deliveryDescription, $targetWarehouseId);
                                $processed +=$qty;
                            }
                        }

                        if (isset($itemsDeliveredDefect[$productId])){

                            //check develiery datas
                            $defectQty = (int) (isset($itemsDeliveredDefect[$productId]) ? $itemsDeliveredDefect[$productId] : 0);

                            if ($defectQty == 0)
                                continue;

                            if ($defectQty > 0){
                                if($defectWarehouseId && $defectWarehouseId>0){
                                    $order->createDelivery($purchaseOrderProduct, $defectQty, $deliveryDate, $deliveryDescription, $defectWarehouseId);
                                    $processed +=$defectQty;
                                }else{
                                    throw new Exception(mage::helper('purchase')->__('Defect delivery was not set - defect delivery aborted'));
                                }
                            }
                        }
                    }
                }

                if($processed){

                    //in all case
                    $order->setpo_status(MDN_Purchase_Model_Order::STATUS_WAITING_FOR_DELIVERY);

                    if ($order->isCompletelyDelivered()){
                        $order->setpo_status(MDN_Purchase_Model_Order::STATUS_COMPLETE);
                    }

                    $order->save();

                    $purchaseOrderUpdater->checkForChangesAndLaunchUpdates($order);
                }

                return $processed;

            }else{
                throw new Exception(mage::helper('purchase')->__('This purchase order does not exists anymore'));
            }
    }


    //------------------------------ READ


    /**
     * Return the global ERP options that can impact on a tiers application using this API
     *
     * @return type
     */
    public function getErpOptions(){

        $options = array();

        //Purchase
        $options['purchase_product_packaging'] = (int) Mage::getStoreConfig('purchase/packaging/enable');
        $options['purchase_defect_deliveries'] = (int) Mage::getStoreConfig('purchase/purchase_order/enable_defect_delivery');

        //advancedStock
        $options['advancedstock_multiple_barcode'] = (int) Mage::helper('AdvancedStock/Product_Barcode')->useStandardErpBarcodeManagement();

        //order prepration
        $options['orderpreparation_scan_serials'] = (int) Mage::getStoreConfig('orderpreparation/packing/scan_serials');
        $options['orderpreparation_display_current_group'] = (int) Mage::getStoreConfig('orderpreparation/packing/display_current_group_only');
        $options['orderpreparation_ask_for_weigth'] = (int) Mage::getStoreConfig('orderpreparation/packing/ask_for_weight');
        $options['orderpreparation_ask_for_parcel_count'] = (int) Mage::getStoreConfig('orderpreparation/packing/ask_for_parcel_count');

        return $options;
    }





    /**
     * return product SKU by EAN
     * @param type $barcode
     * @return boolean
     */
    public function skuByEan($barcode)
    {
        $sku = '';

        $product = mage::helper('AdvancedStock/Product_Barcode')->getProductFromBarcode(trim($barcode));
        if($product->getId()>0){
            $sku = $product->getsku();
        }
        return $sku;
    }


    /**
     * return product Infos by EAN
     * @param $ean
     * @return boolean
     */
    public function productInfosByEan($ean)
    {
        $product = mage::helper('AdvancedStock/Product_Barcode')->getProductFromBarcode(trim($ean));
        return $this->productInfos($product);
    }

    /**
     * return product Infos by id
     *
     * @param $id
     * @return array
     */
    public function productInfosById($id)
    {
        $product = mage::getModel('Catalog/Product')->load($id);
        return $this->productInfos($product);
    }

    /**
     * return product Infos
     *
     * @param $product
     * @return array
     */
    private function productInfos($product)
    {
        $sku = '';
        $name = '';
        $id = '';
        $ean = array();

        if($product->getId()>0){
            $sku = $product->getsku();
            $name = $product->getname();
            $id = $product->getId();
            $ean = $this->getBarcodesFromProductId($id);
        }

        $results = array('id' => $id, 'ean' => $ean, 'sku' => $sku, 'name' => $name);

        return $results;
    }

    private function getBarcodesFromProductId($id)
    {
        $ean = array();

        $collection = Mage::helper('AdvancedStock/Product_Barcode')->getBarcodesForProduct($id);

        foreach ($collection as $item) {
            $ean[] = $item->getppb_barcode();
        }

        return $ean;
    }



    /**
     * Return product Image URL or data in base 64
     *
     * @param type $productId
     * @param type $getBase64
     * @return type
     */
    public function getProductImage($productId, $getBase64 = false, $resize = false, $width = 50, $height=50){

        $imageBase64 = '';
        $imageUrl = '';
        $image_format = 'unknown';

        $imageMagentoPath = Mage::getBaseUrl('media') . DS . 'catalog' . DS . 'product';

        if($productId && $productId>0){

            $product = Mage::getModel('catalog/product')->load($productId);

            if($product && $product->getId() > 0){
                if ($product->getSmallImage()) {
                    $imageUrl = $imageMagentoPath . $product->getSmallImage();
                } else {
                    //try to find image from configurable product
                    $configurableProduct = Mage::helper('AdvancedStock/Product_ConfigurableAttributes')->getConfigurableProduct($product);
                    if ($configurableProduct) {
                        if ($configurableProduct->getSmallImage()) {
                            $imageUrl = $imageMagentoPath . $configurableProduct->getSmallImage();
                        }
                    }
                }
            }
        }

        if(strlen($imageUrl)>0){

            //detect image format
            $image_info = getimagesize($imageUrl);

            //detect
            if($image_info && is_array($image_info)){
                $image_type = $image_info[2];

                if( $image_type == IMAGETYPE_JPEG )
                    $image_format = 'jpg';

                if( $image_type == IMAGETYPE_GIF )
                    $image_format = 'gif';

                if( $image_type ==  IMAGETYPE_PNG )
                    $image_format = 'png';
            }

            //mode Base64
            if($getBase64){

                $imageDataToSend = null;

                //only application is resize size required is consistant
                if($resize && $width > 10 && $height >10){
                    try{
                        $imageDataToResize = null;

                        if($image_info && is_array($image_info)){

                            if( $image_type == IMAGETYPE_JPEG )
                                $imageDataToResize = imagecreatefromjpeg($imageUrl);

                            if( $image_type == IMAGETYPE_GIF )
                                $imageDataToResize = imagecreatefromgif($imageUrl);

                            if( $image_type ==  IMAGETYPE_PNG )
                                $imageDataToResize = imagecreatefrompng($imageUrl);

                            $originalWidth = imagesx($imageDataToResize);
                            $originalHeight = imagesy($imageDataToResize);

                            $imageDataToSend = imagecreatetruecolor($width, $height);
                            imagecopyresampled($imageDataToSend, $imageDataToResize, 0, 0, 0, 0, $width, $height, $originalWidth, $originalHeight);

                            $imageLocalUrl = Mage::getBaseDir().DS.'var'.DS.'tmp_file_for_resize_'.$productId;

                            if( $image_type == IMAGETYPE_JPEG ){
                                $compression = 75;
                                $output = imagejpeg($imageDataToSend, $imageLocalUrl ,$compression);
                            }

                            if( $image_type == IMAGETYPE_GIF )
                                $output = imagegif($imageDataToSend, $imageLocalUrl);

                            if( $image_type ==  IMAGETYPE_PNG )
                                $output = imagepng($imageDataToSend, $imageLocalUrl);

                            unset($imageDataToResize);
                            unset($image_info);

                            $imageDataToSend = @file_get_contents($imageLocalUrl);
                        }
                    }catch(Exception $ex){
                        //resize skipped for any reason, return normal image
                        $imageDataToSend = @file_get_contents($imageUrl);
                    }

                }else{
                    $imageDataToSend = @file_get_contents($imageUrl);
                }

                if($imageDataToSend)
                    $imageBase64 = base64_encode($imageDataToSend);
            }
        }

        return array('url'=> $imageUrl, 'ext' => $image_format , 'base64'=>$imageBase64 );
    }

    /**
     * return product warehouse slist for a product
     * @param type $barcode
     * @return boolean
     */
    public function productWarehouseList($id)
    {

        $results = array();

        $collection = mage::helper('AdvancedStock/Product_Base')->getStocks($id);
        foreach($collection as $si){

            $results[] = array(
                'id' => (int)$si->getstock_id(),
                'name' => $this->formatStringForSoap($si->getstock_name()),
                'qty' => (int)$si->getqty(),
                'availableQty' => (int)$si->getAvailableQty(),
                'warningStockLevel' => (int)$si->getWarningStockLevel(),
                'useDefaultwarningStockLevel' => (int)$si->getuse_config_notify_stock_qty(),
                'idealStockLevel' => (int)$si->getIdealStockLevel(),
                'useDefaultIdealStockLevel' => (int)$si->getuse_config_ideal_stock_level(),
                'shelfLocation' => $this->formatStringForSoap($si->getshelf_location()));
        }

        return $results;
    }

      /**
     * return product purchase Sales Orders list for a product
     * @param type $barcode
     * @return boolean
     */
    public function productPurchaseOrdersList($id, $limit = self::DEFAULT_LIST_LIMIT)
    {
        $results = array();

        $collection = mage::getModel('Purchase/OrderProduct')
			->getCollection()
			->addFieldToFilter('pop_product_id', $id)
			->join('Purchase/Order','po_num=pop_order_num')
			->join('Purchase/Supplier','po_sup_num=sup_id');

         $count = 0;

         if($limit == null || $limit = 0 ||  $limit < 0){
             $limit = self::DEFAULT_LIST_LIMIT;
         }

         foreach($collection as $si){

            $count++;

            if($count > $limit)
                break;

            $results[] = array(
                'po_num' => (int)$si->getpo_num(),
                'po_order_id' => $si->getpo_order_id(),
                'po_date' => $si->getpo_date(),
                'po_supply_date' => $si->getpo_supply_date(),
                'sup_name' => $this->formatStringForSoap($si->getsup_name()),
                'pop_qty' => (int)$this->purchaseOrderGetOrderedQty($id,$si),
                'pop_supplied_qty' => (int)$this->purchaseOrderGetSuppliedQty($id,$si),
                'pop_price_ht_base' => $si->getpop_price_ht_base(),
                'po_paid' => (int)$si->getpo_paid(),
                'po_status' => $si->getpo_status()
            );
        }


        return $results;

    }

    public function purchaseOrderGetSuppliedQty($productId,$si)
    {
        $value = $si->getpop_supplied_qty();

        //convert units if packagings enabled in sales unit
        if (mage::helper('purchase/Product_Packaging')->isEnabled())
        {
            $value = mage::helper('purchase/Product_Packaging')->convertToSalesUnit($productId, $value);
        }

    	return $value.'';
    }

    public function purchaseOrderGetOrderedQty($productId,$si)
    {
        $value = $si->getpop_qty();

        //convert units if packagings enabled in sales unit
        if (mage::helper('purchase/Product_Packaging')->isEnabled())
        {
            $value = mage::helper('purchase/Product_Packaging')->convertToSalesUnit($productId, $value);
        }

    	return $value.'';
    }


       /**
     * return product pending Sales Orders list for a product
     * @param type $barcode
     * @return boolean
     */
    public function productPendingSalesOrdersList($id, $limit = self::DEFAULT_LIST_LIMIT)
    {

        $results = array();

        $collection = mage::helper('AdvancedStock/Product_Base')->GetPendingOrders($id, false);

        $count = 0;

        if($limit == null || $limit = 0 ||  $limit < 0){
             $limit = self::DEFAULT_LIST_LIMIT;
        }

        foreach($collection as $si){

            $count++;

            if($count > $limit)
                break;

            $results[] = array(
                'entity_id' => (int)$si->getentity_id(),
                'increment_id' => $si->getincrement_id(),
                'created_at' => $si->getcreated_at(),
                'billing_name' => $this->formatStringForSoap($si->getbilling_name()),
                'grand_total' => $si->getgrand_total(),
                'ordered_qty' => (int)$this->getOrderItemQty('ordered_qty', $si->getentity_id(), $id),
                'reserved_qty' => (int)$this->getOrderItemQty('reserved_qty', $si->getentity_id(), $id),
                'shipped_qty' => (int)$this->getOrderItemQty('shipped_qty', $si->getentity_id(), $id),
                'remaining_qty' => (int)$this->getOrderItemQty('remaining_qty', $si->getentity_id(), $id),
                'is_valid' => (int)$si->getis_valid(),
                'status' => $this->formatStringForSoap($si->getstatus())
            );
        }


        return $results;
    }

    private function getOrderItemQty($type, $orderId,$productId){

        $collection = mage::getModel('sales/order_item')
    						->getCollection()
    						->addFieldToFilter('order_id', $orderId)
    						->addFieldToFilter('product_id', $productId);

    	//return value
    	$retour = 0;
    	switch ($type) {
    		case 'ordered_qty':
		    	foreach ($collection as $item)
		    	{
		    		$retour += (int)$item->getqty_ordered();
		    	}
    			break;
    		case 'shipped_qty':
		    	foreach ($collection as $item)
		    	{
		    		$retour += (int)$item->getRealShippedQty();
		    	}
    			break;
    		case 'remaining_qty':
		    	foreach ($collection as $item)
		    	{
		    		$retour += $item->getRemainToShipQty();
		    	}
    			break;
    		case 'reserved_qty':
		    	foreach ($collection as $item)
		    	{
		    		$retour += (int)$item->getreserved_qty();
		    	}
    			break;
            case 'qty_invoiced':
		    	foreach ($collection as $item)
		    	{
		    		$retour += (int)$item->getqty_invoiced();
		    	}
    			break;
    	}

    	if ($retour == '')
			$retour = '0';

		return $retour;
    }

    /**
     * return product Stock movements list for a product
     * @param type $barcode
     * @return boolean
     */
    public function productStockMovementList($id, $limit = self::DEFAULT_LIST_LIMIT)
    {

        $results = array();

        //to be able to send warehouse name too
        $warehouseNames = array();
        $warehouseCollection = mage::getModel('AdvancedStock/Warehouse')->getCollection();
        foreach($warehouseCollection as $warehouse){
            $warehouseNames[$warehouse->getstock_id()] = $warehouse->getstock_name();
        }

        $collection = mage::getModel('AdvancedStock/StockMovement')
        					->getCollection()
        					->addFieldToFilter('sm_product_id', $id);

        $count = 0;

        if($limit == null || $limit = 0 ||  $limit < 0){
             $limit = self::DEFAULT_LIST_LIMIT;
        }

        foreach($collection as $sm){

            $count++;

            if($count > $limit)
                break;

            $results[] = array(
                'sm_id' => (int)$sm->getsm_id(),
                'sm_date' => $sm->getsm_date(),
                'sm_source_stock' => (int)$sm->getsm_source_stock(),
                'sm_source_stock_name' => $warehouseNames[$sm->getsm_source_stock()],
                'sm_target_stock' => (int)$sm->getsm_target_stock(),
                'sm_target_stock_name' => $warehouseNames[$sm->getsm_target_stock()],
                'sm_type' => $sm->getsm_type(),
                'sm_qty' => (int)$sm->getsm_qty(),
                'sm_description' => $this->formatStringForSoap($sm->getsm_description()),
                'sm_ui' => (int)$sm->getsm_ui(),
                'sm_po_num' => (int)$sm->getsm_po_num()
            );

        }


        return $results;
    }



    /**
     * Check if a sku eixts
     * @param type $sku
     * @return boolean
     */
    public function skuExists($sku)
    {
        $productId = Mage::getModel('catalog/product')->getIdBySku($sku);
        if ($productId  >0)
            return true;
        else
            return false;
    }


    //PURCHASE --- a segmenter



    public function supplierList()
    {

        $results = array();

        $collection = Mage::getModel('Purchase/Supplier')
                        ->getCollection();


        foreach($collection as $sm){
            $results[] = array(
                'sup_id' => (int)$sm->getsup_id(),
                'sup_code' => $this->formatStringForSoap($sm->getsup_code()),
                'sup_name' => $this->formatStringForSoap($sm->getsup_name()),
                'sup_currency' => $sm->getsup_currency(),
                'sup_mail' => $this->formatStringForSoap($sm->getsup_mail())
            );
        }

        return $results;
    }

    public function purchaseOrdersBySupplierId($supplierId,  $limit = self::DEFAULT_LIST_LIMIT)
    {

        $results = array();

        $warehouseNames = array();
        $warehouseCollection = mage::getModel('AdvancedStock/Warehouse')->getCollection();
        foreach($warehouseCollection as $warehouse){
            $warehouseNames[$warehouse->getstock_id()] = $warehouse->getstock_name();
        }

        $collection = Mage::getModel('Purchase/Order')
                        ->getCollection()
                        ->addFieldToFilter('po_sup_num', $supplierId);

        $count = 0;

        if($limit == null || $limit == 0 ||  $limit < 0){
             $limit = self::DEFAULT_LIST_LIMIT;
        }

        foreach($collection as $sm){

            $count++;

            if($count > $limit)
                break;

            $results[] = array(
                'po_num' => (int)$sm->getpo_num(),
                'po_order_id' => $this->formatStringForSoap($sm->getpo_order_id()),
                'po_date' => $sm->getpo_date(),
                'po_supply_date' => $sm->getpo_supply_date(),
                'po_status' => $this->formatStringForSoap($sm->getpo_status()),
                'po_target_warehouse_id' => (int)$sm->getpo_target_warehouse(),
                'po_target_warehouse_name' => $warehouseNames[$sm->getpo_target_warehouse()],
                'po_status' => $this->formatStringForSoap($sm->getpo_status()),
                'po_delivery_percent' => (int)$sm->getpo_delivery_percent(),
                'po_is_locked' => (int)$sm->getpo_is_locked(),
                'po_paid' => (int)$sm->getpo_paid(),
            );
        }

        return $results;
    }

    public function purchaseOrdersProducts($purchaseOrderId,$targetWarehouseId = 1)
    {
        $results = array();

        $packagingEnabled = mage::helper('purchase/Product_Packaging')->isEnabled();

        if($purchaseOrderId>0){
            $po = Mage::getModel('Purchase/Order')->load($purchaseOrderId);
            if($po){
                $collection = $po->getProducts();
                foreach($collection as $pop){
                    $productId = $pop->getpop_product_id();
                    $shelfLocation = '';

                    //packaging infos
                    $selectedPackagingLabel = '';
                    $selectedPackagingQty = 1;
                    if($packagingEnabled){
                        foreach (mage::helper('purchase/Product_Packaging')->getPackagingForProduct($productId) as $packaging) {
                            if($packaging->getId() == $pop->getpop_packaging_id()){
                                $selectedPackagingLabel = $packaging->getpp_name();
                                $selectedPackagingQty = $packaging->getpp_qty();
                                break;
                            }
                        }
                    }

                    //get shelf location if possible
                    if($targetWarehouseId>0){
                        $stockItem = $collection = mage::getModel('cataloginventory/stock_item')
                            ->getCollection()
                            ->addFieldToFilter('product_id', $productId)
                            ->addFieldToFilter('stock_id', $targetWarehouseId)
                            ->getFirstItem();
                        if($stockItem){
                            $shelfLocation = $stockItem->getshelf_location();
                        }
                    }

                    //$barcode = Mage::helper('AdvancedStock/Product_Barcode')->getBarcodeForProduct($productId);
                    $barcodes = $this->getBarcodesFromProductId($productId);
                    $results[] = array(
                        'pop_product_id' => (int)$productId,
                        'pop_product_name' => $this->formatStringForSoap($pop->getpop_product_name()),
                        'pop_product_sku' => $this->formatStringForSoap($pop->getsku()),
                        'pop_product_ean' => $barcodes,
                        'pop_product_supplier_ref' => $this->formatStringForSoap($pop->getpop_supplier_ref()),
                        'pop_product_ordered_qty' => (int)$pop->getpop_qty(),
                        'pop_product_delivered_qty' => (int)$pop->getSuppliedQty(),
                        'pop_product_price_ht' => (int)$pop->getpop_price_ht(),
                        'pop_product_eco_tax' => (int)$pop->getpop_eco_tax(),
                        'pop_product_discount' => (int)$pop->getpop_discount(),
                        'pop_product_tax_rate' => (int)$pop->getpop_tax_rate(),
                        'pop_product_packaging_enabled' => (int)$packagingEnabled,
                        'pop_product_packaging_id' => (int)$pop->getpop_packaging_id(),
                        'pop_product_packaging_name' => $this->formatStringForSoap($selectedPackagingLabel),
                        'pop_product_packaging_qty' => (int)$selectedPackagingQty,
                        'pop_product_target_shelf_location' => $this->formatStringForSoap($shelfLocation)
                    );
                }
            }
        }
        return $results;
    }

    public function getWarehouseList()
    {

        $results = array();

        $collection = mage::getModel('AdvancedStock/Warehouse')->getCollection();

        foreach($collection as $w){

            $results[] = array(
                'stock_id' => (int)$w->getstock_id(),
                'stock_name' => $this->formatStringForSoap($w->getstock_name()),
                'stock_code' => $this->formatStringForSoap($w->getstock_code()),
                'stock_address' => $this->formatStringForSoap($w->getstock_address()),
                'stock_description' => $this->formatStringForSoap($w->getstock_description()),
                'stock_disable_supply_needs' => (int)$w->getstock_disable_supply_needs()
            );
        }

        return $results;
    }


    /*
     *  ----------------- ORDER PREPARATION ---------------------------------S
     */

    // --------------- READ


    /**
     * Get the list of the fullstock order preparation orders
     *
     * @param int $preparationWarehouseId
     * @return array
     */
    public function getFullStockOrders($preparationWarehouseId)
    {
        return $this->getOrderPreparationOrders($preparationWarehouseId, 'fullstock');
    }

    /**
     * Get the list of the stockless order preparation orders
     *
     * @param int $preparationWarehouseId
     * @return array
     */
    public function getStocklessOrders($preparationWarehouseId)
    {
        return $this->getOrderPreparationOrders($preparationWarehouseId, 'stockless');
    }

    /**
     * Get the list of the ignored order preparation orders
     *
     * @param int $preparationWarehouseId
     * @return array
     */
    public function getIgnoredOrders($preparationWarehouseId)
    {
        return $this->getOrderPreparationOrders($preparationWarehouseId, 'ignored');
    }


    /**
     * Get the list of the order preparation orders
     *
     * @param int $preparationWarehouseId
     * @param string $type
     * @return array
     */
    private function getOrderPreparationOrders($preparationWarehouseId, $type)
    {
        $orders = array();

        if($preparationWarehouseId && $preparationWarehouseId>0 && !empty($type)){

            $collection = mage::getModel('Orderpreparation/ordertopreparepending')
                            ->getCollection()
                            ->addFieldToFilter('opp_type', $type)
                            ->addFieldToFilter('opp_preparation_warehouse', $preparationWarehouseId);

            foreach($collection as $opp){

                $incrementId = $opp->getopp_order_increment_id();

                if(!empty($incrementId)){

                    $order = Mage::getModel('sales/order')->loadByIncrementId($incrementId);

                    if( $order->getId()>0){

                        $o = array();
                        $o['order_entity_id'] = (int)$order->getId();
                        $o['order_increment_id'] = (string)$opp->getopp_order_increment_id();
                        $o['order_date'] = (string)$order->getcreated_at();
                        $o['order_state'] = (string)$order->getstate();
                        $o['order_status'] = (string)$order->getstatus();
                        $o['order_type'] = (string)$type;//optim for remote processing
                        $o['order_shipto_name'] = (string)$opp->getopp_shipto_name();
                        $o['order_shipping_method'] = (string)$opp->getopp_shipping_method();

                        //get products
                        $o['products'] = $this->getRemainToShipOrderItems($o['order_entity_id'],$preparationWarehouseId);
                        $orders[] = $o;
                    }
                }
            }
        }

        return $orders;
    }

    /**
     * Get the list of the order preparation orders
     *
     * @param int $preparationWarehouseId
     * @param string $type
     * @return array
     */
    public function getSelectedOrders($preparationWarehouseId, $operatorId)
    {
        $orders = array();

        if($preparationWarehouseId && $preparationWarehouseId>0 && $operatorId && $operatorId>0){

            $collection = mage::getModel('Orderpreparation/ordertoprepare')
                            ->getCollection()
                            ->addFieldToFilter('user', $operatorId)
                            ->addFieldToFilter('preparation_warehouse', $preparationWarehouseId);


            foreach($collection as $otp){

                $oid = $otp->getorder_id();

                if(!empty($oid)){

                    $order = Mage::getModel('sales/order')->load($oid);

                    if($order->getId()>0){

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
                        $o = array();
                        $o['order_entity_id'] = (int)$order->getId();
                        $o['order_increment_id'] = (string)$order->getIncrementId();
                        $o['order_date'] = (string)$order->getcreated_at();
                        $o['order_state'] = (string)$order->getstate();
                        $o['order_status'] = (string)$order->getstatus();
                        $o['order_type'] = (string)'selected';//optim for remote processing
                        $o['order_shipto_name'] = (string)$ShipToName;
                        $o['order_shipping_method'] = (string)$order->getshipping_description();

                        //append products
                        $o['products'] = $this->getRemainToShipOrderItems($o['order_entity_id'], $preparationWarehouseId);
                        $orders[] = $o;
                    }
                }
            }
        }

        return $orders;
    }


    /**
     * Get the count for each order preparation tab
     *
     * @param int $preparationWarehouseId
     * @param string $type
     * @return array
     */
    public function getOrderPreparationTabCount($preparationWarehouseId,$operatorId=1)
    {
        $counts = array();

        if($preparationWarehouseId>0 && $operatorId>0){

            $counts['fullstock'] = (int)mage::getModel('Orderpreparation/ordertoprepare')->countOrders('fullstock',$preparationWarehouseId,$operatorId);
            $counts['stockless'] = (int)mage::getModel('Orderpreparation/ordertoprepare')->countOrders('stockless',$preparationWarehouseId,$operatorId);
            $counts['ignored'] = (int)mage::getModel('Orderpreparation/ordertoprepare')->countOrders('ignored',$preparationWarehouseId,$operatorId);
            $counts['selected'] = (int)mage::getModel('Orderpreparation/ordertoprepare')->countOrders('selected',$preparationWarehouseId,$operatorId);
        }

        return $counts;
    }



    /**
     * Get the Remain to ship products Infos of an order in order prepration screen
     *
     * @param int $orderId
     * @param int $warehouseId
     * @return array
     */
    public function getRemainToShipOrderItems($orderId, $warehouseId)
    {
        $remainToShipOrderItems = array();

        if($warehouseId != null && $warehouseId >0 && $orderId != null && $orderId >0 ){

            $order = Mage::getModel('sales/order')->load($orderId);

            if($order && $order->getId() > 0){

                foreach ($order->getItemsCollection() as $item) {

                    if ($warehouseId != $item->getpreparation_warehouse())
                        continue;

                    $productId = $item->getproduct_id();
                    $remainingQty = $item->getRemainToShipQty();
                    $reservedQty = $item->getreserved_qty();

                    if($productId > 0){

                        $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProductWarehouse($productId, $warehouseId);

                        $color = '#000000';//black
                        if ($remainingQty > 0) {
                            if ($stockItem && $stockItem->getManageStock()) {
                                if ($reservedQty >= $remainingQty) {
                                    $color = '#00FF00';//green
                                } else {
                                    if (($reservedQty < $remainingQty) && ($reservedQty > 0)) {
                                        $color = '#FF9900';//orange
                                    } else {
                                        $color = '#FF0000';//red
                                    }
                                }
                            }
                            else
                                $color = '#808080';//red
                        }

                        $ean = $this->getBarcodesFromProductId($productId);
                        /*$barcodeCollection = mage::helper('AdvancedStock/Product_Barcode')->getBarcodesForProduct($productId);
                        foreach($barcodeCollection as $barcodeCollectionItem) {
                            $ean = trim($barcodeCollectionItem->getppb_barcode());
                            break;
                        }*/
                        $shelfLocation = $stockItem ? $stockItem->getshelf_location() : ' ';
                        $p = array();

                        $p['product_id'] = (int)$productId;
                        $p['product_sku'] = (string)$item->getsku();
                        $p['product_ean'] = $ean;
                        $p['product_type'] = (string)$item->getProductType();
                        $p['product_sales_item_id'] = (int)$item->getitem_id();
                        $p['product_parent_item_id'] = (int)$item->getparent_item_id();
                        $p['product_name'] = (string) trim($item->getName());
                        $p['product_qty_to_ship'] = (int)$remainingQty;
                        $p['product_qty_reserved'] = (int)$reservedQty;
                        $p['product_display_color'] = (string)$color;
                        $p['product_shelf_location'] = (string)$shelfLocation;

                        $remainToShipOrderItems[] = $p;
                    }
                }
            }
        }
        return $remainToShipOrderItems;
    }


    // --------------- WRITE

    /**
     * Commit a packing and selected the order if it is possible
     *
     * @param type $orderId
     * @param type $preparationWarehouseId
     * @param type $operatorId
     * @return boolean
     */
    public function commitPacking($orderId,$warehouseId,$operatorId = 1){

        $success = false;

        if(   $orderId != null     && $orderId > 0 
	   && $warehouseId != null && $warehouseId > 0 
	   && $operatorId != null  && $operatorId > 0 ){

            //set selected order ro perator and warehouse id in parameter
            $session = Mage::getSingleton('adminhtml/session');
            $session->setData('op_operator', $operatorId);
            $session->setData('op_preparation_warehouse', $warehouseId);

            $isAllreadySelected = false;

            $allreadyAdded = mage::getModel('Orderpreparation/ordertoprepare')
                ->getCollection()
                ->addFieldToFilter('order_id', $orderId)
                ->addFieldToFilter('user', Mage::helper('Orderpreparation')->getOperator())
                ->addFieldToFilter('preparation_warehouse', mage::helper('Orderpreparation')->getPreparationWarehouse())
                ->getFirstItem();

            if($allreadyAdded && $allreadyAdded->getId()){
                 $isAllreadySelected = true;
            }

            $toPack = false;

            //this tets exist to diffrenciate the possibility to select and the possibility to pack
            if($isAllreadySelected){
                //If the order is allready select, nobody will select it, so we can go on
                $toPack = true;
            }else{
                //if the order is not yet selected, we have to test if the order has not been preprared by someone else, to avoid double packing
                $toPack = Mage::getModel('Orderpreparation/ordertoprepare')->AddSelectedOrder($orderId);
            }

            if($toPack){
                $order = Mage::getModel('sales/order')->load($orderId);

		if($order->getId()>0)
		{    
			if (Mage::getStoreConfig('orderpreparation/packing/create_shipment_on_commit')) {

			    Mage::helper('Orderpreparation/Shipment')->CreateShipment($order, $warehouseId, $operatorId);

			    //create invoice (if set)
			    if (mage::getStoreConfig('orderpreparation/packing/create_invoice_on_commit') == 1) {
				if (!Mage::helper('Orderpreparation/Invoice')->InvoiceCreatedForOrder($orderId)) {
				    Mage::helper('Orderpreparation/Invoice')->CreateInvoice($order);
				}
			    }

			    $success = true;
			}
		}else{
               		throw new Exception('Order #'.$orderId.' has been deleted !');
            	}
            }else{
               throw new Exception('Order #'.$orderId.' has allready been packed !');
            }
        }


        return $success;
    }

    /**
     * Add to select order
     *
     * @param type $orderId
     * @param type $preparationWarehouseId
     * @param type $operatorId
     * @return boolean
     */
    public function addToSelectedOrders($orderId,$warehouseId,$operatorId = 1){

        $success = false;

        if($orderId != null && $orderId >0 && $orderId != null && $orderId >0 && $operatorId != null && $operatorId >0){

            //set selected order ro perator and warehouse id in parameter
            $session = Mage::getSingleton('adminhtml/session');
            $session->setData('op_operator', $operatorId);
            $session->setData('op_preparation_warehouse', $warehouseId);

            //select order
            $success = Mage::getModel('Orderpreparation/ordertoprepare')->AddSelectedOrder($orderId);
        }

        return $success;
    }


    /**
     * Remove to select order
     *
     * @param type $orderId
     * @param type $preparationWarehouseId
     * @param type $operatorId
     * @return boolean
     */
    public function removeFromSelectedOrders($orderId,$warehouseId,$operatorId = 1){

        $success = false;

        if($orderId != null && $orderId >0 && $orderId != null && $orderId >0 && $operatorId != null && $operatorId >0){

            //set selected order ro perator and warehouse id in parameter
            $session = Mage::getSingleton('adminhtml/session');
            $session->setData('op_operator', $operatorId);
            $session->setData('op_preparation_warehouse', $warehouseId);

            //select order
            Mage::getModel('Orderpreparation/ordertoprepare')->RemoveSelectedOrder($orderId);
            $success = true;
        }

        return $success;
    }

    /**
     * Return the
     * - current tab name (stockless, ignored of fullstock) -> if the order is available for preparation
     * - or the operator name (which Mean the Order is selected by someone)
     * - or empty (the order can't be preparred (complete, cancelled ...)
     *
     * @param type $orderId
     * @param type $warehouseId
     * @return type
     */
    public function getCurrentOrderPreparationTab($orderId,$warehouseId){

        $result = '';

        $currentTab = mage::helper('Orderpreparation')->getCurrentOrderPreparationTab($orderId,$warehouseId);
        if($currentTab){
             $result = strtolower(trim($currentTab));
        }else{
            $operatorId = mage::helper('Orderpreparation')->getCurrentOrderPreparer($orderId,$warehouseId);
            if($operatorId){
                $operatorName = mage::helper('Orderpreparation')->getOperatorName($operatorId);
                if($operatorName){
                    $result = strtolower(trim($operatorName));
                }
            }
        }

        return $result;
    }

    public function finishSelectedOrders($ordersIds,$warehouseId,$operatorId){

        $successCount = 0;

        $session = Mage::getSingleton('adminhtml/session');
        $session->setData('op_operator', $operatorId);
        $session->setData('op_preparation_warehouse', $warehouseId);

        foreach($ordersIds as $orderId){

            $orderToPrepare = mage::helper('Orderpreparation')->getOrderToPrepareForCurrentContext($orderId);
            if($orderToPrepare){
                $orderToPrepare->delete();

                Mage::dispatchEvent('orderpreparartion_remove_selected_order', array('order_id' => $orderId));

                //Dispatch order
                $order = mage::getModel('sales/order')->load($orderId);
                mage::helper('Orderpreparation/Dispatcher')->DispatchOrder($order);
                $successCount ++;
            }
        }

        return $successCount;
    }

     public function finishSelectedOrder($orderId,$warehouseId,$operatorId){

        $success = false;

        $session = Mage::getSingleton('adminhtml/session');
        $session->setData('op_operator', $operatorId);
        $session->setData('op_preparation_warehouse', $warehouseId);


        $orderToPrepare = mage::helper('Orderpreparation')->getOrderToPrepareForCurrentContext($orderId);
        if($orderToPrepare){
            $orderToPrepare->delete();

            Mage::dispatchEvent('orderpreparartion_remove_selected_order', array('order_id' => $orderId));

            //Dispatch order
            $order = mage::getModel('sales/order')->load($orderId);
            mage::helper('Orderpreparation/Dispatcher')->DispatchOrder($order);
            $success =true;
        }


        return $success;
    }




    //-----------------PRINT

    const printDocPicking = 'picking_list';
    const printAllDocs = 'invoice_shipment';

    const modeMCC = 'mcc';



    public function printPickingListMCC($warehouseId,$userId){

        return $this->printDocumentbyType(self::printDocPicking, self::modeMCC, $warehouseId, $userId);

    }

    public function printDocumentsMCC($warehouseId,$userId){

        return $this->printDocumentbyType(self::printAllDocs, self::modeMCC, $warehouseId, $userId);

    }


    private function printDocumentbyType($type,$mode,$warehouseId,$userId){

        $printed = false;
        $pdf = null;
        $fileName = '';
        $label =  '';

        try {

            if($warehouseId >0 && $userId >0 ){

                //set context
                mage::helper('Orderpreparation')->setOperator($userId);
                mage::helper('Orderpreparation')->setPreparationWarehouse($warehouseId);

                //generate documents to print
                switch ($type){

                    case self::printAllDocs :
                         $fileName = 'documents.pdf';
                         $pdf = mage::helper('Orderpreparation/Documents')->generateDocumentsPdf();
                         $label = 'Order preparation : print documents';
                    break;

                    case self::printDocPicking :
                         $fileName = 'picking_list.pdf';
                         $pdf = mage::helper('Orderpreparation/PickingList')->getPdf();
                         $label = 'Order preparation : Print picking list';
                    break;

                }

                //print the requeted document depeding of the printing Mode
                if($pdf && $fileName && $label){

                     $userLogin = mage::getModel('admin/user')->load($userId)->getusername();

                     switch ($mode){

                         case self::modeMCC :
                            mage::helper('ClientComputer')->printDocument($pdf->render(), $fileName, $label, $userLogin);
                            $printed = true;
                         break;

                     }

                }
            }


        } catch (Exception $ex) {
            die("PDF printing error : " . $ex->getMessage() . '<p>' . $ex->getTraceAsString());
        }

        return $printed;
    }



    //-------------- STOCK TAKE

    /**
     * Get the list of all the stock takes with their id, status, date, name, and warehouse's name
     *
     * @return array
     */
    public function getStockTakesList() {

        $res = array();

        $collection = Mage::getModel('AdvancedStock/Inventory')
            ->getCollection()
            ->addFieldToFilter('ei_status', MDN_AdvancedStock_Model_Inventory::kStatusOpened);

        if ($collection) {
            foreach($collection as $inventory) {
                $inv = array();
                $inv['id'] = (int)$inventory->getId();
                $inv['status'] = $inventory->getei_status();
                $inv['date'] = $inventory->getei_date();
                $inv['title'] = $inventory->getei_name();
                $inv['warehouse'] = $inventory->getWarehouse()->getstock_name();
                $inv['mode'] = $inventory->getei_stock_take_mode();
                $inv['comments'] = $inventory->getei_comments();
                $inv['toscan'] = (int)$this->getStockTakeToScanCount($inventory->getId());

                $res[] = $inv;
            }
        }

        return $res;

    }


    private function getStockTakeToScanCount($inventoryId) {

        $prefix = Mage::getConfig()->getTablePrefix();
        $sql = 'select count(*) from '.$prefix.'erp_inventory_stock_picture where eisp_inventory_id = '.$inventoryId;

        $inv = mage::getResourceModel('AdvancedStock/Inventory_Collection')->getConnection()->query($sql)->fetch();
        return $inv['count(*)'];


    }

    /**
     * Create a new stock take
     *
     * @param $stockTakeName
     * @param $warehouseId
     * @return bool|string
     */
    public function createStockTake($stockTakeName, $warehouseId) {

        try{

            //Create the new Stock Take in DB

            $inventory = Mage::getModel('AdvancedStock/Inventory');
            $inventory->setei_warehouse_id($warehouseId)
                ->setei_name($stockTakeName)
                ->setei_status(MDN_AdvancedStock_Model_Inventory::kStatusOpened);
            $inventory->save();

            //Do a Stock Picture

            $inventory->updateStockPicture();

        } catch (Exception $e) {
            return $e->getMessage();
        }



        return (int)true;

    }

    /**
     * get the stock picture
     *
     * @param $stockTakeId
     * @return array
     */
    public function getStockPicture($stockTakeId)
    {
        $inventory = Mage::getModel('AdvancedStock/Inventory')->load($stockTakeId);
        $stockPicture = $inventory->getStockPicture();

        $res = array();

        foreach($stockPicture as $item)
        {
            $p['id'] = $item['entity_id'];
            $p['sku'] = $item['sku'];
            $p['ean'] = $this->getBarcodesFromProductId($item['entity_id']);
            $p['name'] = $item['name'];
            $p['scanned'] = (int)$item['eip_qty'];
            $p['expected'] = (int)$item['eisp_stock'];
            $p['location'] = $item['eisp_shelf_location'];

            $res[] = $p;
        }

        return $res;
    }

    public function saveStockTakeScan($stockTakeId, $location, $stockTakeProducts)
    {
        /** @var MDN_AdvancedStock_Model_Inventory $inventory */
        $inventory = Mage::getModel('AdvancedStock/Inventory')->load($stockTakeId);
        $stockTakeProducts = $this->convertStdClassToArray($stockTakeProducts);
        //save scanned products
        foreach ($stockTakeProducts as $productId => $qty) {
            $inventory->addScannedProduct($location, $productId, $qty);
        }

        return "1";
    }

    public function createStockMovement($sourceWarehouseId, $targetWarehouseId, $productId, $qty)
    {

        $additionnalData = array(
            'sm_type' => 'adjustment'
        );

        Mage::getModel('AdvancedStock/StockMovement')->createStockMovement(
            $productId,
            $sourceWarehouseId,
            $targetWarehouseId,
            $qty,
            'Adjustement from ErpDroid',
            $additionnalData
        );

        return "1";
    }

    //ACCOUNTING

    /**
     * Return all suppliers or the requested supplier by ID (TESTED)
     * @param int $supplierId
     * @return array of Suppliers Data
     */
    public function getSuppliers($supplierId = null){
        $results = array();

        $collection = mage::getModel('Purchase/Supplier')
            ->getCollection();

        if($supplierId) {
            $collection->addFieldToFilter('sup_id', $supplierId);
        }

        foreach($collection as $sup) {

            $results[] = array(

                'sup_id' => (int)$sup->getsup_id(),
                'sup_name' => $this->formatStringForSoap($sup->getsup_name()),
                'sup_code' => $this->formatStringForSoap($sup->getsup_code()),
                'sup_locale' => $this->formatStringForSoap($sup->getsup_locale()),

                'sup_address1' => $this->formatStringForSoap($sup->getsup_address1()),
                'sup_address2' => $this->formatStringForSoap($sup->getsup_address2()),
                'sup_state' => $this->formatStringForSoap($sup->getsup_state()),
                'sup_city' => $this->formatStringForSoap($sup->getsup_city()),
                'sup_zipcode' => $this->formatStringForSoap($sup->getsup_zipcode()),
                'sup_country' => $this->formatStringForSoap($sup->getsup_country()),

                'sup_tel' => $this->formatStringForSoap($sup->getsup_tel()),
                'sup_fax' => $this->formatStringForSoap($sup->getsup_fax()),

                'sup_contact' => $this->formatStringForSoap($sup->getsup_contact()),
                'sup_mail' => $this->formatStringForSoap($sup->getsup_mail()),

                'sup_website' => $this->formatStringForSoap($sup->getsup_website()),

                'sup_shipping_delay' => (int)$sup->getsup_shipping_delay(),
                'sup_supply_delay' => (int)$sup->getsup_supply_delay(),

                'sup_carrier' => $this->formatStringForSoap($sup->getsup_carrier()),
                'sup_comments' => $this->formatStringForSoap($sup->getsup_comments()),

                'sup_rma_mail' => $this->formatStringForSoap($sup->getsup_rma_mail()),
                'sup_rma_comments' => $this->formatStringForSoap($sup->getsup_rma_comments()),

                'sup_currency' => $this->formatStringForSoap($sup->getsup_currency()),
                'sup_payment_delay' => (int)$sup->getsup_payment_delay(),
                'sup_discount_level' => $sup->getsup_discount_level(),
                'sup_tax_rate' => $sup->getsup_tax_rate(),
                'sup_free_carriage_amount' => $sup->getsup_free_carriage_amount(),
                'sup_free_carriage_weight' => $sup->getsup_free_carriage_weight(),
            );
        }

        return $results;
    }

    public function getPurchaseOrders($poUpdatedAtFrom = null, $poUpdatedAtTo = null, $poSent = null,$poPaid = null, $poFinished = null){

        $results = array();

        $collection = mage::getModel('Purchase/Order')
            ->getCollection();

        if($poUpdatedAtFrom != null && strlen(trim($poUpdatedAtFrom))>0 ){
            $collection->addFieldToFilter('po_updated_at', array("gteq" => $poUpdatedAtFrom));
        }

        if($poUpdatedAtTo != null && strlen(trim($poUpdatedAtTo))>0 ){
            $collection->addFieldToFilter('po_updated_at', array("lteq" => $poUpdatedAtTo));
        }

        if($poSent != null && strlen(trim($poSent))>0 ){
            $collection->addFieldToFilter('po_sent', $poSent);
        }

        if($poPaid != null && strlen(trim($poPaid))>0 ){
            $collection->addFieldToFilter('po_paid', $poPaid);
        }

        if($poFinished != null && strlen(trim($poFinished))>0 ){
            $collection->addFieldToFilter('po_finished', $poFinished);
        }

        //return (string)$collection->getSelect()->assemble();

        foreach($collection as $po) {

            $results[] = array(

                'po_num' => $po->getpo_num(),
                'po_sup_num' => $po->getpo_sup_num(),
                'po_date' => $po->getpo_date(),
                'po_updated_at' => $po->getpo_updated_at(),
                'po_status' => $po->getpo_status(),
                'po_delivery_percent' => $po->getpo_delivery_percent(),
                'po_supply_date' => $po->getpo_supply_date(),
                'po_carrier' => $po->getpo_carrier(),
                'po_payment_type' => $po->getpo_payment_type(),
                'po_currency' => $po->getpo_currency(),
                'po_invoice_date' => $po->getpo_invoice_date(),
                'po_invoice_ref' => $po->getpo_invoice_ref(),
                'po_paid' => $po->getpo_paid(),
                'po_sent' => $po->getpo_sent(),
                'po_finished' => $po->getpo_finished(),
                'po_currency_change_rate' => $po->getpo_currency_change_rate(),
                'po_shipping_cost' => $po->getpo_shipping_cost(),
                'po_shipping_cost_base' => $po->getpo_shipping_cost_base(),
                'po_zoll_cost' => $po->getpo_zoll_cost(),
                'po_zoll_cost_base' => $po->getpo_zoll_cost_base(),
                'po_tax_rate' => $po->getpo_tax_rate(),
                'po_comments' => $po->getpo_comments(),
                'po_payment_date' => $po->getpo_payment_date(),
                'po_supplier_order_ref' => $po->getpo_supplier_order_ref(),
                'po_supplier_notification_date' => $po->getpo_supplier_notification_date(),
                'po_missing_price' => $po->getpo_missing_price(),
                'po_external_extended_cost' => $po->getpo_external_extended_cost(),
                'po_target_warehouse' => $po->getpo_target_warehouse(),
                'po_is_locked' => $po->getpo_is_locked(),
                'po_default_product_discount' => $po->getpo_default_product_discount(),

            );
        }

        return $results;

    }

    /**
     * Get purchase order products for a given Purchase order ID (Tested)
     *
     * @param $poId
     * @return array
     */
    public function getPurchaseOrdersProducts($poId){
        $results = array();

        $collection = mage::getModel('Purchase/OrderProduct')
            ->getCollection()
            ->addFieldToFilter('pop_order_num', $poId)
            ->join('Purchase/Order','po_num=pop_order_num')
            ->join('Purchase/Supplier','po_sup_num=sup_id');



        foreach($collection as $si){

            $pid = $si->getpop_product_id();

            $results[] = array(

                //PO
                'po_num' => (int)$si->getpo_num(),
                'po_order_id' => $si->getpo_order_id(),

                //PRODUCT
                'pop_product_id' => (int)$pid,
                'pop_product_name' => $this->formatStringForSoap($si->getpop_product_name()),

                'pop_qty' => (int)$this->purchaseOrderGetOrderedQty($pid,$si),
                'pop_supplied_qty' => (int)$this->purchaseOrderGetSuppliedQty($pid,$si),

                'pop_price_ht' => $si->getpop_price_ht(),
                'pop_price_ht_base' => $si->getpop_price_ht_base(),

                'pop_eco_tax' => $si->getpop_eco_tax(),
                'pop_eco_tax_base' => $si->getpop_eco_tax_base(),
                'pop_tax_rate' => $si->getpop_tax_rate(),

                'pop_extended_costs' => $si->getpop_extended_costs(),
                'pop_extended_costs_base' => $si->getpop_extended_costs_base(),

                'pop_packaging_id' => (int)$si->getpop_packaging_id(),
                'pop_packaging_value' => $si->getpop_packaging_value(),
                'pop_packaging_name' => $this->formatStringForSoap($si->getpop_packaging_name()),

                'pop_weight' => $si->getpop_weight(),
                'pop_discount' => $si->getpop_discount(),
                'pop_delivery_date' => $si->getpop_delivery_date(),
            );

        }


        return $results;

    }

}

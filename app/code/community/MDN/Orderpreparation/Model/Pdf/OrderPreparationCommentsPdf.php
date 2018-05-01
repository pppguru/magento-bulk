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
 * @copyright  Copyright (c) 2013 Maison du Logiciel (http://www.maisondulogiciel.com)
 * @author : Olivier ZIMMERMANN
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MDN_Orderpreparation_Model_Pdf_OrderPreparationCommentsPdf extends MDN_Orderpreparation_Model_Pdf_Pdfhelper {

    protected $_currentOrderId = null;
    protected $_alreadyDisplayedProducts = array();

    // ORDER BY ORDER PICKING LIST

    const MODE_ALL = 'ALL'; // Print button from the Tab Prepration from Sales -> Order -> Select an order
    const MODE_ORDER_PREPRATION_NOT_SELECTED_TAB = 'NOT_SELECTED'; // case massDownloadPreparationPdfAction from Other Tab that the "Selected tab"
    const MODE_ORDER_PREPRATION_SELECTED_TAB = 'SELECTED'; //case DownloadDocument Button + Picking List Button when order by order mode is set in options

    CONST X_POS_IMAGE = 10;
    CONST X_POS_BARCODE = 60;
    CONST X_POS_SKU = 150;
    CONST X_POS_SHELF_LOCATION = 210;
    CONST X_POS_NAME = 280;
    CONST X_POS_PARENT_NAME = 280;
    CONST X_POS_QTY = 540;

    CONST NAME_MAX_WIDTH = 255;
    CONST SKU_MAX_WIDTH = 10;
    CONST LOCATION_MAX_WIDTH = 10;

    CONST FONT_SIZE = 10;
    CONST HEADERS_FONT_SIZE = 10;

    protected $_selectedMode;

    /**
     * Alias to set a mode before calling getPdf
     * 
     */
    public function getPdfWithMode($order, $mode){
      $this->_selectedMode = $mode;   
      return $this->getPdf($order);
    }

    public function getPdf($order = array()) {        
        

        $this->_beforeGetPdf();
        $this->_initRenderer('invoice');

        if ($this->pdf == null)
            $this->pdf = new Zend_Pdf();
        else
            $this->firstPageIndex = count($this->pdf->pages);

        $this->_currentOrderId = $order->getincrement_id();
       
        //cree la nouvelle page
        $titre = mage::helper('purchase')->__('Order #') . $order->getincrement_id();
        $settings = array();
        $settings['title'] = $titre;
        $settings['store_id'] = $order->getStoreId();
        $page = $this->NewPage($settings);
        $this->defineFont($page,10,self::FONT_MODE_BOLD);

        //cartouche
        $txt_date = "Date :  " . mage::helper('core')->formatDate($order->getCreatedAt(), 'long');
        $txt_order = '';
        
        //$adresse_fournisseur = Mage::getStoreConfig('sales/identity/address');
        $customer = mage::getmodel('customer/customer')->load($order->getCustomerId());
        $adresse_client = mage::helper('purchase')->__('Shipping Address') . ":\n" . $this->FormatAddress($order->getShippingAddress(), '', false, $customer->gettaxvat());
        $adresse_fournisseur = mage::helper('purchase')->__('Billing Address') . ":\n" . $this->FormatAddress($order->getBillingAddress(), '', false, $customer->gettaxvat());
        $this->AddAddressesBlock($page, $adresse_fournisseur, $adresse_client, $txt_date, $txt_order);

        //draw comments
        $comments = mage::helper('Orderpreparation/Comments')->getAll($order);
        if (!empty($comments)) {
            $this->y -= 15;
            $page->drawText(mage::helper('purchase')->__('Comments'), 15, $this->y, 'UTF-8');
            $comments = $this->WrapTextToWidth($page, $comments, 450);
            $offset = $this->DrawMultilineText($page, $comments, 150, $this->y, 10, 0.2, 11);
            $this->y -= (8 + $offset);
            $page->drawLine(10, $this->y, $this->_BLOC_ENTETE_LARGEUR, $this->y);            
        }
        
        //Rajoute le carrier et la date d'expe prevue & les commentaires
        $this->defineFont($page,10);
        $this->y -=15;
        $page->drawText(mage::helper('purchase')->__('Shipping') . ' : ' . $order->getShippingDescription(), 15, $this->y, 'UTF-8');
        $this->y -=15;
        $comments = $this->WrapTextToWidth($page, $order->getmdn_comments(), 550);
        $offset = $this->DrawMultilineText($page, $comments, 15, $this->y, 10, 0.2, 11);
        $this->y -=10 + $offset;
        $page->drawLine(10, $this->y, $this->_BLOC_ENTETE_LARGEUR, $this->y);

        
        //affiche l'entete du tableau
        $this->drawTableHeader($page);
        $this->y -=10;

        //get items
        $preparationWarehouseId = mage::helper('Orderpreparation')->getPreparationWarehouse();
        $operatorId = mage::helper('Orderpreparation')->getOperator();
        $items = Mage::helper('Orderpreparation/Shipment')->GetItemsToShipAsArray($order->getId(), $preparationWarehouseId, $operatorId);
        
        //if array is empty, include all products
        if (count($items) == 0)
        {
            foreach($order->getAllItems() as $orderItem)
            {
                $items[$orderItem->getId()] = $orderItem->getqty_ordered();
            }
        }
                
        //SORT BY LOCATION
        if (mage::getStoreConfig('orderpreparation/picking_list/sort_mode') == 'location') {
            
            //get all locations
            $itemsByLocation = array();        
            foreach ($items as $orderItemId => $qty) {
              $itemsByLocation[$orderItemId] =  mage::getModel('sales/order_item')->load($orderItemId)->getShelfLocation();//return "" for product without stock management so OK
            }
            
            //order them
            if(count($itemsByLocation)>0){
                //order the list by value, so by shelf location
                asort($itemsByLocation);
                
                //order $items like $itemsByLocation
                $orderedItemsByLocation = array();
                foreach ($itemsByLocation as $orderItemId => $shelflocation) {
                    $orderedItemsByLocation[$orderItemId] = $items[$orderItemId];
                }
                
                //if operation is successful, replace $item
                if(count($orderedItemsByLocation)>0){
                    $items = $orderedItemsByLocation;
                }
            }
        }      
        
        
        //PRODUCTS
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0.2));
        $this->defineFont($page,10);

        //if they are some bundle or configurable product, simple product can be in double, triple ...
        //this array enable to display item once
        $this->_alreadyDisplayedProducts = array();

        foreach ($items as $orderItemId => $qty) {
            
            if ($qty == 0)
                continue;

            $this->drawProduct($page, $orderItemId, $order, $preparationWarehouseId, $operatorId, $items);

            //LINE between 2 products
            $page->setLineWidth(0.5);
            $page->drawLine(10, $this->y - 4, $this->_BLOC_ENTETE_LARGEUR, $this->y - 4);
            $this->y -= $this->_ITEM_HEIGHT;

            //FOOTER or NEXT PAGE
            if ($this->y < ($this->_BLOC_FOOTER_HAUTEUR + 40)) {
                $this->drawFooter($page);
                $page = $this->NewPage($settings);
                $this->drawTableHeader($page);
            }
        }

        $this->drawFooter($page);
        $this->AddPagination($this->pdf);
        $this->_afterGetPdf();

        return $this->pdf;
    }

    /**
     * @param $page
     * @param $orderItemId
     * @param $order
     * @param $preparationWarehouseId
     * @param $operatorId
     *
     * Draw a single product item
     */
    public function drawProduct(&$page, $orderItemId, $order, $preparationWarehouseId, $operatorId, $items)
    {
        //Load product
        $item = mage::getModel('sales/order_item')->load($orderItemId);
        $productId = $item->getproduct_id();
        $product = mage::getModel('catalog/product')->load($productId);

        if (mage::getStoreConfig('orderpreparation/picking_list/display_sub_products') == 1) {
            if(!in_array($productId, $this->_alreadyDisplayedProducts)){
                $this->_alreadyDisplayedProducts[] = $productId;
            }else{
                return;
            }
        }

        //Does not display products that dont manage stocks
        if (mage::getStoreConfig('orderpreparation/picking_list/display_product_without_stock_management') == 0) {
            if (!$product->getStockItem()->ManageStock()){
                return;
            }
        }

        //PICTURE
        $picturePath = Mage::helper('AdvancedStock/Product_Image')->getProductImageDir($product);
        if (file_exists($picturePath)) {
            try {
                $zendPicture = Zend_Pdf_Image::imageWithPath($picturePath);
                $imageHeightWidth = 30;
                //public function drawImage(Zend_Pdf_Resource_Image $image, $x1, $y1, $x2, $y2);
                $page->drawImage($zendPicture, self::X_POS_IMAGE, $this->y - 15, self::X_POS_IMAGE + $imageHeightWidth, $this->y - 15 + $imageHeightWidth);
            } catch (Exception $ex) {
                mage::logException($ex);
            }
        }

        //BARCODE - EAN
        $barcode = mage::helper('AdvancedStock/Product_Barcode')->getBarcodeForProduct($product);
        if ($barcode) {
            try {
                $picture = mage::helper('AdvancedStock/Product_Barcode')->getBarcodePicture($barcode);
                if ($picture) {
                    $zendPicture = $this->pngToZendImage($picture);
                    $imageHeight = 30;
                    $imageWidth = 80;
                    $page->drawImage($zendPicture, self::X_POS_BARCODE, $this->y - 15, self::X_POS_BARCODE + $imageWidth, $this->y - 15 + $imageHeight);
                }
            }catch(Exception $ex){
                mage::logException($ex);
            }
        }

        //PARENT
        //add configurable product sku + name above if possible
        if ($product->gettype_id() == 'simple'){
            $parentIds = Mage::getResourceSingleton('catalog/product_type_configurable')->getParentIdsByChild($item->getproduct_id());
            if(count($parentIds)>0){
                $productParent = Mage::getModel('catalog/product')->load($parentIds[0]);
                if($productParent && $productParent->getId()>0) {
                    $this->y -= 5;
                    $configurableProductYshift = 15;
                    $configurableProductFontHeigth = 8;
                    $this->defineFont($page,$configurableProductFontHeigth,self::FONT_MODE_ITALIC);
                    $page->drawText($this->TruncateTextToWidth($page, $productParent->getSku(), 70), self::X_POS_SKU, $this->y+$configurableProductYshift, 'UTF-8');
                    $name = $this->WrapTextToWidth($page, $productParent->getName(), self::X_POS_NAME);
                    $this->DrawMultilineText($page, $name, self::X_POS_PARENT_NAME, $this->y+$configurableProductYshift, $configurableProductFontHeigth, 0.2, 11);
                }
            }
        }

        //PARENT ITEM
        $parentItem = ($item->getparent_item_id())?mage::getModel('sales/order_item')->load($item->getparent_item_id()):null;


        //SKU
        $this->defineFont($page, self::FONT_SIZE);
        $skuWordWrapped = wordwrap($product->getSku(), self::SKU_MAX_WIDTH, "\n", true);
        $this->DrawMultilineText($page, $skuWordWrapped, self::X_POS_SKU, $this->y, self::FONT_SIZE, 0.2, self::FONT_SIZE +1);

        //SHELF LOCATION
        $shelfLocationWrapped = wordwrap($item->getShelfLocation(), self::LOCATION_MAX_WIDTH, "\n", true);
        $this->DrawMultilineText($page, $shelfLocationWrapped, self::X_POS_SHELF_LOCATION, $this->y, self::FONT_SIZE, 0.2, self::FONT_SIZE +1);


        //NAME
        $productName = $product->getName();
        $productName .= mage::helper('AdvancedStock/Product_ConfigurableAttributes')->getDescription($productId);
        $customOptions = $item->getOrderItemOptions();
        if($parentItem) {
            $customOptions .= $parentItem->getOrderItemOptions();
        }
        //skip if there is only a line return
        if(strlen($customOptions)>2)
            $productName .= $customOptions;

        $name = $this->WrapTextToWidth($page, $productName, self::NAME_MAX_WIDTH);
        $offset = $this->DrawMultilineText($page, $name, self::X_POS_NAME, $this->y, 10, 0.2, 11);
        $this->y -= $offset;

        //QTY
        $this->defineFont($page,self::FONT_SIZE);
        if (!$product->getStockItem()->ManageStock()){
            $qtySelected = $item->getqty_ordered();
        }else{
            if($this->_selectedMode == self::MODE_ALL){
                $qtySelected = $item->getqty_ordered();
            }else if($this->_selectedMode == self::MODE_ORDER_PREPRATION_NOT_SELECTED_TAB){
                $qtySelected = $item->getreserved_qty();
            }else if($this->_selectedMode == self::MODE_ORDER_PREPRATION_SELECTED_TAB){
                $qtySelected = mage::getModel('Orderpreparation/ordertoprepare')->GetTotalAddedQtyForProductForSelectedOrder($productId, $order->getId(), $preparationWarehouseId, $operatorId);
            }else{
                $qtySelected = $this->_selectedMode;
            }
        }
        $page->drawText((int)$qtySelected, self::X_POS_QTY, $this->y, 'UTF-8');


        //COMMENTS
        $this->defineFont($page,self::FONT_SIZE - 2);
        $this->y -= $this->_ITEM_HEIGHT;
        $caption = $this->WrapTextToWidth($page, $item->getcomments(), 300);
        $offset = $this->DrawMultilineText($page, $caption, 200, $this->y, 10, 0.2, 11);
        $this->y -= $offset;

        //CHILDs (optionnal) display child product for configurable or a bundle
        if (mage::getStoreConfig('orderpreparation/picking_list/display_sub_products') == 1) {
            if ($product->gettype_id() == 'bundle' || $product->gettype_id() == 'configurable'){
                $this->y += 15;
                foreach ($items as $ssorderItemId => $ssqty) {
                    $ssItem = mage::getModel('sales/order_item')->load($ssorderItemId);
                    if ($ssItem->getparent_item_id() == $orderItemId) {
                        $subProductId = $ssItem->getproduct_id();
                        if($subProductId>0){
                            $subProduct = mage::getModel('catalog/product')->load($subProductId);
                            if($subProduct && $subProduct->getId()>0){
                                $subProductYshift = 9;
                                $subProductFontHeigth = 8;
                                $this->y -= $subProductFontHeigth + $subProductYshift;
                                $this->defineFont($page,$subProductFontHeigth,self::FONT_MODE_ITALIC);
                                $page->drawText($this->TruncateTextToWidth($page, $subProduct->getSku(), 70), self::X_POS_SKU, $this->y+$subProductYshift, 'UTF-8');
                                $name = $this->WrapTextToWidth($page, $ssqty.'x '.$subProduct->getName(), self::X_POS_NAME);
                                $this->DrawMultilineText($page, $name, self::X_POS_PARENT_NAME, $this->y+$subProductYshift, $subProductFontHeigth, 0.2, 11);
                                $this->_alreadyDisplayedProducts[] = $subProductId;
                            }
                        }
                    }
                }
            }
        }
    }



    /**
     * Product table header
     *
     * @param unknown_type $page
     */
    public function drawTableHeader(&$page) {

        //HEADERS TITLE
        $this->y -= 15;
        $this->defineFont($page,self::HEADERS_FONT_SIZE);

        $page->drawText(mage::helper('purchase')->__('Sku'), self::X_POS_SKU, $this->y, 'UTF-8');
        $page->drawText(mage::helper('purchase')->__('Location'), self::X_POS_SHELF_LOCATION, $this->y, 'UTF-8');
        $page->drawText(mage::helper('purchase')->__('Name'), self::X_POS_NAME, $this->y, 'UTF-8');
        $page->drawText(mage::helper('purchase')->__('Qty'), self::X_POS_QTY, $this->y, 'UTF-8');

        //LINE BELOW HEADERS
        $this->y -= 8;
        $page->drawLine(10, $this->y, $this->_BLOC_ENTETE_LARGEUR, $this->y);

        $this->y -= 15;
    }

  /**
     * Page header
     */
    public function drawHeader(&$page, $title, $StoreId = null) {


        if(!$StoreId){
          $StoreId = Mage::app()->getStore()->getStoreId();
        }

        $this->defineFont($page,10);

        //BACK GROUND
        $color = 1; //WHITE
        $page->setFillColor(new Zend_Pdf_Color_GrayScale($color));
        $page->drawRectangle(10, $this->y, $this->_BLOC_ENTETE_LARGEUR, $this->y - $this->_BLOC_ENTETE_HAUTEUR, Zend_Pdf_Page::SHAPE_DRAW_FILL);

        //LOGO
        $this->insertLogo($page, $StoreId);

        //ADRESSES
        $color = 0; //BLACK
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
        $this->DrawMultilineText($page, Mage::getStoreConfig('purchase/general/header_text', $StoreId), 300, $this->y - 10, 10, 0, 15);

        //LINE BELOW ADDRESS
        $color = 0.2; //GREY
        $this->y -= $this->_BLOC_ENTETE_HAUTEUR + 5;
        $page->setLineWidth(1.5);
        $page->setLineColor(new Zend_Pdf_Color_GrayScale($color));
        $page->drawLine(10, $this->y, $this->_BLOC_ENTETE_LARGEUR, $this->y);

        //TITLE
        $this->y -= 35;
        $name = $title;
        $page->setFillColor(new Zend_Pdf_Color_GrayScale($color));
        $this->defineFont($page,24,self::FONT_MODE_BOLD);
        $this->drawTextInBlock($page, $name, 10, $this->y, $this->_PAGE_WIDTH, 50, 'l');

        //ORDER BARCODE
        if (class_exists('Zend_Barcode'))
        {
            try {
                $barcodeOptions = array('text' => $this->_currentOrderId);
                $rendererOptions = array();
                $factory = Zend_Barcode::factory(
                        'Code128', 'image', $barcodeOptions, $rendererOptions
                );
                $image = $factory->draw();
                $zendPicture = $this->pngToZendImage($image);
                $barcodeWidth = 150;
                $barcodeHeight = 35;
                $page->drawImage($zendPicture, $this->_BLOC_ENTETE_LARGEUR - $barcodeWidth, $this->y - 10, $this->_BLOC_ENTETE_LARGEUR, $this->y - 10 + $barcodeHeight);
            }catch(Exception $ex){
               mage::logException($ex);
            }
        }

        //LINE BELOW TITLE
        $this->y -= 20;
        $page->drawLine(10, $this->y, $this->_BLOC_ENTETE_LARGEUR, $this->y);
    }


    protected function pngToZendImage($pngImage) {
        $path = Mage::getBaseDir() . DS . 'var' . DS . 'barcode_image.png';
        imagepng($pngImage, $path);
        $zendPicture = Zend_Pdf_Image::imageWithPath($path);
        unlink($path);
        return $zendPicture;
    }

}


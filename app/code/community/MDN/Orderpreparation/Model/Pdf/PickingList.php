<?php

/**
 * Generate PDF for picking list
 *
 */
class MDN_Orderpreparation_Model_Pdf_PickingList extends MDN_Orderpreparation_Model_Pdf_Pdfhelper {


    CONST X_POS_IMAGE = 10;
    CONST X_POS_QTY = 55;
    CONST X_POS_NAME = 80;
    CONST X_POS_BARCODE = 360;
    CONST X_POS_ORDERIDS = 450;
    CONST X_POS_LOCATION = 520;

    CONST WIDTH_IMAGE = 40;
    CONST WIDTH_NAME = 250;
    CONST WIDTH_BARCODE = 80;

    CONST DEFAULT_FONT_SIZE = 10;
    CONST DEFAULT_LINE_HEIGHT = 10;

    /**
     * Enter description here...
     *
     * @param array $data :
     * ---> key comments contains comments
     * ---> key products contains an array with products
     * -------> each product as data : type_id, picture_path, qty, manufacturer, sku, name, location, barcode
     *
     * @return unknown
     */
    public function getPdf($data = array()) {

        $this->_beforeGetPdf();
        $this->_initRenderer('invoice');

        //init datas
        $comments = $data['comments'];
        $products = $data['products'];

        //init pdf object
        if ($this->pdf == null)
            $this->pdf = new Zend_Pdf();


        //create new page
        $titre = mage::helper('purchase')->__('Picking List');
        $settings = array();
        $settings['title'] = $titre;
        $settings['store_id'] = 0;
        $page = $this->NewPage($settings);
        $this->defineFont($page,self::DEFAULT_FONT_SIZE,self::FONT_MODE_BOLD);

        //display comments
        if ($comments) {
            $this->y -=20;
            $offset = $this->DrawMultilineText($page, $comments, 25, $this->y, 12, 0, 18);
            $this->y -= $offset + 10;
            $page->drawLine(self::DEFAULT_LINE_HEIGHT, $this->y, $this->_BLOC_ENTETE_LARGEUR, $this->y);
            $this->y -=10;
        }

        //display table header
        $this->drawTableHeader($page);
        $this->y -=10;
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0.2));
        $this->defineFont($page,self::DEFAULT_FONT_SIZE);

        foreach ($products as $product) {

            //---------------------------------------------
            //PICTURE
            if ($product['picture_path']) {
                if (file_exists($product['picture_path'])) {
                    try {
                        $zendPicture = Zend_Pdf_Image::imageWithPath($product['picture_path']);
                        $page->drawImage($zendPicture, self::X_POS_IMAGE, $this->y - 15, self::WIDTH_IMAGE, $this->y - 15 + 30);
                    } catch (Exception $ex) {
                        mage::logException($ex);
                    }
                }
            }

            //---------------------------------------------
            //QTY
            $this->defineFont($page, self::DEFAULT_FONT_SIZE);
            $page->drawText($product['qty'], self::X_POS_QTY, $this->y - 5, 'UTF-8');

            //---------------------------------------------
            //PRODUCT NAME
            $caption = $product['sku'];
            $manufacturerText = $product['manufacturer'];
            if ($manufacturerText) {
                $caption = $manufacturerText . ' - ' . $caption;
            }
            $caption .= "\n" . $product['name'];

            $caption .= mage::helper('AdvancedStock/Product_ConfigurableAttributes')->getDescription($product->getId());
            $caption = $this->WrapTextToWidth($page, $caption, self::WIDTH_NAME);
            $offset = $this->DrawMultilineText($page, $caption, self::X_POS_NAME, $this->y + 10, self::DEFAULT_FONT_SIZE, 0.2, 16);

            //---------------------------------------------
            //BARCODE
            if ($product['barcode']) {
                try{
                    $picture = mage::helper('AdvancedStock/Product_Barcode')->getBarcodePicture($product['barcode']);
                    if ($picture) {
                        $zendPicture = $this->pngToZendImage($picture);
                        $page->drawImage($zendPicture, self::X_POS_BARCODE, $this->y - 15, self::X_POS_BARCODE + self::WIDTH_BARCODE, $this->y - 15 + 30);
                    }
                }catch(Exception $ex){
                    mage::logException($ex);
                }
            }

            //---------------------------------------------
            //ORDER LIST AND ORDER QTY
            if (($product['order_list']) && is_array($product['order_list'])) {
                $buffer = "";
                foreach($product['order_list'] as $orderId => $qty){
                     $buffer .= $this->getOrderIncrementId($orderId) . ' ('.$qty.')'."\n";
                }
                $offset = $this->DrawMultilineText($page, $buffer, self::X_POS_ORDERIDS, $this->y + 5, self::DEFAULT_FONT_SIZE, 0.2, 16);
            }

            //---------------------------------------------
            //SHELF LOCATION
            $page->drawText($product['location'], self::X_POS_LOCATION, $this->y, 'UTF-8');


            if ($offset < 20)
                $offset = 20;
            $this->y -= $offset;

            //line separation
            $page->setLineWidth(0.5);
            $page->drawLine(self::DEFAULT_LINE_HEIGHT, $this->y - 4, $this->_BLOC_ENTETE_LARGEUR, $this->y - 4);
            $this->y -= $this->_ITEM_HEIGHT;

            //new page (if needed)
            if ($this->y < ($this->_BLOC_FOOTER_HAUTEUR + 40)) {
                $this->drawFooter($page);
                $page = $this->NewPage($settings);
                $this->drawTableHeader($page);
                $this->y -= 20;
            }
        }

        //draw footer
        $this->drawFooter($page);

        //draw pager
        $this->AddPagination($this->pdf);

        $this->_afterGetPdf();

        return $this->pdf;
    }

    /**
     * Table header
     *
     * @param unknown_type $page
     */
    public function drawTableHeader(&$page) {

        $this->y -= 15;
        $this->defineFont($page,self::DEFAULT_FONT_SIZE);

        $page->drawText(mage::helper('purchase')->__('Qty'), self::X_POS_QTY, $this->y, 'UTF-8');
        $page->drawText(mage::helper('purchase')->__('Product'), self::X_POS_NAME, $this->y, 'UTF-8');
        $page->drawText(mage::helper('purchase')->__('Barcode'), self::X_POS_BARCODE, $this->y, 'UTF-8');
        $page->drawText(mage::helper('purchase')->__('Order'), self::X_POS_ORDERIDS, $this->y, 'UTF-8');
        $page->drawText(mage::helper('purchase')->__('Location'), self::X_POS_LOCATION, $this->y, 'UTF-8');

        $this->y -= 8;
        $page->drawLine(self::DEFAULT_LINE_HEIGHT, $this->y, $this->_BLOC_ENTETE_LARGEUR, $this->y);

        $this->y -= 15;
    }

    private function getOrderIncrementId($orderId){
        $sql = 'SELECT increment_id FROM '.Mage::getConfig()->getTablePrefix().'sales_flat_order WHERE entity_id = '.$orderId;
        return mage::getResourceModel('sales/order_item_collection')->getConnection()->fetchOne($sql);
    }

}


<?php

class MDN_Orderpreparation_Model_Pdf_OrderPickup extends MDN_Orderpreparation_Model_Pdf_Pdfhelper {

    public function getPdf($order = array()) {
        $order = $order[0];

        $this->_beforeGetPdf();
        $this->_initRenderer('invoice');

        if ($this->pdf == null)
            $this->pdf = new Zend_Pdf();
        else
            $this->firstPageIndex = count($this->pdf->pages);

        

        //add new page
        $titre = mage::helper('purchase')->__('Order pickup');
        $settings = array();
        $settings['title'] = $titre;
        $settings['store_id'] = 0;
        $page = $this->NewPage($settings);
        $this->defineFont($page,10,self::FONT_MODE_BOLD);

        //address block
        $txt_date = "Date :  " . mage::helper('core')->formatDate($order->getCreatedAt(), 'long');
        $txt_order = mage::helper('Orderpreparation')->__('Order #') . $order->getincrement_id();
        $customer = mage::getmodel('customer/customer')->load($order->getCustomerId());
        $adresse_client = mage::helper('purchase')->__('Shipping Address') . ":\n" . $this->FormatAddress($order->getShippingAddress(), '', false, $customer->gettaxvat());
        $adresse_fournisseur = mage::helper('purchase')->__('Billing Address') . ":\n" . $this->FormatAddress($order->getBillingAddress(), '', false, $customer->gettaxvat());
        $this->AddAddressesBlock($page, $adresse_fournisseur, $adresse_client, $txt_date, $txt_order);

        //Add carrier
        $this->defineFont($page,10);
        $this->y -=15;
        $page->drawText(mage::helper('purchase')->__('Shipping') . ' : ' . $order->getShippingDescription(), 15, $this->y, 'UTF-8');
        $this->y -=15;

        //table jeader
        $this->drawTableHeader($page);
        $this->y -=10;


        //Display products
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0.2));
        $this->defineFont($page,10);
        foreach ($order->getAllItems() as $item) {
            //Load product
            $product = mage::getModel('catalog/product')->load($item->getproduct_id());

            //draw columns
            $page->drawText((int)$item->getqty_ordered(), 15, $this->y, 'UTF-8');
            $page->drawText($product->getSku(), 160, $this->y, 'UTF-8');

            $name = $this->WrapTextToWidth($page, $product->getName(), 250);
            $name = $this->WrapTextToWidth($page, $product->getName(), 250);
            $offset = $this->DrawMultilineText($page, $name, 300, $this->y, 10, 0.2, 11);

            //break line
            $page->setLineWidth(0.5);
            $page->drawLine(10, $this->y - 4, $this->_BLOC_ENTETE_LARGEUR, $this->y - 4);
            $this->y -= $this->_ITEM_HEIGHT;

            //add new page
            if ($this->y < ($this->_BLOC_FOOTER_HAUTEUR + 40)) {
                $this->drawFooter($page);
                $page = $this->NewPage($settings);
                $this->drawTableHeader($page);
            }
        }

        //add customer commitment zone
        $this->y -= 40;
        $this->defineFont($page,12);
        $txt = mage::helper('Orderpreparation')->__('I undersign, %s attest to have picked up products on %s', '........................................................', date('Y-m-d'));
        $page->drawText($txt, 15, $this->y, 'UTF-8');
        $this->y -= 50;
        $page->drawText(mage::helper('Orderpreparation')->__('Signature :'), 15, $this->y, 'UTF-8');

        //misc
        $this->drawFooter($page);
        $this->AddPagination($this->pdf);

        $this->_afterGetPdf();

        return $this->pdf;
    }

    /**
     * Display table header
     *
     * @param unknown_type $page
     */
    public function drawTableHeader(&$page) {

        $this->y -= 15;
        $this->defineFont($page,12);
        
        $page->drawText(mage::helper('purchase')->__('Qty'), 15, $this->y, 'UTF-8');
        $page->drawText(mage::helper('purchase')->__('Sku'), 160, $this->y, 'UTF-8');
        $page->drawText(mage::helper('purchase')->__('Name'), 300, $this->y, 'UTF-8');

        $this->y -= 8;
        $page->drawLine(10, $this->y, $this->_BLOC_ENTETE_LARGEUR, $this->y);

        $this->y -= 15;
    }

}


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

/**
 * Purchase Order PDF
 *
 */
class MDN_Purchase_Model_Pdf_Order extends MDN_Purchase_Model_Pdf_Pdfhelper {

    private $_showPictures = false;
    private $_pictureSize = 70;

    const LEFT_MARGIN_WIDTH = 15;
    const SKU_WIDTH = 90;
    const DESCRIPTION_WIDTH = 200;
    const UNIT_PRICE_WIDTH = 65;
    const WEE_WIDTH = 50;
    const QTY_WIDTH = 35;
    const TAX_WIDTH = 45;
    const SUBTOTAL_WIDTH = 60;


    public function getPdf($orders = array()) {
        $this->initLocale($orders);

        $this->_beforeGetPdf();
        $this->_initRenderer('invoice');

        if ($this->pdf == null)
            $this->pdf = new Zend_Pdf();
        else
            $this->firstPageIndex = count($this->pdf->pages);

        foreach ($orders as $order) {

            //add new page
            $titre = mage::helper('purchase')->__('Purchase Order');
            $settings = array();
            $settings['title'] = $titre;
            $settings['store_id'] = 0;
            $page = $this->NewPage($settings);

            //page header
            $txt_date = mage::helper('purchase')->__('Date').' : ' . mage::helper('core')->formatDate($order->getpo_date(), 'short');
            $txt_order = mage::helper('purchase')->__('N. '). $order->getpo_order_id();
            $addressLeft = $order->getSupplier()->getAddressAsText();
            $addressRight = $order->getTargetWarehouse()->getstock_address();
            $this->AddAddressesBlock($page, $addressLeft, $addressRight, $txt_date, $txt_order);

            //table header
            $this->drawTableHeader($page);

            $this->y -=10;
            $offset=0;
            $x = self::LEFT_MARGIN_WIDTH;

            //Display products
            foreach ($order->getProducts() as $item) {

                $this->y -= $offset;
                $item->setPurchaseOrder($order);

                //font initialization
                $page->setFillColor(new Zend_Pdf_Color_GrayScale(0.2));
                $this->defineFont($page,10);

                //display SKU or picture

                $x = self::LEFT_MARGIN_WIDTH;




            if ($this->_showPictures == true) {
                    $product = mage::getModel('catalog/product')->load($item->getpop_product_id());
                    if ($product->getId()) {
                        $productImagePath = '';

                        $smallImage = $product->getsmall_image();

                        //if no image look for parent product to get the image
                        if(!$smallImage){
                            $_parentIdArray = Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($product->getId());
                            if($_parentIdArray && sizeof($_parentIdArray)==1){
                              $_product = Mage::getModel('catalog/product')->load($_parentIdArray[0]);
                              if ($_product->getId() && $_product->getTypeId() == 'configurable') {
                                $smallImage = $_product->getsmall_image();
                              }
                            }
                        }

                        if($smallImage)
                            $productImagePath = Mage::getBaseDir() . '/media/catalog/product' . $smallImage;

                        if (is_file($productImagePath)) {
                            try {
                                $image = Zend_Pdf_Image::imageWithPath($productImagePath);
                                $page->drawImage($image, 10, $this->y - $this->_pictureSize + 20, 5 + $this->_pictureSize, $this->y + 10);
                                $x += self::SKU_WIDTH;
                            } catch (Exception $ex) {
                                 mage::logException($ex);
                            }
                        }
                    }
                }



                //name
                $skuAndDescriptionWidth = self::SKU_WIDTH + self::DESCRIPTION_WIDTH;
                $desc_with = $x + $skuAndDescriptionWidth;
                if (mage::getStoreConfig('purchase/purchase_product_grid/display_weee') == 0) {
                  $desc_with += self::WEE_WIDTH;
                }

                //WrapTextToWidth is not optimal because it depend of word width, so it need to be cut to 70% to avoid problem
                $realisticWidthToAvoidOverride = $desc_with*0.7; //70%
                $caption = $this->WrapTextToWidth($page, $item->getpop_product_name(), $realisticWidthToAvoidOverride);

                //Add sku below description
                $sku = ($item->getpop_supplier_ref())?$item->getpop_supplier_ref():$item->getsku();               
                $skuWrapped = $this->WrapTextToWidth($page, $sku, $skuAndDescriptionWidth);
                $caption .= "\n".$skuWrapped;

                //add packaging name and Quantity to avoid misunderstanding of the supplier
                if ($item->getpop_packaging_id())                
                    $caption .= "\n".$item->getpop_packaging_name().' ('.$item->getpop_packaging_value().'x)';
                

                $nameLineHeight = 10;
                $nameColor = 0.2;
                $nameFontSize = 10;
                $offset = $this->DrawMultilineText($page, $caption, $x, $this->y, $nameFontSize, $nameColor, $nameLineHeight);
                $x += $skuAndDescriptionWidth;
                
                if (mage::getStoreConfig('purchase/purchase_product_grid/display_weee') == 0) {
                  $x += self::WEE_WIDTH;
                }

                //unit price
                if ($order->getpo_status() != MDN_Purchase_Model_Order::STATUS_INQUIRY){
                    $this->drawTextInBlock($page, $order->getCurrency()->formatTxt($item->getpop_price_ht()), $x, $this->y, self::UNIT_PRICE_WIDTH, 20, 'l');
                }
                
                $x += self::UNIT_PRICE_WIDTH;
                
                //qty
                $this->drawTextInBlock($page, (int) $item->getOrderedQty(), $x, $this->y, self::QTY_WIDTH, 20, 'l');
                $x += self::QTY_WIDTH;
               
                //WEEE, tax rate, row total
                if ($order->getpo_status() != MDN_Purchase_Model_Order::STATUS_INQUIRY) {
                     if (mage::getStoreConfig('purchase/purchase_product_grid/display_weee') == 1) {
                      $this->drawTextInBlock($page, $order->getCurrency()->formatTxt($item->getpop_eco_tax()), $x, $this->y, self::WEE_WIDTH, 20, 'l');
                      $x += self::WEE_WIDTH;
                     }
                     $this->drawTextInBlock($page, number_format($item->getpop_tax_rate(), 2) . '%', $x, $this->y, self::TAX_WIDTH, 20, 'l');
                     $x += self::TAX_WIDTH;

                     //Row total
                     $this->drawTextInBlock($page, $order->getCurrency()->formatTxt($item->getRowTotal()), $x, $this->y, self::SUBTOTAL_WIDTH, 20, 'r');
                }


                $lineAdditionalHeight = 12;
                if ($this->_showPictures && ($this->_pictureSize > $offset + $lineAdditionalHeight))
                    $this->y -= $this->_pictureSize;
                else
                    $this->y -= $offset + $lineAdditionalHeight;

                //new page if required
                if ($this->y < ($this->_BLOC_FOOTER_HAUTEUR + 40)) {
                    $this->drawFooter($page);
                    $page = $this->NewPage($settings);
                    $this->drawTableHeader($page);
                }
            }

            //add shipping costs
            if ($order->getpo_status() != MDN_Purchase_Model_Order::STATUS_INQUIRY) {

                $this->y -= $lineAdditionalHeight;

                $xText = self::LEFT_MARGIN_WIDTH + self::SKU_WIDTH;
                $xValue = $xText + self::DESCRIPTION_WIDTH + self::UNIT_PRICE_WIDTH + self::WEE_WIDTH + self::QTY_WIDTH;

                $lineY = $this->y + 10;
                $page->drawLine(10, $lineY, $this->_BLOC_ENTETE_LARGEUR, $lineY);

                $this->y -= ($this->_ITEM_HEIGHT -20);


                //Shipping cost
                $this->defineFont($page,10,self::FONT_MODE_BOLD);
                $this->DrawMultilineText($page, mage::helper('purchase')->__('Shipping costs'), $xText, $this->y, 10, 0.2, 11);
                $this->drawTextInBlock($page, number_format($order->getpo_tax_rate(), 2) . '%', $xValue, $this->y, self::TAX_WIDTH, 20, 'l');
                $this->drawTextInBlock($page, $order->getCurrency()->formatTxt($order->getShippingAmountHt()), $x, $this->y, self::SUBTOTAL_WIDTH, 20, 'r');

                //Tax & duties
                $this->y -= $this->_ITEM_HEIGHT;
                $this->defineFont($page,10,self::FONT_MODE_BOLD);
                $this->DrawMultilineText($page, mage::helper('purchase')->__('Taxes and Duties'), $xText, $this->y, 10, 0.2, 11);
                $this->drawTextInBlock($page, number_format($order->getpo_tax_rate(), 2) . '%', $xValue, $this->y, self::TAX_WIDTH, 20, 'l');
                $this->drawTextInBlock($page, $order->getCurrency()->formatTxt($order->getZollAmountHt()), $x, $this->y, self::SUBTOTAL_WIDTH, 20, 'r');
                
            }

            //new page if required
            if ($this->y < (150)) {
                $this->drawFooter($page);
                $page = $this->NewPage($settings);
                $this->drawTableHeader($page);
            }

            //grey line
            $this->y -= 10;
            $page->drawLine(10, $this->y, $this->_BLOC_ENTETE_LARGEUR, $this->y);
            $VerticalLineHeight = 80;
            $page->drawLine($this->_PAGE_WIDTH / 2, $this->y, $this->_PAGE_WIDTH / 2, $this->y - $VerticalLineHeight);

            //totals font
            $this->defineFont($page,14);
            $page->setFillColor(new Zend_Pdf_Color_GrayScale(0.2));
            $this->y -= 20;

            //Comments
            $comments = $order->getpo_comments();
            if (($comments != '') && ($comments != null)) {
                $page->setFillColor(new Zend_Pdf_Color_GrayScale(0.3));
                $this->defineFont($page,12);
                $page->drawText(mage::helper('purchase')->__('Comments'), 15, $this->y, 'UTF-8');
                $comments = $this->WrapTextToWidth($page, $comments, $this->_PAGE_WIDTH / 2);
                $this->DrawMultilineText($page, $comments, 15, $this->y - 15, 10, 0.2, 11);
            }
            if ($order->getpo_status() != MDN_Purchase_Model_Order::STATUS_INQUIRY) {
                $page->drawText(mage::helper('purchase')->__('Total (excl tax)'), $this->_PAGE_WIDTH / 2 + 10, $this->y, 'UTF-8');
                $this->drawTextInBlock($page, $order->getCurrency()->formatTxt($order->getTotalHt()), $this->_PAGE_WIDTH / 2, $this->y, $this->_PAGE_WIDTH / 2 - 30, 40, 'r');
                $this->y -= 20;
                $page->drawText(mage::helper('purchase')->__('Tax'), $this->_PAGE_WIDTH / 2 + 10, $this->y, 'UTF-8');
                $this->drawTextInBlock($page, $order->getCurrency()->formatTxt($order->getTaxAmount()), $this->_PAGE_WIDTH / 2, $this->y, $this->_PAGE_WIDTH / 2 - 30, 40, 'r');
                $this->y -= 20;
                $page->drawText(mage::helper('purchase')->__('Total (incl tax)'), $this->_PAGE_WIDTH / 2 + 10, $this->y, 'UTF-8');
                $this->drawTextInBlock($page, $order->getCurrency()->formatTxt($order->getTotalTtc()), $this->_PAGE_WIDTH / 2, $this->y, $this->_PAGE_WIDTH / 2 - 30, 40, 'r');
            }

            $this->y -= 20;
            $page->drawLine(10, $this->y, $this->_BLOC_ENTETE_LARGEUR, $this->y);

            //Payment & shipping methods
            $this->defineFont($page,10);
            $this->y -= 20;
            $page->drawText(mage::helper('purchase')->__('Billing Method : ') . $order->getpo_payment_type(), 15, $this->y, 'UTF-8');
            $this->y -= 20;
            $page->drawText(mage::helper('purchase')->__('Carrier : ') . $order->getpo_carrier(), 15, $this->y, 'UTF-8');

            if ($order->getSupplier()->getsup_shipping_instructions())
            {
                $shippingInstructions = mage::helper('purchase')->__('Shipping instructions').' : '. $order->getSupplier()->getsup_shipping_instructions();
                $shippingInstructions = $this->WrapTextToWidth($page, $shippingInstructions, $this->_PAGE_WIDTH - 50);
                $offset = $this->DrawMultilineText($page, $shippingInstructions, 15, $this->y - 15, 10, 0.2, 11);
                $this->y -= $offset + 20;
            }

            $this->y -= 20;
            $page->drawLine(10, $this->y, $this->_BLOC_ENTETE_LARGEUR, $this->y);

            //Static area
            $this->y -= 20;
            $this->defineFont($page,12);
            $page->drawText(mage::helper('purchase')->__('Comments : '), 15, $this->y, 'UTF-8');
            $this->y -= 20;
            $this->defineFont($page,10);
            $txt = Mage::getStoreConfig('purchase/general/pdf_comment');
            $txt = $this->WrapTextToWidth($page, $txt, $this->_PAGE_WIDTH - 100);
            $this->DrawMultilineText($page, $txt, 15, $this->y, 10, 0.2, 11);

            //Draw footer
            $this->drawFooter($page);
        }

        //Display pages numbers
        $this->AddPagination($this->pdf);

        $this->_afterGetPdf();

        //reset language
        Mage::app()->getLocale()->revert();

        return $this->pdf;
    }

    /**
     * Dessine l'entete du tableau avec la liste des produits
     *
     * @param unknown_type $page
     */
    public function drawTableHeader(&$page) {

        //entetes de colonnes
        $this->y -= 15;
        $this->defineFont($page,10);

        $x = self::LEFT_MARGIN_WIDTH;

        $page->drawText(mage::helper('purchase')->__('Description'), $x, $this->y, 'UTF-8');
        $x += self::SKU_WIDTH;
        $x += self::DESCRIPTION_WIDTH;

        if (mage::getStoreConfig('purchase/purchase_product_grid/display_weee') == 0) {
          $x += self::WEE_WIDTH;
        }
        $page->drawText(mage::helper('purchase')->__('Unit Price'), $x, $this->y, 'UTF-8');
        $x += self::UNIT_PRICE_WIDTH;

        $page->drawText(mage::helper('purchase')->__('Qty'), $x, $this->y, 'UTF-8');
        $x += self::QTY_WIDTH;
        
        if (mage::getStoreConfig('purchase/purchase_product_grid/display_weee') == 1) {          
          $page->drawText(mage::helper('purchase')->__('WEEE'), $x, $this->y, 'UTF-8');
          $x += self::WEE_WIDTH;
        }        
       
        $page->drawText(mage::helper('purchase')->__('Tax'), $x, $this->y, 'UTF-8');
        $x += self::TAX_WIDTH;

        $this->drawTextInBlock($page,mage::helper('purchase')->__('Subtotal'), $x, $this->y, self::SUBTOTAL_WIDTH, 20, 'r');

        
 
        //barre grise fin entete colonnes
        $this->y -= 8;
        $page->drawLine(10, $this->y, $this->_BLOC_ENTETE_LARGEUR, $this->y);

        $this->y -= 15;
    }

    /**
     * init pdf locale depending of supplier locale
     *
     */
    protected function initLocale($orders) {
        //consider only first order
        foreach ($orders as $order) {
            $localeId = $order->getSupplier()->getsup_locale();
            if ($localeId)
                Mage::app()->getLocale()->emulateLocale($localeId);
        }
    }

}
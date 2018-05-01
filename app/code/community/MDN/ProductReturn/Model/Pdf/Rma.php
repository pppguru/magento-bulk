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
 * @author     : Olivier ZIMMERMANN
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
/**
 * Classe pour l'impression d'un bon de commande fournisseur
 *
 */
class MDN_ProductReturn_Model_Pdf_Rma extends MDN_ProductReturn_Model_Pdf_Pdfhelper
{

    protected $_currentRma = null;

    public function getPdf($rmas = array())
    {
        $this->_beforeGetPdf();
        $this->_initRenderer('invoice');

        if ($this->pdf == null)
            $this->pdf = new Zend_Pdf();
        else
            $this->firstPageIndex = count($this->pdf->pages);

        $style = new Zend_Pdf_Style();
        $style->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD), 10);

        foreach ($rmas as $rma) {

            $this->_currentRma = $rma;

            //new page
            $titre                = mage::helper('ProductReturn')->__('Product Return #%s', $rma->getrma_ref());
            $settings             = array();
            $settings['title']    = $titre;
            $settings['store_id'] = $rma->getSalesOrder()->getstore_id();
            $page                 = $this->NewPage($settings);

            //addresses and main information
            $txt_date   = mage::helper('ProductReturn')->__('Date : %s', mage::helper('core')->formatDate($rma->getrma_created_at(), 'short'));
            $txtStatus       = mage::helper('ProductReturn')->__('Order : %s', $rma->getSalesOrder()->getincrement_id());
            $myAddress       = Mage::getStoreConfig('productreturn/pdf/address');
            $customerAddress = $rma->getShippingAddress()->getFormated();
            $this->AddAddressesBlock($page, $myAddress, $customerAddress, $txt_date, $txtStatus);
            $this->y -= 20;

            //display valid date end status
            $soustitre = mage::helper('ProductReturn')->__('Valid until %s', mage::helper('core')->formatDate($rma->getrma_expire_date(), 'short'));
            $page->setFillColor(new Zend_Pdf_Color_GrayScale(0.3));
            $page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD), 14);
            $this->drawTextInBlock($page, $soustitre, 0, $this->y, $this->_PAGE_WIDTH, 50, 'c');
            $this->y -= 10;
            $page->drawLine(10, $this->y, $this->_BLOC_ENTETE_LARGEUR, $this->y);

            $this->y -= 20;

            //display products
            foreach ($rma->getProducts() as $product) {
                if ($product->getrp_qty() > 0) {
                    $itemrealsize       = $this->y;
                    $imgsize            = 100;
                    $this->_ITEM_HEIGHT = $imgsize;
                    $pimg               = mage::helper('ProductReturn')->getProductImage($product->getproduct_id(), $imgsize);
                    $imageFile          = str_replace(Mage::getBaseUrl('media'), "media/", $pimg);
                    try {
                        $zendPicture = Zend_Pdf_Image::imageWithPath($imageFile);
                        $page->drawImage($zendPicture, 10, $this->y - $imgsize + $this->_LINE_HEIGHT, 10 + $imgsize, $this->y - $imgsize + $this->_LINE_HEIGHT + $imgsize);
                    } catch (Exception $ex) {
                        //nothing
                    }


                    $page->setFillColor(new Zend_Pdf_Color_GrayScale(0.2));
                    $page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD), 14);

                    $productName = (int)$product->getrp_qty() . ' x ';
                    $productName .= Mage::getSingleton('ProductReturn/RmaProducts')->getProductName($product, false);
                    $offset = $this->DrawMultilineText($page, $productName, $imgsize + 20, $this->y, 14, 0.2, 15);
                    $this->y -= $offset + $this->_LINE_HEIGHT;
                    $offset = $this->DrawMultilineText($page, ' ('.$product->getsku().')', $imgsize + 20, $this->y, 14, 0.2, 15);
                    $this->y -= $offset + $this->_LINE_HEIGHT;

                    $page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA), 10);
                    $this->y -= $this->_LINE_HEIGHT;
                    $page->drawText($this->TruncateTextToWidth($page, mage::helper('ProductReturn')->__('Reason') . ' : ' . $product->getrp_reason(), 100), $imgsize + 20, $this->y, 'UTF-8');

                    $this->y -= $this->_LINE_HEIGHT;
                    $page->drawText($this->TruncateTextToWidth($page, mage::helper('ProductReturn')->__('Request type') . ' : ' . $product->getrp_request_type(), 400), $imgsize + 20, $this->y, 'UTF-8');

                    //$this->y -= $this->_LINE_HEIGHT;
                    //$decision = mage::helper('ProductReturn')->__('Decision').' '.mage::helper('ProductReturn')->__($product->getrp_action());
                    //$page->drawText($this->TruncateTextToWidth($page, $decision, 200), $imgsize+20, $this->y, 'UTF-8');

                    $comments = mage::helper('ProductReturn')->__('Comments : ') . $product->getrp_description();
                    if ($product->getrp_serials() != '') {
                        $comments .= "\n" . mage::helper('ProductReturn')->__('Serials : %s', $product->getrp_serials());
                    }
                    $comments = $this->WrapTextToWidth($page, $comments, 450);

                    $this->y -= $this->_LINE_HEIGHT;
                    $offset = $this->DrawMultilineText($page, $comments, $imgsize + 20, $this->y, 10, 0.2, 11);
                    $this->y -= $offset + $this->_LINE_HEIGHT;

                    $itemrealsize = $itemrealsize - $this->y;
                    if ($itemrealsize < $this->_ITEM_HEIGHT) {
                        $this->y -= ($this->_ITEM_HEIGHT - $itemrealsize);
                    }


                    $page->setFillColor(new Zend_Pdf_Color_GrayScale(1));
                    $page->drawLine(10, $this->y, $this->_BLOC_ENTETE_LARGEUR, $this->y);
                    $this->y -= $this->_LINE_HEIGHT + $this->_LINE_HEIGHT / 2;


                }

                //next page
                if ($this->y < ($this->_BLOC_FOOTER_HAUTEUR + $this->_ITEM_HEIGHT)) {
                    $this->drawFooter($page);
                    $page = $this->NewPage($settings);
                    //$this->drawTableHeader($page);
                    $this->y -= $this->_LINE_HEIGHT * 1.5;
                }

            }

            //comments
            $this->y -= 5;
            $page->drawLine(10, $this->y, $this->_BLOC_ENTETE_LARGEUR, $this->y);
            $this->y -= 15;
            $page->setFillColor(new Zend_Pdf_Color_GrayScale(0.3));
            $page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA), 12);

            $comments = $rma->getrma_public_description();
            if (!empty($comments)) {
                $page->drawText(mage::helper('ProductReturn')->__('Comments :'), 15, $this->y, 'UTF-8');
                $comments = $this->WrapTextToWidth($page, $comments, $this->_PAGE_WIDTH);
                $offset   = $this->DrawMultilineText($page, $comments, 15, $this->y - 20, 10, 0.2, 11);
                $this->y -= $offset;

                $this->y -= 35;
                $page->drawLine(10, $this->y, $this->_BLOC_ENTETE_LARGEUR, $this->y);
                $this->y -= 15;
                $page->setFillColor(new Zend_Pdf_Color_GrayScale(0.3));
                $page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA), 12);
            }

            //add pdf comments
            $comments = mage::getStoreConfig('productreturn/pdf/pdf_comment');
            if ($comments != '') {
                $comments = $this->WrapTextToWidth($page, $comments, $this->_PAGE_WIDTH);
                $offset   = $this->DrawMultilineText($page, $comments, 15, $this->y - 20, 10, 0.2, 11);
            }

            //si on a plus la place de rajouter le footer, on change de page
            if ($this->y < (150)) {
                $this->drawFooter($page);
                $page = $this->NewPage($settings);
                //$this->drawTableHeader($page);
            }

            //dessine le pied de page
            $this->drawFooter($page);
        }

        //rajoute la pagination
        $this->AddPagination($this->pdf);

        $this->_afterGetPdf();

        return $this->pdf;
    }


    /**
     * Dessine l'entete du tableau avec la liste des produits
     *
     * @param unknown_type $page
     */
    public function drawTableHeader(&$page)
    {

        //entetes de colonnes
        $this->y -= 15;
        $page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA), 10);

        $page->drawText(mage::helper('ProductReturn')->__('Product'), 15, $this->y, 'UTF-8');
        $page->drawText(mage::helper('ProductReturn')->__('Qty'), 250, $this->y, 'UTF-8');
        $page->drawText(mage::helper('ProductReturn')->__('Reason'), 300, $this->y, 'UTF-8');
        $page->drawText(mage::helper('ProductReturn')->__('Request type'), 350, $this->y, 'UTF-8');
        $page->drawText(mage::helper('ProductReturn')->__('Decision'), 450, $this->y, 'UTF-8');

        //barre grise fin entete colonnes
        $this->y -= 8;
        $page->drawLine(10, $this->y, $this->_BLOC_ENTETE_LARGEUR, $this->y);

        $this->y -= 15;
    }

    public function drawHeader(&$page, $title, $StoreId = null)
    {
        parent::drawHeader($page, $title, $StoreId);

        //BARCODE
        $barcode = $this->_currentRma->getrma_ref();
        if ($barcode) {
            try {
                $picture = mage::helper('ProductReturn/Barcode')->getBarcodePicture($barcode);
                if ($picture) {
                    $zendPicture = mage::helper('ProductReturn/Barcode')->pngToZendImage($picture);
                    $width = 100;
                    $height = 30;
                    $page->drawImage($zendPicture, 470, $this->y +5, 470 + $width, $this->y+5 + $height);
                }
            }catch(Exception $ex){
                mage::logException($ex);
            }
        }
    }


}
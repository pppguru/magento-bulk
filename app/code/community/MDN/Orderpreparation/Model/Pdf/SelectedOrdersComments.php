<?php

class MDN_Orderpreparation_Model_Pdf_SelectedOrdersComments extends MDN_Orderpreparation_Model_Pdf_Pdfhelper {

    public function getPdf($orders = array()) {

        $this->_beforeGetPdf();
        $this->_initRenderer('invoice');

        //on cree le pdf que si il n'est pas deja defini( ca permet de mettre plrs documents dans le mm pdf (genre une facture, un BL ....)
        $this->pdf = new Zend_Pdf();
        
        //cree la nouvelle page
        $titre = mage::helper('purchase')->__('Order Comments');
        $settings = array();
        $settings['title'] = $titre;
        $settings['store_id'] = 0;
        $page = $this->NewPage($settings);
        $this->defineFont($page,10,self::FONT_MODE_BOLD);

        $this->y -= $this->_ITEM_HEIGHT * 2;
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0.2));
        $this->defineFont($page,10);

        //Rajoute les commandes avec commentaires
        foreach ($orders as $order) {

            $realOrder = mage::getModel('sales/order')->load($order->getorder_id());

            $comments = mage::helper('Orderpreparation/Comments')->getAll($realOrder);

            //Display
            if (!empty($comments)) {                
                $page->drawText(mage::helper('purchase')->__('Order # ') . $realOrder->getIncrementId(), 15, $this->y, 'UTF-8');
                $comments = $this->WrapTextToWidth($page, $comments, 450);
                $offset = $this->DrawMultilineText($page, $comments, 150, $this->y, 10, 0.2, 11);
                $this->y -= (8 + $offset);
                $page->drawLine(10, $this->y, $this->_BLOC_ENTETE_LARGEUR, $this->y);
                $this->y -= 15;
            }

        }

        //dessine le pied de page
        $this->drawFooter($page);

        //rajoute la pagination
        $this->AddPagination($this->pdf);

        $this->_afterGetPdf();

        return $this->pdf;
    }

}


<?php

class MDN_Shipworks_FrontController extends Mage_Core_Controller_Front_Action {

    /**
     * Main entry
     */
    public function IndexAction() {

       Mage::helper('Shipworks/Request')->process($_GET);
    }

}
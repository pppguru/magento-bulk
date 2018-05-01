<?php

class Wyomind_Googletrustedstores_GenerateController extends Mage_Core_Controller_Front_Action {
    
    public function cancellationsAction() {
        
        $website = $this->getRequest()->getParam('website');
        $ak = $this->getRequest()->getParam('ak');
        
        $activation_key = md5(Mage::getStoreConfig("googletrustedstores/license/activation_key"));
        
        if($activation_key==$ak) {
            try {
                $ret = Mage::getModel('googletrustedstores/googletrustedstores')->generateCancellationsFeed($website,true);
                if (!is_string($ret)) { die('Invalid license'); } else { echo $ret; die(); }
            } catch (Exception $e) {
                die($e->getMessage());
            }
        } else {
            die('Invalid activation key');
        }
        
    }
    
    public function shipmentsAction() {
        
        $website = $this->getRequest()->getParam('website');
        $ak = $this->getRequest()->getParam('ak');
        
        $activation_key = md5(Mage::getStoreConfig("googletrustedstores/license/activation_key"));
        
        if($activation_key==$ak) {
            try {
                $ret = Mage::getModel('googletrustedstores/googletrustedstores')->generateShipmentsFeed($website,true);
                if (!is_string($ret)) { die('Invalid license'); } else { echo $ret; die(); }
            } catch (Exception $e) {
                die($e->getMessage());
            }
        } else {
            die('Invalid activation key');
        }
        
    }
    
}

<?php

class Wyomind_Googletrustedstores_Model_System_Config_Source_Lang {


    
    public function toOptionArray() {
        return Mage::app()->getLocale()->getTranslatedOptionLocales();
    }
    public function toArray() {
        return Mage::app()->getLocale()->getTranslatedOptionLocales();
    }
    

}

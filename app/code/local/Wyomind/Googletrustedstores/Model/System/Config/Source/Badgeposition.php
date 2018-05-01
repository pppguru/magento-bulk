<?php

class Wyomind_Googletrustedstores_Model_System_Config_Source_Badgeposition {

    
    public function toOptionArray() {
        return array(
            array('label' => 'BOTTOM_RIGHT', 'value' => 'BOTTOM_RIGHT'),
            array('label' => 'USER_DEFINED', 'value' => 'USER_DEFINED'),
        );
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray() {
        return array(
            array('label' => 'BOTTOM_RIGHT', 'value' => 'BOTTOM_RIGHT'),
            array('label' => 'USER_DEFINED', 'value' => 'USER_DEFINED'),
        );
    }
    

}

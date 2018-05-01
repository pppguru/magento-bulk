<?php

class Wyomind_Googletrustedstores_Model_System_Config_Source_Updatemethod {

    
    public function toOptionArray() {
        return array(
            array('label' => 'Dynamic link', 'value' => '0'),
            array('label' => 'Cron task schedule', 'value' => '1'),
        );
    }

    public function toArray() {
        return array(
            array('label' => 'Dynamic link', 'value' => '0'),
            array('label' => 'Cron task schedule', 'value' => '1'),
        );
    }
    

}

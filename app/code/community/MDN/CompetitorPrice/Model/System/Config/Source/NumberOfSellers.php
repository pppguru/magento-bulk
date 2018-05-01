<?php

class MDN_CompetitorPrice_Model_System_Config_Source_NumberOfSellers extends Mage_Eav_Model_Entity_Attribute_Source_Abstract {

    protected $_options;

    public function toOptionArray() {

        if (!$this->_options) {
            $this->getAllOptions();
        }
        return $this->_options;
    }

    public function getAllOptions() {
        if (!$this->_options) {
            $this->_options = array();

            for($i=1;$i<=5;$i++)
                $this->_options[] = array( 'value' => $i, 'label' => $i);

        }
        return $this->_options;
    }


}
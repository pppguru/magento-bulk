<?php

class MDN_SmartReport_Model_System_Config_Source_ReportsForDashboard extends Mage_Eav_Model_Entity_Attribute_Source_Abstract {

    protected $_options;

    public function getAllOptions() {
        $options = null;

        if (!$this->_options) {

            foreach (Mage::getSingleton('SmartReport/Report')->getReports() as $report) {
                if ($report->getavailable_for_dashboard()) {
                    $options[] = array(
                        'value' => $report->getId(),
                        'label' => Mage::helper('SmartReport')->__(ucfirst($report->getGroup())).' - '.Mage::helper('SmartReport')->__($report->getName()),
                    );
                }
            }

            $this->_options = $options;
        }
        return $this->_options;
    }

    public function toOptionArray() {
        return $this->getAllOptions();
    }

}

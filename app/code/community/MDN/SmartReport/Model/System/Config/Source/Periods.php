<?php

class MDN_SmartReport_Model_System_Config_Source_Periods extends Mage_Eav_Model_Entity_Attribute_Source_Abstract {

    public function getAllOptions() {
        if (!$this->_options) {

            $this->_options = array();

            $this->_options[] = array('value' => ('today'), 'label' => Mage::helper('SmartReport')->__('Today'));
            $this->_options[] = array('value' => ('yesterday'), 'label' => Mage::helper('SmartReport')->__('Yesterday'));
            $this->_options[] = array('value' => ('this_week'), 'label' => Mage::helper('SmartReport')->__('This week'));
            $this->_options[] = array('value' => ('this_month'), 'label' => Mage::helper('SmartReport')->__('This month'));
            $this->_options[] = array('value' => ('last_month'), 'label' => Mage::helper('SmartReport')->__('Last month'));
            $this->_options[] = array('value' => ('last_30_days'), 'label' => Mage::helper('SmartReport')->__('Last 30 days'));
            $this->_options[] = array('value' => ('last_3_months'), 'label' => Mage::helper('SmartReport')->__('Last 3 months'));
            $this->_options[] = array('value' => ('last_6_months'), 'label' => Mage::helper('SmartReport')->__('Last 6 months'));
            $this->_options[] = array('value' => ('last_12_months'), 'label' => Mage::helper('SmartReport')->__('Last 12 months'));
            $this->_options[] = array('value' => ('this_year'), 'label' => Mage::helper('SmartReport')->__('This year'));
            $this->_options[] = array('value' => ('lifetime'), 'label' => Mage::helper('SmartReport')->__('Lifetime'));
            $this->_options[] = array('value' => 'custom', 'label' => Mage::helper('SmartReport')->__('Custom'));

        }
        return $this->_options;
    }

    public function getAllOptionsWitDates()
    {
        $options = array();

        $options[] = array('value' => $this->buildValue('today'), 'label' => Mage::helper('SmartReport')->__('Today'));
        $options[] = array('value' => $this->buildValue('yesterday'), 'label' => Mage::helper('SmartReport')->__('Yesterday'));
        $options[] = array('value' => $this->buildValue('this_week'), 'label' => Mage::helper('SmartReport')->__('This week'));
        $options[] = array('value' => $this->buildValue('this_month'), 'label' => Mage::helper('SmartReport')->__('This month'));
        $options[] = array('value' => $this->buildValue('last_month'), 'label' => Mage::helper('SmartReport')->__('Last month'));
        $options[] = array('value' => $this->buildValue('last_30_days'), 'label' => Mage::helper('SmartReport')->__('Last 30 days'));
        $options[] = array('value' => $this->buildValue('last_3_months'), 'label' => Mage::helper('SmartReport')->__('Last 3 months'));
        $options[] = array('value' => $this->buildValue('last_6_months'), 'label' => Mage::helper('SmartReport')->__('Last 6 months'));
        $options[] = array('value' => $this->buildValue('last_12_months'), 'label' => Mage::helper('SmartReport')->__('Last 12 months'));
        $options[] = array('value' => $this->buildValue('this_year'), 'label' => Mage::helper('SmartReport')->__('This year'));
        $options[] = array('value' => $this->buildValue('lifetime'), 'label' => Mage::helper('SmartReport')->__('Lifetime'));
        $options[] = array('value' => 'custom', 'label' => Mage::helper('SmartReport')->__('Custom'));

        return $options;
    }

    public function toOptionArray() {
        return $this->getAllOptions();
    }

    public function buildValue($code)
    {
        $from = '';
        $to = '';

        switch($code)
        {
            case 'today':
                $from = date('Y-m-d');
                $to = date('Y-m-d');
                break;
            case 'yesterday':
                $from = date('Y-m-d', time() - 3600 * 24);
                $to = date('Y-m-d', time() - 3600 * 24);
                break;
            case 'this_week':
                $from = date('Y-m-d', time() - 3600 * 24 * (date('N') - 1));
                $to = date('Y-m-d');
                break;
            case 'this_month':
                $from = date('Y-m-01');
                $to = date('Y-m-d');
                break;
            case 'last_month':
                $lastMonth = (date('m') > 1 ? date('m') - 1 : 12);
                $from = date('Y-'.$lastMonth.'-01');
                $to = date('Y-'.$lastMonth.'-31');
                break;
            case 'last_30_days':
                $from = date('Y-m-d', time() - 3600 * 24 * 30);
                $to = date('Y-m-d');
                break;
            case 'last_3_months':
                $month = date('m') - 3;
                $year = date('Y');
                if ($month <= 0) {
                    $year--;
                    $month = 12 + $month;
                }
                $from = date($year.'-'.$month.'-d');
                $to = date('Y-m-d');
                break;
            case 'last_6_months':
                $month = date('m') - 6;
                $year = date('Y');
                if ($month <= 0) {
                    $year--;
                    $month = 12 + $month;
                }
                $from = date($year.'-'.$month.'-d');
                $to = date('Y-m-d');
                break;
            case 'last_12_months':
                $month = date('m') - 12;
                $year = date('Y') - 1;
                if ($month <= 0)
                    $month = 12 + $month;
                $from = date($year.'-'.$month.'-d');
                $to = date('Y-m-d');
                break;
            case 'this_year':
                $from = date('Y-01-01');
                $to = date('Y-m-d');
                break;
            case 'lifetime':
                $from = date('2005-01-01');
                $to = date('Y-m-d');
                break;
        }

        return $from.'|'.$to;
    }

}
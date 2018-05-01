<?php

class MDN_SmartReport_Helper_Data extends Mage_Core_Helper_Abstract {

    const kvariableKey = 'smart_report_variables';

    public function log($msg)
    {
        Mage::log($msg, null, 'smart_report.log');
    }

    public function getVariables()
    {
        $session = Mage::getSingleton('adminhtml/session');
        $vars = $session->getData(self::kvariableKey);

        if (!$vars)
        {
            $vars = array();
            $vars['group_by_date'] = Mage::getStoreConfig('smartreport/filters/group');
            $vars['period'] = Mage::getSingleton('SmartReport/System_Config_Source_Periods')->buildValue(Mage::getStoreConfig('smartreport/filters/period'));
            list($vars['date_from'], $vars['date_to']) = explode('|', $vars['period']);

            $vars['manufacturer_attribute_id'] = Mage::getStoreConfig('smartreport/attributes/manufacturer_attribute');
            $vars['prefix'] = (string)Mage::getConfig()->getTablePrefix();

            $storeIds = array();
            foreach (Mage::getModel('core/store')->getCollection() as $store)
                $storeIds[] = $store->getId();
            $vars['sm_store'] = implode(',', $storeIds);
        }

        if (strlen($vars['date_from']) <= 10)
            $vars['date_from'] .= ' 00:00:00';
        if (strlen($vars['date_to']) <= 10)
            $vars['date_to'] .= ' 23:59:59';

        return $vars;
    }

    public function setVariable($k, $v)
    {
        $session = Mage::getSingleton('adminhtml/session');
        $vars = $this->getVariables();
        $vars[$k] = $v;
        $session->setData(self::kvariableKey, $vars);
    }

    public function updateVariables($params)
    {
        foreach($params as $k => $v)
        {
            if (($k != 'key') && ($k != 'form_key'))
                $this->setVariable($k, $v);
        }
    }

    public function replaceCodes($string, $codes)
    {
        $codes['order_statuses'] = array();
        $t = explode(',', Mage::getStoreConfig('smartreport/filters/order_statuses'));
        foreach($t as $item)
        {
            $codes['order_statuses'][] = "'".$item."'";
        }
        $codes['order_statuses'] = implode(',', $codes['order_statuses']);

        foreach($codes as $k => $v)
        {
            if (!is_array($v))
                $string = str_replace('{'.$k.'}', $v, $string);
        }
        return $string;
    }

    public function getManufacturerAttributeCode()
    {
        $id = Mage::getStoreConfig('smartreport/attributes/manufacturer_attribute');
        return Mage::helper('SmartReport/Attribute')->getAttributeCode($id);
    }

    public function erpIsInstalled()
    {
        return (Mage::getStoreConfig('advancedstock/erp/is_installed') == 1);
    }

    public function getName()
    {
        if ($this->erpIsInstalled())
            return 'ERP Reports';
        else
            return 'Ultimate Reports';
    }

}

<?php
class AW_Advancedreports_Helper_Additional_Newvsreturning extends Mage_Core_Helper_Abstract
{
    const ROUTE_ADDITIONAL_NEWVSRETURNING = 'additional_newvsreturning';

    public function getChartParams($key)
    {
        $params = array();
        $params[self::ROUTE_ADDITIONAL_NEWVSRETURNING] = array(
            array('value' => 'base_grand_total', 'label' => 'Total'),
        );
        if (isset($params[$key])) {
            return $params[$key];
        }
    }

    public function getNeedReload($key)
    {
        $params = array();
        $params[self::ROUTE_ADDITIONAL_NEWVSRETURNING] = false;
        if (isset($params[$key])) {
            return $params[$key];
        }
    }

    public function getNeedTotal($key)
    {
        $params = array();
        $params[self::ROUTE_ADDITIONAL_NEWVSRETURNING] = true;
        if (isset($params[$key])) {
            return $params[$key];
        }
    }

}



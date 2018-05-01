<?php
    /**
     * aheadWorks Co.
     *
     * NOTICE OF LICENSE
     *
     * This source file is subject to the EULA
     * that is bundled with this package in the file LICENSE.txt.
     * It is also available through the world-wide-web at this URL:
     * http://ecommerce.aheadworks.com/LICENSE-M1.txt
     *
     * @category   AW
     * @package    AW_ARUnits_Salesstatistics
     * @copyright  Copyright (c) 2009-2011 aheadWorks Co. (http://www.aheadworks.com)
     * @license    http://ecommerce.aheadworks.com/LICENSE-M1.txt
     */
?>
<?php
class AW_Advancedreports_Helper_Additional_Salesstatistics extends Mage_Core_Helper_Abstract
{
    const ROUTE_ADDITIONAL_SALESSTATISTICS = 'additional_salesstatistics';

    public function getChartParams($key)
    {
        $params = array();
        $params[self::ROUTE_ADDITIONAL_SALESSTATISTICS] = array(
            array( 'value'=>'base_grand_total', 'label'=>'Total' ),
        );
        if (isset($params[$key])){
            return $params[$key];
        }
    }

    public function getNeedReload($key)
    {
        $params = array();
        $params[self::ROUTE_ADDITIONAL_SALESSTATISTICS] = false;
        if (isset($params[$key])){
            return $params[$key];
        }
    }

    public function getNeedTotal($key)
    {
        $params = array();
        $params[self::ROUTE_ADDITIONAL_SALESSTATISTICS] = true;
        if (isset($params[$key])){
            return $params[$key];
        }
    }

}



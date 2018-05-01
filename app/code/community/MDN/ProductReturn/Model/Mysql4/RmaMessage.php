<?php

/**
 * Class MDN_ProductReturn_Model_Mysql4_RmaMessage
 *
 * @author    Nicolas Mugnier <contact@boostmyshop.com>
 * @copyright 2015-2016 BoostMyShop (http://www.boostmyshop.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MDN_ProductReturn_Model_Mysql4_RmaMessage extends Mage_Core_Model_Mysql4_Abstract {

    protected function _construct()
    {
        $this->_init('ProductReturn/RmaMessage', 'rmam_id');
    }

}
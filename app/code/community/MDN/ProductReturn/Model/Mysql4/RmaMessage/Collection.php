<?php

/**
 * Class MDN_ProductReturn_Model_Mysql4_RmaMessage_Collection
 *
 * @author    Nicolas Mugnier <contact@boostmyshop.com>
 * @copyright 2015-2016 BoostMyShop (http://www.boostmyshop.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MDN_ProductReturn_Model_Mysql4_RmaMessage_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {

    protected function _construct()
    {
        parent::_construct();
        $this->_init('ProductReturn/RmaMessage');
    }

}
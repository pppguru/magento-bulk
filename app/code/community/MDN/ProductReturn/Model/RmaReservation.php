<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright  Copyright (c) 2009 Maison du Logiciel (http://www.maisondulogiciel.com)
 * @author     : Olivier ZIMMERMANN
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MDN_ProductReturn_Model_RmaReservation extends Mage_Core_Model_Abstract
{
    private $_product = null;

    public function _construct()
    {
        parent::_construct();
        $this->_init('ProductReturn/RmaReservation');
    }

    public function getProduct()
    {
        if ($this->_product == null) {
            $this->_product = mage::getModel('catalog/product')->load($this->getrr_product_id());
        }

        return $this->_product;
    }
}
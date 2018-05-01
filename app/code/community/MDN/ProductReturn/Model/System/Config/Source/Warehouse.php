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
class MDN_ProductReturn_Model_System_Config_Source_Warehouse extends Mage_Core_Model_Abstract
{
    public function getAllOptions()
    {
        //check if ERP is installed
        if (!mage::helper('ProductReturn')->erpIsInstalled())
            return array();

        if (!$this->_options) {
            return mage::getModel('ProductReturn/Rma')->getStatuses();
        }

        return $this->_options;
    }

    public function toOptionArray()
    {
        return mage::getModel('AdvancedStock/System_Config_Source_Warehouse')->getAllOptions();
    }
}
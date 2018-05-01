<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This software is designed to work with Magento community edition and
 * its use on an edition other than specified is prohibited. aheadWorks does not
 * provide extension support in case of incorrect edition use.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Catalogpermissions
 * @version    1.3.3
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */

class AW_Catalogpermissions_Model_Product_Attribute_Frontend_Hideprice
    extends Mage_Eav_Model_Entity_Attribute_Frontend_Abstract
{
    protected $_isTranslated = false;

    public function getAttribute()
    {
        if (!$this->_isTranslated) {
            //Note translate
            $_url = Mage::getUrl('adminhtml/system_config/edit', array('section' => 'catalogpermissions'));
            $_linkText = $this->_helper()->__('global value');
            $note = $this->_helper()->__('This value adds to %s', "<a href='{$_url}'>{$_linkText}</a>");
            $this->_attribute->setData('note', $note);

            $this->_isTranslated = true;
        }
        return parent::getAttribute();
    }

    protected function _helper()
    {
        return Mage::helper('catalogpermissions');
    }
}

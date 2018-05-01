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
class MDN_ProductReturn_Helper_PredefinedText extends Mage_Core_Helper_Abstract
{
    /**
     * return predefined text
     *
     * @return array <type>
     */
    public function getItems()
    {
        $all = Mage::getStoreConfig('productreturn/predefined_msg/messages');
        $t   = explode("\n", $all);

        return $t;
    }
}
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


class AW_Catalogpermissions_Model_AdminhtmlObserver
{

    /**
     * Check and update variables from product update
     *
     * @param Varien_Event_Observer $event
     */
    public function catalogProductSave($event)
    {
        $disable_product = AW_Catalogpermissions_Helper_Data::CP_DISABLE_PRODUCT;
        $disable_price = AW_Catalogpermissions_Helper_Data::CP_DISABLE_PRICE;
        /** @var Mage_Core_Controller_Varien_Action $controller_action */
        $controller_action = $event['controller_action'];

        $product_data = $controller_action->getRequest()->getPost('product');
        if (!isset($product_data[$disable_product]) or !is_array($product_data[$disable_product])) {
            $product_data[$disable_product] = array();
        }
        if (!isset($product_data[$disable_price]) or !is_array($product_data[$disable_price])) {
            $product_data[$disable_price] = array();
        }
        $controller_action->getRequest()->setPost('product', $product_data);
    }

    /*
     *  
     * Reinit customer cache sorted by groups of customers
     * 
     */

    public function massenablePostdispatch($observer)
    {

        $types = $observer->getControllerAction()->getRequest()->getParam('types');
        if (!empty($types)) {
            foreach ($types as $type) {
                if ($type == AW_Catalogpermissions_Model_Cache::CACHE_TYPE
                    && Mage::getModel('core/cache')->canUse(
                        AW_Catalogpermissions_Model_Cache::CACHE_TYPE
                    )
                ) {
                    Mage::getModel('catalogpermissions/cache')->refreshCache();
                }
            }
        }

    }

    /*      
     * Reinit customer cache sorted by groups of customers
     */

    public function massrefreshPostdispatch($observer)
    {
        $this->massenablePostdispatch($observer);
    }


    public function abstractModelSaveAfter($observer)
    {

        if ($observer->getObject() instanceof Mage_Customer_Model_Group
            || $observer->getObject() instanceof Mage_Core_Model_Store
        ) {
            if ($observer->getObject()->isObjectNew()) {
                Mage::getModel('core/cache')->invalidateType(AW_Catalogpermissions_Model_Cache::CACHE_TYPE);
            }
        }

        if ($observer->getObject() instanceof Mage_Catalog_Model_Product) {

            $observer->setProduct($observer->getObject());

            Mage::getModel('catalogpermissions/cache')->productSaveAfter($observer);

        }

    }

}

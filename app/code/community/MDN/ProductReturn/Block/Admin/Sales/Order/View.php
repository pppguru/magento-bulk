<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2008 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml sales order view
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class MDN_ProductReturn_Block_Admin_Sales_Order_View extends Mage_Adminhtml_Block_Sales_Order_View
{

    public function __construct()
    {

        parent::__construct();

        if (Mage::getSingleton('admin/session')->isAllowed('admin/sales/order_view/creatermainorderview')) {
            if ($this->getOrder()->getId()) {
                $this->_addButton('order_new_product_return', array(
                    'label'   => Mage::helper('ProductReturn')->__('New Product Return'),
                    'onclick' => 'setLocation(\'' . $this->getNewProductReturnUrl() . '\')',
                ));
            }
        }

    }

    public function getNewProductReturnUrl()
    {
        return $this->getUrl('adminhtml/ProductReturn_Admin/Edit', array('order_id' => mage::registry('sales_order')->getId()));
    }

}

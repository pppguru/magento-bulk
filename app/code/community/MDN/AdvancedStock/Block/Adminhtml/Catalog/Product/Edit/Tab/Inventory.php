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
 * @copyright   Copyright (c) 2009 Irubin Consulting Inc. DBA Varien (http://www.varien.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MDN_AdvancedStock_Block_Adminhtml_Catalog_Product_Edit_Tab_Inventory extends Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Inventory {

    protected function _toHtml() {
        $parentHtml = parent::_toHtml();

        //if product creation, return parent html
        $product = $this->getProduct();
        if (!$product->getId())
            return $parentHtml;

        //if not creation and hide info is set, make content unvisible and add link to erp view
        if (mage::getStoreConfig('advancedstock/general/replace_magento_inventory_tab') == 1) {

            //show link
            $parentHtml = '<div style="display: none">' . $parentHtml . '</div>';
            $parentHtml .= '<p>' . $this->__('This feature is replaced with ERP product view.') . ' : <a href="' . $this->getUrl('adminhtml/AdvancedStock_Products/Edit', array('product_id' => $this->getProduct()->getId())) . '">' . $this->__('Click here to access to ERP view') . '</a></p>';
            $parentHtml .= '<p>&nbsp;</p>';

            $parentHtml .= '<div class="entry-edit">
                                <div class="entry-edit-head">
                                    <h4 class="icon-head head-edit-form fieldset-legend">' . $this->__('Stocks') . '</h4>
                                </div>
                            ';

            //add product stocks
            $block = $this->getLayout()->createBlock('AdvancedStock/Product_Stocks');
            $block->setProductId($this->getProduct()->getId());
            $block->setTemplate('AdvancedStock/Product/Stocks.phtml');
            $block->setReadOnlyMode();
            $parentHtml .= $block->toHtml();
            $parentHtml .= '</div>';

            $parentHtml .= '<p>&nbsp;</p>';

            //last PO
            $parentHtml .= '<div class="entry-edit">
                                <div class="entry-edit-head">
                                    <h4 class="icon-head head-edit-form fieldset-legend">' . $this->__('Last purchase orders') . '</h4>
                                </div>
                            ';

            //add product stocks
            $block = $this->getLayout()->createBlock('Purchase/Product_Edit_Tabs_AssociatedOrdersGrid');
            $block->setProduct($this->getProduct());
            $block->setReadOnlyMode();
            $parentHtml .= $block->toHtml();
            $parentHtml .= '</div>';


            $parentHtml .= '<p>&nbsp;</p>';

            //pending sales order
            $parentHtml .= '<div class="entry-edit">
                                <div class="entry-edit-head">
                                    <h4 class="icon-head head-edit-form fieldset-legend">' . $this->__('Pending sales order') . '</h4>
                                </div>
                            ';

            //add product stocks
            $block = $this->getLayout()->createBlock('AdvancedStock/Product_Edit_Tabs_PendingSalesOrder');
            $block->setProduct($this->getProduct());
            $block->setReadOnlyMode();
            $parentHtml .= $block->toHtml();
            $parentHtml .= '</div>';

            return $parentHtml;
        }
        else
            return $parentHtml;
    }

}
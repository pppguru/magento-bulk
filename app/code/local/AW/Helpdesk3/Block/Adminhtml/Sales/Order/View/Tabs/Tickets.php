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
 * @package    AW_Helpdesk3
 * @version    3.3.0
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Helpdesk3_Block_Adminhtml_Sales_Order_View_Tabs_Tickets
    extends Mage_Adminhtml_Block_Widget
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{

    /**
     * Get tab label
     *
     * @return string
     */
    public function getTabLabel()
    {
        $collection = Mage::getModel('aw_hdu3/ticket')->getCollection();
        $collection->addNotArchivedFilter();
        $collection->addFilterByOrder($this->_getOrderIncrementId());
        $collection->addAdminUserFilter(Mage::getSingleton('admin/session')->getUser());
        $count = $collection->count();
        if ($count) {
            return Mage::helper('aw_hdu3')->__('Help Desk Tickets (%d)', $count);
        }
        return Mage::helper('aw_hdu3')->__('Help Desk Tickets');
    }

    public function getTabTitle()
    {
        return Mage::helper('aw_hdu3')->__('Help Desk Tickets');
    }

    public function canShowTab()
    {
        return $this->getId() ? true : false;
    }

    /**
     * Retrives order's id from request
     *
     * @return integer
     */
    public function getId()
    {
        return $this->getRequest()->getParam('order_id');
    }

    /**
     * Check if tab is hidden
     *
     * @return boolean
     */
    public function isHidden()
    {
        return false;
    }

    protected function _getOrderIncrementId()
    {
        $order = Mage::getModel('sales/order')->load($this->getId());
        return $order->getIncrementId();
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->getId()) {
            return '';
        }

        $order = Mage::getModel('sales/order')->load($this->getId());
        /** @var Mage_Customer_Model_Customer $customer */
        $customer = Mage::getModel('customer/customer')->load($order->getCustomerId());
        if (!$order->getId()) {
            return '';
        }

        $buttonHtml = '';
        if (
            Mage::helper('aw_hdu3/config')->isActiveDepartments(
                Mage::app()->getStore()->getId()
            )
        ) {
            $buttonHtml .= '<div style="text-align: right; margin-bottom: 0.5em;">';
            $buttonHtml .= $this->getButtonHtml(
                $this->__('Create Ticket'),
                'setLocation(\''
                . $this->getUrl(
                    'helpdesk_admin/adminhtml_ticket/new',
                    array(
                        '_query' => array(
                            'customer_email' => $customer->getEmail(),
                            'customer_name' => $customer->getName(),
                            'order_increment_id' => $this->_getOrderIncrementId(),
                            'return_order_id' => $order->getId()
                        )
                    )
                )
                . '\')',
                'scalable add'
            );
            $buttonHtml .= '</div>';
        }

        /** @var AW_Helpdesk3_Block_Adminhtml_Ticket_Grid $grid */
        $grid = $this->getLayout()->createBlock('aw_hdu3/adminhtml_ticket_grid');

        $grid->setId('orderTicketGrid');
        $grid->setDefaultFilter(array());
        $grid->setFilterVisibility(false);
        $grid->setPagerVisibility(0);
        $grid->setUserMode();
        $grid->setUnnecessaryColumn(array('order_number', 'customer'));
        $grid->setOrderIncrementId($this->_getOrderIncrementId());
        $grid->setOnePage();

        return $buttonHtml.$grid->toHtml();
    }
}

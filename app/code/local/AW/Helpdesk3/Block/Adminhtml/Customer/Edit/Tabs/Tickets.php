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


class AW_Helpdesk3_Block_Adminhtml_Customer_Edit_Tabs_Tickets
    extends Mage_Adminhtml_Block_Widget
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    /**
     * Reference to product objects that is being edited
     *
     * @var Mage_Catalog_Model_Product
     */
    protected $_product = null;

    protected $_config = null;

    /**
     * Get tab label
     *
     * @return string
     */
    public function getTabLabel()
    {
        $title = Mage::helper('aw_hdu3')->__('Help Desk Tickets');
        $customer = Mage::getModel('customer/customer')->load($this->getId());
        if (!$customer->getId()) {
            return $title;
        }
        $collection = Mage::getModel('aw_hdu3/ticket')->getCollection();
        $collection->addNotArchivedFilter();
        $collection->addFilterByCustomerEmail($customer->getEmail());
        if ($count = $collection->getSize()) {
            $title = Mage::helper('aw_hdu3')->__('Help Desk Tickets (%d)', $count);
        }
        return $title;
    }

    public function getTabTitle()
    {
        return Mage::helper('aw_hdu3')->__('Customer Tickets');
    }

    public function canShowTab()
    {
        return $this->getId() ? true : false;
    }

    /**
     * Retrives custmer's id from request
     *
     * @return integer
     */
    public function getId()
    {
        return $this->getRequest()->getParam('id');
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
        /** @var Mage_Customer_Model_Customer $customer */
        $customer = Mage::getModel('customer/customer')->load($this->getId());
        if (!$customer->getId()) {
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
                            'return_customer_id' => $customer->getId()
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

        $grid->setId('customerTicketGrid');
        $grid->setDefaultFilter(array());
        $grid->setFilterVisibility(false);
        $grid->setPagerVisibility(0);
        $grid->setUserMode();
        $grid->setUnnecessaryColumn(array('customer'));
        $grid->setCustomerEmail($customer->getEmail());
        $grid->setOnePage();

        return $buttonHtml.$grid->toHtml();
    }
}

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
 * @package    AW_Marketsuite
 * @version    2.1.0
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Marketsuite_Block_Adminhtml_Order_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('mssOrderGrid');
        $this->setUseAjax(true);
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    /**
     * Get current selected rule id
     *
     * @return int|null
     */
    public function getCurrentRuleId()
    {
        $data = array();
        $filter = $this->getRequest()->getParam('filter');
        if (is_null($filter)) {
            $filter = $this->getParam('filter');
        }
        parse_str(urldecode(base64_decode($filter)), $data);

        if (!empty($data['marketsuite_rule_id'])) {
            return $data['marketsuite_rule_id'];
        }

        return null;
    }

    /**
     * Prepare collection for grid by MSS Rule Id
     * if rule is not specified get all collection
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareCollection()
    {
        if ($this->getCurrentRuleId() !== null) {
            $collection = Mage::getModel('marketsuite/api')->exportOrders($this->getCurrentRuleId());
        } else {
            $collection = Mage::getResourceModel('sales/order_grid_collection');
            $orderTable = $collection->getResource()->getTable('sales/order');

            $collection->getSelect()
                ->join(
                    array('sales_order' => $orderTable),
                    'main_table.entity_id = sales_order.entity_id',
                    'customer_email'
                )
            ;
        }

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Prepare columns
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'real_order_id',
            array(
                 'header' => Mage::helper('sales')->__('Order #'),
                 'width'  => '80px',
                 'type'   => 'text',
                 'index'  => 'increment_id',
                 'filter_index' => 'main_table.increment_id',
            )
        );

        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn(
                'store_id',
                array(
                     'header'          => Mage::helper('sales')->__('Purchased from (store)'),
                     'index'           => 'store_id',
                     'type'            => 'store',
                     'store_view'      => true,
                     'display_deleted' => true,
                     'filter_index' => 'main_table.store_id',
                )
            );
        }

        $this->addColumn(
            'created_at',
            array(
                 'header' => Mage::helper('sales')->__('Purchased On'),
                 'index'  => 'created_at',
                 'type'   => 'datetime',
                 'width'  => '100px',
                 'filter_index' => 'main_table.created_at',
            )
        );

        $this->addColumn(
            'billing_name',
            array(
                 'header' => Mage::helper('sales')->__('Bill to Name'),
                 'index'  => 'billing_name',
            )
        );

        $this->addColumn(
            'shipping_name',
            array(
                 'header' => Mage::helper('sales')->__('Ship to Name'),
                 'index'  => 'shipping_name',
            )
        );

        $this->addColumn(
            'customer_email',
            array(
                'header' => Mage::helper('sales')->__('Email'),
                'index'  => 'customer_email',
                'width'  => '100px',
            )
        );

        $this->addColumn(
            'base_grand_total',
            array(
                 'header'   => Mage::helper('sales')->__('G.T. (Base)'),
                 'index'    => 'base_grand_total',
                 'type'     => 'currency',
                 'currency' => 'base_currency_code',
                 'filter_index' => 'main_table.base_grand_total',
            )
        );

        $this->addColumn(
            'grand_total',
            array(
                 'header'   => Mage::helper('sales')->__('G.T. (Purchased)'),
                 'index'    => 'grand_total',
                 'type'     => 'currency',
                 'currency' => 'order_currency_code',
                 'filter_index' => 'main_table.grand_total',
            )
        );

        $this->addColumn(
            'status',
            array(
                 'header'  => Mage::helper('sales')->__('Status'),
                 'index'   => 'status',
                 'type'    => 'options',
                 'width'   => '70px',
                 'options' => Mage::getSingleton('sales/order_config')->getStatuses(),
                 'filter_index' => 'main_table.status',
            )
        );

        $this->addColumn(
            'action',
            array(
                 'header'    => Mage::helper('sales')->__('Action'),
                 'width'     => '50px',
                 'type'      => 'action',
                 'getter'    => 'getId',
                 'actions'   => array(
                     array(
                         'caption' => Mage::helper('sales')->__('View'),
                         'url'     => array('base' => '*/*/view'),
                         'field'   => 'order_id',
                     )
                 ),
                 'filter'    => false,
                 'sortable'  => false,
                 'index'     => 'stores',
                 'is_system' => true,
            )
        );

        $this->addExportType('*/*/exportCsv', Mage::helper('customer')->__('CSV'));
        $this->addExportType('*/*/exportXml', Mage::helper('customer')->__('XML'));

        parent::_prepareColumns();
    }

    /**
     * Add MSS rule selector before reset button
     *
     * @return string
     */
    public function getResetFilterButtonHtml()
    {
        $filterSelect = $this->getLayout()->createBlock('adminhtml/html_select')->setData(
            array(
                 'id'    => 'marketsuite_rule_id',
                 'title' => Mage::helper('marketsuite')->__('Apply MSS rule:'),
                 'name'  => 'marketsuite_rule_id',
                 'class' => 'select',
                 'style' => 'width:100px;',
                 'value' => $this->getCurrentRuleId(),
            )
        );

        $options = array(
            array(
                'value' => null,
                'label' => Mage::helper('marketsuite')->__('---Please Select---'),
            )
        );

        $options = array_merge(
            $options,
            Mage::getModel('marketsuite/filter')->getActiveRuleCollection()->addSortByName()->toOptionArray()
        );

        $filterSelect->setOptions($options);

        $html = '<span class="filter">' . Mage::helper('marketsuite')->__('Apply MSS rule:') . '&nbsp;'
            . $filterSelect->toHtml()
            . '</span>'
        ;

        return $html . $this->getChildHtml('reset_filter_button');
    }

    /**
     * Get row url for $row
     *
     * @param Varien_Object $row
     *
     * @return bool|string
     */
    public function getRowUrl($row)
    {
        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/view')) {
            return $this->getUrl('*/*/view', array('order_id' => $row->getId()));
        }
        return false;
    }
}
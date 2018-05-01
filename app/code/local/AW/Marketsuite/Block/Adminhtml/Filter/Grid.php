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


class AW_Marketsuite_Block_Adminhtml_Filter_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('filter_id');
        $this->setDefaultSort('name');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('marketsuite/filter_collection')
            ->addOrderCount()
            ->addCustomerCount()
        ;

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'filter_id',
            array(
                 'header' => Mage::helper('marketsuite')->__('ID'),
                 'align'  => 'right',
                 'type'   => 'number',
                 'width'  => '110px',
                 'index'  => 'filter_id',
            )
        );

        $this->addColumn(
            'name',
            array(
                 'header' => Mage::helper('marketsuite')->__('Rule Name'),
                 'align'  => 'left',
                 'index'  => 'name',
            )
        );

        $this->addColumn(
            'order_count',
            array(
                 'index'        => 'order_count',
                 'header'       => Mage::helper('marketsuite')->__('Orders'),
                 'width'        => '110px',
                 'align'        => 'right',
                 'type'         => 'number',
                 'filter_index' => 'order_count',
                 'renderer'     => 'AW_Marketsuite_Block_Adminhtml_Filter_Renderer_Orders',
            )
        );

        $this->addColumn(
            'customer_count',
            array(
                 'index'        => 'customer_count',
                 'header'       => Mage::helper('marketsuite')->__('Customers'),
                 'width'        => '110px',
                 'align'        => 'right',
                 'type'         => 'number',
                 'filter_index' => 'customer_count',
                 'renderer'     => 'AW_Marketsuite_Block_Adminhtml_Filter_Renderer_Customers',
            )
        );

        $this->addColumn(
            'is_active',
            array(
                 'header'  => Mage::helper('marketsuite')->__('Status'),
                 'align'   => 'left',
                 'width'   => '100px',
                 'index'   => 'is_active',
                 'type'    => 'options',
                 'options' => array(
                     1 => $this->__('Active'),
                     0 => $this->__('Inactive'),
                 ),
            )
        );

        $this->addColumn(
            'progress_percent',
            array(
                 'header'   => Mage::helper('marketsuite')->__('Progress'),
                 'width'    => '120',
                 'align'    => 'left',
                 'index'    => 'progress_percent',
                 'filter'   => false,
                 'renderer' => 'AW_Marketsuite_Block_Adminhtml_Filter_Renderer_Progress',
            )
        );

        $this->addColumn(
            'updated_at',
            array(
                 'header' => Mage::helper('marketsuite')->__('Last update'),
                 'width'  => '120',
                 'align'  => 'left',
                 'index'  => 'updated_at',
                 'type'   => 'datetime',
            )
        );

        $this->addColumn(
            'action',
            array(
                 'header'    => Mage::helper('marketsuite')->__('Action'),
                 'width'     => '100',
                 'type'      => 'action',
                 'getter'    => 'getId',
                 'actions'   => array(
                     array(
                         'caption' => Mage::helper('marketsuite')->__('Edit'),
                         'url'     => array('base' => '*/*/edit'),
                         'field'   => 'id',
                     )
                 ),
                 'filter'    => false,
                 'sortable'  => false,
                 'index'     => 'stores',
                 'is_system' => true,
            )
        );

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getFilterId()));
    }
}
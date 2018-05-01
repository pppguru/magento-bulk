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


class AW_Helpdesk3_Block_Adminhtml_Quickresponse_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('awhdu3QuickresponseGrid');
        $this->setDefaultSort('title');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('aw_hdu3/template')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'id',
            array(
                'header' => $this->__('ID'),
                'align'  => 'right',
                'width'  => '50px',
                'index'  => 'id',
            )
        );

        $this->addColumn(
            'is_active',
            array(
                'header'  => $this->__('Status'),
                'align'   => 'right',
                'width'   => '50px',
                'index'   => 'is_active',
                'type'    => 'options',
                'options' => AW_Helpdesk3_Model_Source_Status::toOptionHash()
            )
        );

        $this->addColumn(
            'title',
            array(
                'header' => $this->__('Title'),
                'align'  => 'left',
                'index'  => 'title',
            )
        );

        $this->addColumn(
            'store_ids',
            array(
                'header'    => $this->__('Store'),
                'align'     =>'left',
                'index'     => 'store_ids',
                'type'      => 'store',
                'sortable'  => false,
                'renderer'  => 'aw_hdu3/adminhtml_widget_grid_renderer_storeIds',
                'filter_condition_callback' => array($this, 'filterStore'),
                'width'     => 200,
            )
        );

        $this->addColumn(
            'action',
            array(
                'header'    => $this->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption' => $this->__('Delete'),
                        'url'     => array('base' => '*/*/delete'),
                        'confirm' => $this->__('Are you sure you want do this?'),
                        'field'   => 'id'
                    ),
                    array(
                        'caption' => $this->__('Edit'),
                        'url'     => array('base' => '*/*/edit'),
                        'field'   => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'is_system' => true,
            )
        );
        return parent::_prepareColumns();
    }

    protected function filterStore($collection, $column)
    {
        $collection->addFilterByStoreId($column->getFilter()->getValue());
        return $this;
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('templateIds');
        $this->getMassactionBlock()->addItem(
            'delete',
            array(
                'label'   => $this->__('Delete'),
                'url'     => $this->getUrl('*/*/massDelete'),
                'confirm' => $this->__('Are you sure?')
            )
        );
        return $this;
    }
}
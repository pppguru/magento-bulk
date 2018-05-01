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


class AW_Helpdesk3_Block_Adminhtml_Rejecting_Pattern_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('awhdu3RejectingPatternGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('aw_hdu3/gateway_mail_rejectPattern')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'id',
            array(
                'header' => $this->__('ID'),
                'width'  => '100px',
                'index'  => 'id',
            )
        );

        $this->addColumn(
            'title',
            array(
                'header' => $this->__('Name'),
                'index'  => 'title',
            )
        );

        $this->addColumn(
            'is_active',
            array(
                'header'  => $this->__('Status'),
                'index'   => 'is_active',
                'type'    => 'options',
                'options' => AW_Helpdesk3_Model_Source_Status::toOptionHash(),
                'width'   => '100px',
            )
        );

        $this->addColumn(
            'types',
            array(
                'header'   => $this->__('Scope'),
                'index'    => 'types',
                'type'     => 'options',
                'renderer' => 'aw_hdu3/adminhtml_rejecting_pattern_grid_renderer_types',
                'filter_condition_callback' => array($this, '_filterTypes'),
                'options' => AW_Helpdesk3_Model_Source_Gateway_Mail_RejectPattern::toOptionHash()
            )
        );

        $this->addColumn(
            'pattern',
            array(
                'header' => $this->__('Pattern'),
                'index'  => 'pattern',
            )
        );

        $this->addColumn(
            'actions',
            array(
                'header'    => $this->__('Actions'),
                'width'     => '150px',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption' => $this->__('Edit'),
                        'url'     => array('base' => '*/*/edit'),
                        'field'   => 'id'
                    ),
                    array(
                        'caption' => $this->__('Delete'),
                        'url'     => array('base' => '*/*/delete'),
                        'field'   => 'id',
                        'confirm' => $this->__('Are you sure you want do this?')
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'is_system' => true
            )
        );
        return parent::_prepareColumns();
    }

    protected function _filterTypes($collection, $column)
    {
        $collection->addTypesFilter($column->getFilter()->getValue());
        return $this;
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('patternIds');
        $this->getMassactionBlock()->addItem(
            'delete',
            array(
                'label'   => $this->__('Delete'),
                'url'     => $this->getUrl('*/*/massDelete'),
                'confirm' => $this->__('Are you sure?')
            )
        );

        $this->getMassactionBlock()->addItem(
            'status',
            array(
                'label' => $this->__('Change status'),
                'url'   => $this->getUrl('*/*/massStatus'),
                'additional' => array(
                    'visibility' => array(
                        'name'   => 'status',
                        'type'   => 'select',
                        'class'  => 'required-entry',
                        'label'  => $this->__('Status'),
                        'values' => AW_Helpdesk3_Model_Source_Status::toOptionArray()
                    )
                )
            )
        );
        return $this;
    }
}
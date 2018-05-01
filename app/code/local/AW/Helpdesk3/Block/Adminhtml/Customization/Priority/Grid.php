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


class AW_Helpdesk3_Block_Adminhtml_Customization_Priority_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('awhdu3CustomizationPriorityGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setNoFilterMassactionColumn(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('aw_hdu3/ticket_priority')->getCollection()->addNotDeletedFilter();
        $collection->joinLabelTable();
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
            'status',
            array(
                'header'  => $this->__('Status'),
                'align'   => 'left',
                'width'   => '200px',
                'index'   => 'status',
                'type'    => 'options',
                'options' => AW_Helpdesk3_Model_Source_Status::toOptionHash()
            )
        );
        $this->addColumn(
            'value',
            array(
                'header' => $this->__('Title'),
                'align'  => 'left',
                'width'  => '200px',
                'index'  => 'value',
            )
        );
        $this->addColumn(
            'font_color',
            array(
                'header' => $this->__('Font Color'),
                'align'  => 'left',
                'width'  => '200px',
                'index'  => 'font_color',
            )
        );
        $this->addColumn(
            'background_color',
            array(
                'header' => $this->__('Background Color'),
                'align'  => 'left',
                'width'  => '200px',
                'index'  => 'background_color',
            )
        );
        $this->addColumn(
            'is_system',
            array(
                'header' => $this->__('Is System'),
                'align'  => 'left',
                'width'  => '200px',
                'index'  => 'is_system',
                'type'   => 'options',
                'options' => AW_Helpdesk3_Model_Source_Yesno::toOptionHash()
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
                        'field'   => 'id',
                        'type'   => 'delete'
                    ),
                    array(
                        'caption' => $this->__('Edit'),
                        'url'     => array('base' => '*/*/edit'),
                        'field'   => 'id'
                    )
                ),
                'renderer'  => 'aw_hdu3/adminhtml_widget_grid_renderer_priorityActions',
                'filter'    => false,
                'sortable'  => false,
                'is_system' => true,
            )
        );
        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('priorityIds');
        $this->getMassactionBlock()->setFormFieldName('priorityIds');
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

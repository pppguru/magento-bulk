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


class AW_Helpdesk3_Block_Adminhtml_Department_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('awhdu3DepartmentGrid');
        $this->setDefaultSort('id', 'desc');
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('aw_hdu3/department')->getCollection()->addNotDeletedFilter();
        $this->setCollection($collection);
        parent::_prepareCollection();

        foreach ($collection as $link) {
            if ($link->getStoreIds() && $link->getStoreIds() != 0) {
                $link->setStoreIds(explode(',', $link->getStoreIds()));
            } elseif ($link->getStoreIds() == '') {
                $link->setStoreIds(array('_notset_'));
            } else {
                $link->setStoreIds(array('0'));
            }

        }
        $this->setCollection($collection);
        return $this;
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'id',
            array(
                'header'    => $this->__('ID'),
                'align'     => 'right',
                'width'     => '50px',
                'index'     => 'id',
            )
        );

        $this->addColumn(
            'status',
            array(
                'header'    => $this->__('Status'),
                'align'     => 'left',
                'width'     => '200px',
                'index'     => 'status',
                'type'      => 'options',
                'options'   => AW_Helpdesk3_Model_Source_Status::toOptionHash()
            )
        );

        $this->addColumn(
            'title',
            array(
                'header'    => $this->__('Title'),
                'align'     => 'left',
                'index'     => 'title',
            )
        );

        $stores= Mage::getSingleton('adminhtml/system_store')
            ->getStoreValuesForForm(true, true);
        $stores=array_merge(array(
            'label' => 'Not Visible',
            'value' => ''
        ),$stores);
        $this->addColumn(
            'store_ids',
            array(
                'header'    => $this->__('Visible on'),
                'align'     =>'left',
                'index'     => 'store_ids',
                'type'      => 'store',
                'display_not_set'=>true,
                'store_all'=>true,
                'empty_stores'=>true,
                'sortable'  => false,

                'renderer'  => 'aw_hdu3/adminhtml_widget_grid_renderer_storeIds',
                'filter'=>'aw_hdu3/adminhtml_widget_grid_filter_storeIds',
              //  'filter_condition_callback' => array($this, 'filterStore'),
                'width'     => 200,
               'options' => $stores,
            )
        );

        $this->addColumn(
            'sort_order',
            array(
                'header' => $this->__('Sort Order'),
                'align'  => 'right',
                'width'  => '50px',
                'index'  => 'sort_order',
            )
        );

        $this->addColumn(
            'action',
            array(
                'header'    => $this->__('Action'),
                'width'     => '100px',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption' => $this->__('Delete'),
                        'url'     => array('base' => '*/*/delete'),
                        'confirm' => $this->__('Are you sure you want do this?'),
                        'field'   => 'id',
                        'type' => 'delete'
                    ),
                    array(
                        'caption' => $this->__('Edit'),
                        'url'     => array('base' => '*/*/edit'),
                        'field'   => 'id'
                    )
                ),
                'renderer'  => 'aw_hdu3/adminhtml_widget_grid_renderer_departmentActions',
                'filter'    => false,
                'sortable'  => false,
                'is_system' => true,
            )
        );
        return parent::_prepareColumns();
    }
/*
    protected function filterStore($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return;
        }
        $collection->addFilterByStoreId($column->getFilter()->getValue());
        return $this;
    }
*/
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }
}

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


class AW_Lib_Block_Log_Log_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('awLibLogGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('aw_lib/log_logger')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'date',
            array(
                 'header' => Mage::helper('aw_lib')->__('Date'),
                 'align'  => 'right',
                 'width'  => '170px',
                 'index'  => 'date',
                 'type'   => 'datetime',
            )
        );
        $this->addColumn(
            'id',
            array(
                 'header' => Mage::helper('aw_lib')->__('ID'),
                 'width'  => '70px',
                 'align'  => 'right',
                 'index'  => 'id',
            )
        );
        $this->addColumn(
            'module',
            array(
                 'header' => Mage::helper('aw_lib')->__('Module'),
                 'width'  => '100px',
                 'align'  => 'left',
                 'index'  => 'module',
            )
        );
        $this->addColumn(
            'severity',
            array(
                'header'   => Mage::helper('aw_lib')->__('Type'),
                'width'    => '100px',
                'align'    => 'left',
                'index'    => 'severity',
                'renderer' => 'aw_lib/log_widget_grid_column_renderer_notice',
                'type'     => 'options',
                'options'  => array(
                    'notice'  => "Notice",
                    'warning' => "Warning",
                    'error'   => "Error",
                    'strict'  => "Strict",
                ),
            )
        );
        $this->addColumn(
            'title',
            array(
                 'header' => Mage::helper('aw_lib')->__('Title'),
                 'width'  => '200px',
                 'align'  => 'left',
                 'index'  => 'title',
            )
        );
        $this->addColumn(
            'content',
            array(
                'header'   => Mage::helper('aw_lib')->__('Details'),
                'align'    => 'left',
                'index'    => 'content',
                'renderer' => 'aw_lib/log_widget_grid_column_renderer_content',
            )
        );
        $this->addColumn(
            'file_info',
            array(
                 'header' => Mage::helper('aw_lib')->__('Info'),
                 'align'  => 'left',
                 'width'  => '200px',
                 'index'  => 'file_info',
                 'renderer'     => 'aw_lib/log_widget_grid_column_renderer_info',
            )
        );

        parent::_prepareColumns();
        return $this;
    }
}
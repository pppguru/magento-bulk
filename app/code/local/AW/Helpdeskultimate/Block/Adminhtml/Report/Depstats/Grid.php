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
 * @package    AW_Helpdeskultimate
 * @version    2.10.7
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Helpdeskultimate_Block_Adminhtml_Report_Depstats_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setFilterVisibility(false);
        $this->setPagerVisibility(false);
        $this->setTemplate('report/grid.phtml');
        $this->setUseAjax(false);
        $this->setCountTotals(true);
    }


    protected function _prepareCollection()
    {
        $this->setCollection(Mage::getModel('awcore/logger')->getCollection());
        parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'name',
            array(
                'header'   => $this->__('Department'),
                'sortable' => true,
                'index'    => 'name'
            )
        );
        $this->addColumn(
            'total_tickets',
            array(
                'header'   => $this->__('Total Tickets'),
                'sortable' => true,
                'index'    => 'total_tickets'
            )
        );
        $this->addColumn(
            'total_tickets',
            array(
                'header'   => $this->__('Total Tickets'),
                'sortable' => true,
                'index'    => 'total_tickets'
            )
        );

        $this->addExportType('*/*/exportOrdersCsv', Mage::helper('reports')->__('CSV'));
        $this->addExportType('*/*/exportOrdersExcel', Mage::helper('reports')->__('Excel'));

        return parent::_prepareColumns();
    }
}

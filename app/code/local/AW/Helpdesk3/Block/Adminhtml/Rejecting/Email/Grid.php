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


class AW_Helpdesk3_Block_Adminhtml_Rejecting_Email_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('awhdu3RejectingEmailGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        /** @var AW_Helpdesk3_Model_Resource_Gateway_Mail_Collection $collection */
        $collection = Mage::getModel('aw_hdu3/gateway_mail')->getCollection();
        $collection->addRejectedTitle()
            ->addRejectedFilter()
        ;
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'id',
            array(
                'header'       => $this->__('ID'),
                'width'        => '100px',
                'index'        => 'id',
                'filter_index' => 'main_table.id',
            )
        );

        $this->addColumn(
            'from',
            array(
                'header'       => $this->__('From'),
                'index'        => 'from',
                'filter_index' => 'main_table.from',
            )
        );

        $this->addColumn(
            'subject',
            array(
                'header' => $this->__('Subject'),
                'index'  => 'subject',
            )
        );

        $this->addColumn(
            'pattern_title',
            array(
                'header'       => $this->__('Rejected by Pattern'),
                'index'        => 'pattern_title',
                'filter_index' => 'gmrp.title'
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
                        'caption' => $this->__('Mark as unprocessed'),
                        'url'     => array('base' => '*/*/markAsUnprocessed'),
                        'field'   => 'id',
                    ),
                    array(
                        'caption' => $this->__('Delete'),
                        'url'     => array('base' => '*/*/delete'),
                        'confirm' => $this->__('Are you sure you want do this?'),
                        'field'   => 'id',
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'is_system' => true,
            )
        );
    }

    public function getGridRowUrl($row)
    {
        return null;
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('main_table.id');
        $this->getMassactionBlock()->setFormFieldName('emailIds');
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
                'label' => $this->__('Mark as unprocessed'),
                'url'   => $this->getUrl('*/*/massMarkAsUnprocessed'),
            )
        );
        return $this;
    }
}
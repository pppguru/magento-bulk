<?php
/**
 * NOTICE OF LICENSE
 *
 * You may not sell, sub-license, rent or lease
 * any portion of the Software or Documentation to anyone.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade to newer
 * versions in the future.
 *
 * @category   ET
 * @package    ET_IpSecurity
 * @copyright  Copyright (c) 2012 ET Web Solutions (http://etwebsolutions.com)
 * @contacts   support@etwebsolutions.com
 * @license    http://shop.etwebsolutions.com/etws-license-free-v1/   ETWS Free License (EFL1)
 */

/**
 * Class ET_IpSecurity_Block_Adminhtml_Log_Grid
 */
class ET_IpSecurity_Block_Adminhtml_Log_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->setId('etipsecuritylogGrid');
        $this->setDefaultSort('update_time');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
    }

    /**
     * Prepare grid collection object
     *
     * @return ET_IpSecurity_Block_Adminhtml_Log_Grid $this
     */
    protected function _prepareCollection()
    {
        /** @var ET_IpSecurity_Model_Ipsecuritylog $model */
        $model = Mage::getModel('etipsecurity/ipsecuritylog');

        $collection = $model->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Prepare grid columns
     *
     * @return $this
     * @throws Exception
     */
    protected function _prepareColumns()
    {

        /** @var ET_IpSecurity_Helper_Data $helper */
        $helper = Mage::helper('etipsecurity');

        $this->addColumn('blocked_ip', array(
            'header' => $helper->__('Blocked IP'),
            'align' => 'left',
            'width' => '150px',
            'index' => 'blocked_ip',
        ));

        $this->addColumn('qty', array(
            'header' => $helper->__('Qty blocked'),
            'align' => 'left',
            'width' => '100px',
            'index' => 'qty',
            'type' => 'number',
        ));

        $this->addColumn('last_block_rule', array(
            'header' => $helper->__('Last block rule'),
            'align' => 'left',
            'width' => '300px',
            'index' => 'last_block_rule',
            'renderer' => 'etipsecurity/adminhtml_log_renderer_translaterule',
            'filter' => false,
        ));

        $this->addColumn('create_time', array(
            'header' => $helper->__('First block'),
            'align' => 'left',
            'width' => '160px',
            'index' => 'create_time',
            'type' => 'datetime',
        ));

        $this->addColumn('update_time', array(
            'header' => $helper->__('Last block'),
            'align' => 'left',
            'width' => '160px',
            'index' => 'update_time',
            'type' => 'datetime',
        ));

        $this->addColumn('blocked_from', array(
            'header' => $helper->__('Blocked from'),
            'align' => 'left',
            //'width'     => '100px',
            'index' => 'blocked_from',
        ));

        $this->addExportType('*/*/exportCsv', Mage::helper('customer')->__('CSV'));
        $this->addExportType('*/*/exportXml', Mage::helper('customer')->__('Excel XML'));

        return parent::_prepareColumns();
    }
}

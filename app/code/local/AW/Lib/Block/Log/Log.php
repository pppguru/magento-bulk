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


class AW_Lib_Block_Log_Log extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'log_log';
        $this->_blockGroup = 'aw_lib';
        $this->_headerText = Mage::helper('aw_lib')->__('aheadWorks Extensions Log');

        parent::__construct();

        $this->setTemplate('widget/grid/container.phtml');
        $this->_removeButton('add');
        $this->_addButton('back', array(
            'label'     => $this->getBackButtonLabel(),
            'onclick'   => 'setLocation(\'' . $this->getBackUrl() .'\')',
            'class'     => 'back',
        ));
        $this->_addButton(
            'clear', array(
                 'label'   => Mage::helper('aw_lib')->__('Clear Log'),
                 'onclick' => 'if(confirm(\'' . Mage::helper('aw_lib')->__('Are you sure to clear all log entries?') . '\'))setLocation(\'' . $this->getClearUrl() . '\')',
                 'class'   => 'delete',
        ));
    }  
    
    public function getClearUrl()
    {
        return Mage::getSingleton('adminhtml/url')->getUrl('awlib_admin/log/clear');
    }
    
    public function getBackUrl()
    {
        return Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/system_config/edit', array('section' => 'awall'));
    }
}
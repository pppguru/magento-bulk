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


class AW_Helpdesk3_Block_Adminhtml_Ticket_New extends Mage_Adminhtml_Block_Widget_Form_Container
{

    public function __construct()
    {
        $this->_objectId = 'id';
        $this->_blockGroup = 'aw_hdu3';
        $this->_controller = 'adminhtml_ticket';
        $this->_mode = 'new';
        parent::__construct();
    }

    /**
     * @return string
     */
    public function getHeaderText()
    {
        return $this->__('New Ticket');
    }

    /**
     * @return string
     */
    public function getHeaderCssClass()
    {
        return 'head-' . strtr($this->_controller, '_', '-');
    }

    public function getBackUrl() {
        if ($this->getRequest()->getParam('return_customer_id')) {
            return $this->getUrl('adminhtml/customer/edit/', array('id' => $this->getRequest()->getParam('return_customer_id'), 'tab' => 'Tickets'));
        }
        if ($this->getRequest()->getParam('return_order_id')) {
            return $this->getUrl('adminhtml/sales_order/view/', array('order_id' => $this->getRequest()->getParam('return_order_id'), 'tab' => 'Tickets'));
        }
        return parent::getBackUrl();
    }
}

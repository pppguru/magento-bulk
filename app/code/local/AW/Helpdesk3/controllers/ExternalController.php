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


require_once 'AW/Helpdesk3/controllers/CustomerController.php';
class AW_Helpdesk3_ExternalController extends AW_Helpdesk3_CustomerController
{
    protected $_ignoreAction = array('setRate');

    public function preDispatch()
    {
        parent::preDispatch();
        if (!Mage::helper('aw_hdu3/config')->isAllowExternalViewForTickets()
            && !in_array($this->getRequest()->getActionName(), $this->_ignoreAction)
        ) {
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
            return $this->_redirect('aw_hdu3/customer');
        }
    }

    public function _checkAuth()
    {
        return $this;
    }

    protected function _initTicket()
    {
        $key = $this->getRequest()->getParam('key');
        if (null !== $key) {
            $key = Mage::helper('aw_hdu3/ticket')->decryptExternalKey($key);
            list($email, $ticketId) = @explode(',', $key);
            if (!empty($email) && !empty($ticketId)) {
                $ticketModel = Mage::getModel('aw_hdu3/ticket')->load($ticketId);
                if (null !== $ticketModel->getId()) {
                    Mage::register('current_ticket', $ticketModel);
                    return $this;
                }
            }
        }
        $this->_forward('noRoute');
    }

    public function indexAction()
    {
        $this->_forward('noRoute');
        return $this;
    }

    public function createTicketPostAction()
    {
        $this->_forward('noRoute');
        return $this;
    }

    public function viewTicketAction()
    {
        $this->_initTicket();
        $this->loadLayout();
        $this->getLayout()->getBlock('head')->setTitle($this->__('Help Desk / Ticket Details'));
        $this->renderLayout();
        return $this;
    }

    public function setRateAction()
    {
        $this->_initTicket();
        $ticket = Mage::registry('current_ticket');
        $response = array();
        try {
            $rate = $this->getRequest()->getParam('rate');
            if (!$rate || $rate<0 || $rate > 5) {
                throw new Exception($this->__('Wrong ticket rate specified'));
            }
            if (!Mage::helper('aw_hdu3/config')->isAllowRate()) {
                throw new Exception($this->__('Ticket rating is not allowed'));
            }
            if (!$ticket->customerCanVote()) {
                throw new Exception($this->__("You can't rate this ticket. Rating is only allowed within %d days since the last status update.",AW_Helpdesk3_Model_Ticket::MAX_DAY_ALLOW_RATE));
            }
            $ticket
                ->setRate(intval($rate))
                ->save();
            $response['success'] = true;
        } catch (Exception $e) {
            $response['success'] = false;
            $response['message'] = $e->getMessage();
        }
        if ($this->getRequest()->getParam('ajax')) {
            $response = Mage::helper('core')->jsonEncode($response);
            return $this->getResponse()->setBody($response);
        }
        if ($response['success']) {
            Mage::getSingleton('core/session')->addSuccess($this->__('Thank you for voting!'));
        } else {
            Mage::getSingleton('core/session')->addError($response['message']);
        }
        if (Mage::helper('aw_hdu3/config')->isAllowExternalViewForTickets()) {
            return $this->getResponse()->setRedirect(Mage::helper('aw_hdu3/ticket')->getExternalViewUrl($ticket));
        }
        $this->_redirect('aw_hdu3/customer/viewTicket', array('id' => $ticket->getId()));
    }
}
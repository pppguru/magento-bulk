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


/**
 * Class AW_Helpdesk3_Model_Ticket
 * @method string getId()
 * @method string getDepartmentAgentId()
 * @method string getDepartmentId()
 * @method string getUid()
 * @method string getStatus()
 * @method string getPriority()
 * @method string getCustomerName()
 * @method string getCustomerEmail()
 * @method string getSubject()
 * @method string getOrderIncrementId()
 * @method string getIsLocked()
 * @method string getLockedByDepartmentAgentId()
 * @method string getLockedAt()
 * @method string getStoreId()
 * @method string getCreatedAt()
 * @method string getUpdatedAt()
 */
class AW_Helpdesk3_Model_Ticket extends Mage_Core_Model_Abstract
{
    const ABUSE_IDS = 'sex,wtf,fuc,fuk,fck,ass,hui,dck,pzd,ebl,bla,xep';
    const NOT_ARCHIVED = 0;
    const ARCHIVED = 1;
    const MAX_DAY_ALLOW_RATE = 15;


    public function _construct()
    {
        parent::_construct();
        $this->_init('aw_hdu3/ticket');
    }

    /**
     * @return AW_Helpdesk3_Model_Resource_Ticket_History_Collection
     */
    public function getHistoryCollection()
    {
        $historyCollection = Mage::getModel('aw_hdu3/ticket_history')->getCollection();
        $historyCollection->addFilterByTicketId($this->getId());
        return $historyCollection;
    }

    /**
     * @return AW_Helpdesk3_Model_Resource_Ticket_Status_Collection
     */
    public function getStatusCollection()
    {
        $statusCollection = Mage::getModel('aw_hdu3/ticket_status')->getCollection();
        $statusCollection
            ->joinLabelTable($this->getStoreId())
            ->addActiveFilter();
        return $statusCollection;
    }

    /**
     * @param Mage_Admin_Model_User $user
     *
     * @return bool
     */
    public function isCanViewTicket($user)
    {
        return $this->getDepartment()->isCanViewTicket($user);
    }

    /**
     * @param string $uid
     *
     * @return $this
     */
    public function loadByUid($uid)
    {
        return $this->load($uid, 'uid');
    }

    /**
     * @return bool
     */
    public function isReadOnly()
    {
        if ($this->getIsLocked()) {
            return true;
        }
        return false;
    }

    /**
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        return Mage::getModel('sales/order')->loadByIncrementId($this->getOrderIncrementId());
    }

    /**
     * @return AW_Helpdesk3_Model_Department
     */
    public function getDepartment()
    {
        return Mage::getModel('aw_hdu3/department')->load($this->getDepartmentId());
    }

    /**
     * @return AW_Helpdesk3_Model_Department_Agent
     */
    public function getDepartmentAgent()
    {
        $agent = Mage::getModel('aw_hdu3/department_agent')->load($this->getDepartmentAgentId());
        $agent->setDepartment($this->getDepartment());
        return $agent;
    }

    /**
     * Add history by event type
     *
     * @param int $type
     * @param array $data
     *
     * @return $this
     */
    public function addHistory($type, $data)
    {
        $history = Mage::getModel('aw_hdu3/ticket_history');
        $history
            ->setTicket($this)
            ->processEvent($type, $data);
        return $this;
    }

    /**
     * @param int|null $storeId
     *
     * @return string
     */
    public function getStatusLabel($storeId = null)
    {
        if (null === $storeId) {
            $storeId = $this->getStoreId();
        }
        $status = Mage::getModel('aw_hdu3/ticket_status')->load($this->getStatus());
        return $status->getTitle($storeId);
    }

    /**
     * @param int|null $storeId
     *
     * @return string
     */
    public function getPriorityLabel($storeId = null)
    {
        if (null === $storeId) {
            $storeId = $this->getStoreId();
        }
        $priority = Mage::getModel('aw_hdu3/ticket_priority')->load($this->getPriority());
        return $priority->getTitle($storeId);
    }

    /**
     * Add history events after ticket save
     *
     * @return $this
     */
    protected function _addHistoryAfterSaveTicket()
    {
        $history = Mage::getModel('aw_hdu3/ticket_history');
        $history->ticketAfterSave($this);
        return $this;
    }

    /**
     * @return Mage_Core_Model_Abstract
     */
    protected function _afterSave()
    {
        $this->_addHistoryAfterSaveTicket();
        $this->getDepartment()->getEmailNotification()->sendToCustomerNotificationTicketChanged($this);
        return parent::_afterSave();
    }

    /**
     * @return $this
     */
    protected function _beforeSave()
    {
        $_result = parent::_beforeSave();
        $currentDate = new Zend_Date();
        if ($this->isObjectNew()) {
            $this
                ->setUid($this->_generateUid())
                ->setCreatedAt($currentDate->toString(Varien_Date::DATETIME_INTERNAL_FORMAT))
                ->setUpdatedAt($currentDate->toString(Varien_Date::DATETIME_INTERNAL_FORMAT));
        } else {
            $this->setUpdatedAt($currentDate->toString(Varien_Date::DATETIME_INTERNAL_FORMAT));
        }
        return $_result;
    }

    /**
     * Generates UID for ticket
     *
     * @return string
     */
    protected function _generateUid()
    {
        do {
            $digits = (string)rand(100000, 199999);
            $digits = substr($digits, 1);

            $letters = '';
            $aZ = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
            $aZCount = strlen($aZ);
            for ($i = 0; $i < 3; $i++) {
                $int = rand(0, $aZCount - 1);
                $letters .= $aZ[$int];
            }
            $sameTicket = Mage::getModel('aw_hdu3/ticket')->loadByUid($letters . '-' . $digits);
        } while ($sameTicket->getData() || stripos(self::ABUSE_IDS, $letters) !== false);
        return $letters . '-' . $digits;
    }

    /**
     * @return string
     */
    public function getCustomerFirstName()
    {
        if ($this->getCustomerName()) {
            $customerName = explode(' ', $this->getCustomerName());
            return $customerName[0];
        }
        return '';
    }

    /**
     * @param int $lockValue
     *
     * @return $this
     */
    public function setLock($lockValue)
    {
        $currentAgent = Mage::helper('aw_hdu3/ticket')->getCurrentDepartmentAgent();
        if (null !== $currentAgent->getId()) {
            $currentDate = new Zend_Date();
            $this
                ->setIsLocked($lockValue)
                ->setLockedByDepartmentAgentId($currentAgent->getId())
                ->setLocketAt($currentDate->toString(Varien_Date::DATETIME_INTERNAL_FORMAT))
                ->save();
        }
        return $this;
    }

    public function delete()
    {
        $this->setArchived(self::ARCHIVED)
            ->save();
        return $this;
    }

    /**
     * @param Mage_Admin_Model_User $user
     *
     * @return bool
     */

    public function agentCanViewTicket($user)
    {
        $ticketDepartmentAgentIds = $this->getDepartment()->getAgentCollection()->getAllIds();
        $agent = Mage::getModel('aw_hdu3/department_agent')->loadAgentByUserId($user->getId());
        if ($agent && in_array(($agent->getId()), $ticketDepartmentAgentIds)) {
            return true;
        }
        return false;
    }

    public function customerCanVote()
    {
        $currentTime = new DateTime(Mage::getSingleton('core/date')->gmtDate());
        $diff = $currentTime->diff(new DateTime($this->getUpdatedAt()));
        return ($diff->days < self::MAX_DAY_ALLOW_RATE);
    }
}

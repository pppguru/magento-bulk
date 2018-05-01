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
 * Class AW_Helpdesk3_Model_Department
 * @method string getId()
 * @method string getPrimaryAgentId()
 * @method string getTitle()
 * @method string getStoreIds()
 * @method string getCreatedAt()
 * @method string getStatus()
 * @method string getSortOrder()
 */
class AW_Helpdesk3_Model_Department extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('aw_hdu3/department');
    }

    /**
     * @return AW_Helpdesk3_Model_Department_Notification
     */
    public function getEmailNotification()
    {
        return Mage::getModel('aw_hdu3/department_notification')->loadByDepartmentId($this->getId());
    }

    /**
     * @return AW_Helpdesk3_Model_Department_Permission
     */
    public function getPermission()
    {
        return Mage::getModel('aw_hdu3/department_permission')->loadByDepartmentId($this->getId());
    }

    /**
     * @return AW_Helpdesk3_Model_Resource_Department_Agent_Collection
     */
    public function getAgentCollection()
    {
        $agentCollection = Mage::getModel('aw_hdu3/department_agent')->getCollection()->addNotDeletedFilter();
        $agentCollection->addFilterByDepartmentId($this->getId());
        return $agentCollection;
    }

    public function getPrimaryAgent()
    {
        $agent = Mage::getModel('aw_hdu3/department_agent')->load($this->getPrimaryAgentId());
        $agent->setDepartment($this);
        return $agent;
    }

    /**
     * @param Mage_Admin_Model_User $user
     *
     * @return bool
     */
    public function isCanViewTicket($user)
    {
        return $this->getPermission()->isCanViewTicket($user);
    }

    /**
     * @return AW_Helpdesk3_Model_Gateway
     */
    public function getGateway()
    {
        return Mage::getModel('aw_hdu3/gateway')->loadByDepartmentId($this->getId());
    }

    /**
     * @param array $agentIds
     *
     * @return $this
     */
    public function addDepartmentAgents($agentIds)
    {
        $this->getResource()->addDepartmentAgents($this, $agentIds);
        return $this;
    }

    public function isEnabled() {
        return ($this->getStatus() == AW_Helpdesk3_Model_Source_Status::ENABLED_VALUE);
    }

    public function isPrimary() {
        return ($this->getId() == Mage::helper('aw_hdu3/config')->getDefaultDepartmentId(Mage::app()->getStore()->getId()));
    }

    /**
     * @return $this
     */
    protected function _beforeSave()
    {
        $_result = parent::_beforeSave();
        if ($this->isObjectNew()) {
            $currentDate = new Zend_Date();
            $this
                ->setCreatedAt($currentDate->toString(Varien_Date::DATETIME_INTERNAL_FORMAT))
            ;
        }
        return $_result;
    }
}

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
 * Class AW_Helpdesk3_Block_Adminhtml_Ticket_Edit_Form_Thread_Renderer_Department
 * @method AW_Helpdesk3_Model_Ticket_History getEvent()
 * @method bool getIsShort()
 */
class AW_Helpdesk3_Block_Adminhtml_Ticket_Edit_Form_Thread_Renderer_Department extends Mage_Adminhtml_Block_Template
{
    protected function _toHtml()
    {
        $this->setTemplate('aw_hdu3/ticket/edit/container/thread/department/full.phtml');
        if ($this->getIsShort()) {
            $this->setTemplate('aw_hdu3/ticket/edit/container/thread/department/short.phtml');
        }
        return parent::_toHtml();
    }

    /**
     * @return string
     */
    public function getEventInitiatorName()
    {
        $agentId = $this->getEvent()->getData('initiator_department_agent_id');
        /** @var AW_Helpdesk3_Model_Department_Agent $agent */
        $agent = Mage::getModel('aw_hdu3/department_agent')->load($agentId);
        return $agent->getName();
    }

    /**
     * @return string
     */
    public function getFromDepartmentName()
    {
        $departmentId = $this->getEvent()->getEventData('from');
        /** @var AW_Helpdesk3_Model_Department $department */
        $department = Mage::getModel('aw_hdu3/department')->load($departmentId);
        return $department->getTitle();
    }

    /**
     * @return string
     */
    public function getToDepartmentName()
    {
        $departmentId = $this->getEvent()->getEventData('to');
        /** @var AW_Helpdesk3_Model_Department $department */
        $department = Mage::getModel('aw_hdu3/department')->load($departmentId);
        return $department->getTitle();
    }
}
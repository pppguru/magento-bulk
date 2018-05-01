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


class AW_Helpdesk3_Block_Adminhtml_Ticket_Edit_Form_Details extends Mage_Adminhtml_Block_Widget_Form
{

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(
            array('id' => 'awhdu3-ticket-edit-details-form')
        );
        $form->setUseContainer(false);
        $this->setForm($form);

        $fieldset = $form->addFieldset(
            'awhdu3-ticket-edit-details-fieldset', array('legend' => $this->__('Details'))
        );
        $lockHtml = '&nbsp;<span id="awhdu3-ticket-edit-details-agent-locked" title="' . $this->__('Locked') . '"'
            . 'style="display:' . ($this->getTicket()->getIsLocked()?'inline-block':'none') . '"'
            . '></span>'
        ;
        $fieldset->addField(
            'agent', 'label',
            array(
                'label'              => $this->__('Help Desk Agent'),
                'value'              => $this->getAgentName(),
                'bold'               => true,
                'after_element_html' => "<span id='awhdu3-ticket-edit-details-agent-anchor'></span>" . $lockHtml,
            )
        );
        $fieldset->addField(
            'department', 'label',
            array(
                'label' => $this->__('Department'),
                'value' => $this->getDepartmentName(),
                'bold'  => true,
                'after_element_html' => "<span id='awhdu3-ticket-edit-details-department-anchor'></span>",
            )
        );
        $fieldset->addType('status', 'AW_Helpdesk3_Block_Adminhtml_Ticket_Edit_Form_Details_Status');
        $fieldset->addField(
            'status', 'status',
            array(
                'label'  => $this->__('Status'),
                'value'  => $this->getStatusLabel()
            )
        );
        $fieldset->addType('priority', 'AW_Helpdesk3_Block_Adminhtml_Ticket_Edit_Form_Details_Priority');
        $fieldset->addField(
            'priority', 'priority',
            array(
                'label'  => $this->__('Priority'),
                'value'  => $this->getPriorityLabel()
            )
        );
        $fieldset->addType('order', 'AW_Helpdesk3_Block_Adminhtml_Ticket_Edit_Form_Details_Order');
        $fieldset->addField(
            'order', 'order',
            array(
                'label' => $this->__('Order'),
                'value' => $this->getOrderNumber() ? $this->getOrderNumber() : $this->__('Unassigned'),
            )
        );
        $fieldset->addField(
            'store_view', 'note',
            array(
                'label'   => $this->__('Store View'),
                'text'    => $this->getTicketStoreName(),
                'after_element_html' => "<span id='awhdu3-ticket-edit-details-storeview-anchor'></span>",
            )
        );
        $fieldset->addField(
            'external_view', 'link',
            array(
                'label'  => $this->__('External View'),
                'href'   => $this->getUrlToExternalView(),
                'value'  => $this->__('URL'),
                'target' => '_blank'
            )
        );

        $fieldset->addType('rate', 'AW_Helpdesk3_Block_Adminhtml_Ticket_Edit_Form_Details_Rate');

        if($this->getTicket()->getRate()) {
            $fieldset->addField(
                'rate', 'rate',
                array(
                    'label' => $this->__('Rate'),
                    'value' => $this->getTicket()->getRate(),
                )
            );
        }
        return parent::_prepareForm();
    }

    /**
     * @return AW_Helpdesk3_Model_Ticket
     */
    public function getTicket()
    {
        return Mage::registry('current_ticket');
    }

    /**
     * @return string
     */
    public function getAgentName()
    {
        return $this->getTicket()->getDepartmentAgent()->getName();
    }

    /**
     * @return string
     */
    public function getDepartmentName()
    {
        return $this->getTicket()->getDepartment()->getTitle();
    }

    /**
     * @return string
     */
    public function getStatusLabel()
    {
        return $this->getTicket()->getStatusLabel(Mage::app()->getStore()->getId());
    }

    /**
     * @return string
     */
    public function getPriorityLabel()
    {
        return $this->getTicket()->getPriorityLabel(Mage::app()->getStore()->getId());
    }

    /**
     * @return string
     */
    public function getOrderNumber()
    {
        $orderIncrementId = $this->getTicket()->getOrderIncrementId();
        if (empty($orderIncrementId)) {
            return "";
        }
        return $orderIncrementId;
    }

    public function getTicketStoreName()
    {
        if ($storeId = $this->getTicket()->getStoreId()) {
            $store = Mage::app()->getStore($storeId);
            $name = array(
                $store->getWebsite()->getName(),
                $store->getGroup()->getName(),
                $store->getName()
            );
            return implode("<br/>", $name);
        }
        return "";
    }

    /**
     * @return string
     */
    public function getUrlToExternalView()
    {
        return Mage::helper('aw_hdu3/ticket')->getExternalViewUrl($this->getTicket());
    }
}
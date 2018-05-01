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


class AW_Helpdesk3_Block_Adminhtml_Ticket_New_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(
            array(
                'id'     => 'edit_form',
                'action' => $this->getUrl('*/*/saveNewPost'),
                'method' => 'post',
                'enctype' => 'multipart/form-data'
            )
        );
        $form->setUseContainer(true);
        $this->setForm($form);

        $fieldset = $form->addFieldset(
            'aw_hdu3_ticket_new_form', array('legend' => $this->__('Ticket Information'))
        );
        $fieldset->addField(
            'title', 'text',
            array(
                'name'     => 'title',
                'label'    => $this->__('Subject'),
                'required' => true,
            )
        );
        $fieldset->addField(
            'store_ids', 'select',
            array(
                'name'   => 'store_id',
                'label'  => $this->__('Store ID'),
                'values' => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, false),
                'required' => true,
            )
        );
        $fieldset->addField(
            'department_id', 'select',
            array(
                'name'   => 'department_id',
                'label'  => $this->__('Department'),
                'values' => AW_Helpdesk3_Model_Source_Department::toOptionArray(),
                'required' => true,
            )
        );
        $fieldset->addField(
            'status_id', 'select',
            array(
                'name'   => 'status_id',
                'label'  => $this->__('Status'),
                'values' => AW_Helpdesk3_Model_Source_Ticket_Status::toOptionArray(),
                'required' => true,
            )
        );

        $fieldset->addField(
            'priority_id', 'select',
            array(
                'name'   => 'priority_id',
                'label'  => $this->__('Priority'),
                'values' => AW_Helpdesk3_Model_Source_Ticket_Priority::toOptionArray(),
                'required' => true,
                'value' => AW_Helpdesk3_Model_Source_Ticket_Priority::TODO_VALUE,
            )
        );
        $departmentCollection = Mage::getModel('aw_hdu3/department')->getCollection()->addNotDeletedFilter();
        $_dependBlock = $this->getLayout()->createBlock('adminhtml/widget_form_element_dependence');
        $_dependBlock->addFieldMap($form->getHtmlIdPrefix() . 'department_id', 'department_id');
        foreach ($departmentCollection as $department) {
            $fieldset->addField(
                'department_agent_id' . $department->getId(), 'select',
                array(
                    'name'   => 'department_agent_id' . $department->getId(),
                    'label'  => $this->__('Agent'),
                    'values' => $department->getAgentCollection()->addActiveFilter()->toOptionArray(),
                )
            );
            $_dependBlock
                ->addFieldMap(
                    $form->getHtmlIdPrefix() . 'department_agent_id' . $department->getId(),
                    'department_agent_id' . $department->getId()
                )
                ->addFieldDependence(
                    'department_agent_id' . $department->getId(),
                    'department_id',
                    $department->getId()
                )
            ;
        }
        $fieldset->addType('customer', 'AW_Helpdesk3_Block_Adminhtml_Ticket_New_Form_Customer');
        $fieldset->addField(
            'customer_email', 'customer',
            array(
                'label'    => $this->__('Customer Email'),
                'name'     => 'customer_email',
                'class'    => 'validate-email',
                'required' => true
            )
        );
        $fieldset->addField(
            'customer_name', 'text',
            array(
                'label'    => $this->__('Customer Name'),
                'name'     => 'customer_name',
                'required' => true,
                'value' => $this->getRequest()->getParam('customer_name')
            )
        );
        $fieldset->addField(
            'order_increment_id', 'hidden',
            array(
                'name'     => 'order_increment_id',
                'value' => $this->getRequest()->getParam('order_increment_id')
            )
        );
        $fieldset->addField(
            'return_customer_id', 'hidden',
            array(
                'name'     => 'return_customer_id',
                'value' => $this->getRequest()->getParam('return_customer_id')
            )
        );

        $templateCollection = Mage::getModel('aw_hdu3/template')->getCollection()->addActiveFilter()->toOptionHash();
        $fieldset->addField(
            'quick_response', 'select',
            array(
                'label'  => $this->__('Use Quick Response'),
                'name'   => 'quick_response',
                'values' => $templateCollection,
                'after_element_html' => $this->_prepareObserver() .$this->_getApplyQuickResponseButton()->toHtml()
            )
        );

        $fieldset->addField(
            'awhdu3_content_state', 'hidden',
            array(
                'name'  => 'awhdu3_content_state',
                'value' => Mage::getSingleton('adminhtml/session')->getEditorState() ? Mage::getSingleton('adminhtml/session')->getEditorState() : 'show',
            )
        );

        $fieldset->addField(
            'awhdu3_content', 'editor',
            array(
                'label'  => $this->__('Message'),
                'name'   => 'content',
                'style'  => 'width:500px; height:400px;',
                'config' => $this->_getWysiwygConfig(),
                'required' => true,
                'after_element_html' => $this->_showHideObserver()
            )
        );
        $fieldset->addType('multiattach', 'AW_Helpdesk3_Block_Adminhtml_Ticket_Edit_Form_Reply_Multiattach');
        $fieldset->addField(
            'attach', 'multiattach',
            array(
                'label'    => $this->__('Attach File(s)'),
                'name'     => 'attach[]',
                'multiple' => true
            )
        );
        $departmentId = Mage::helper('aw_hdu3/config')->getDefaultDepartmentId();
        $departmentModel = Mage::getModel('aw_hdu3/department');
        $departmentModel->load($departmentId);
        if ($departmentModel->getId() && $departmentModel->isEnabled()) {
            $form->addValues(array('department_id' => $departmentId));
        }
        $this->setChild('form_after', $_dependBlock);
        return parent::_prepareForm();
    }

    /**
     * @return null|array
     */
    private function _getWysiwygConfig()
    {
        try {
            $config = Mage::getSingleton('cms/wysiwyg_config')->getConfig();
            $config->setData(
                Mage::helper('aw_hdu3')->recursiveReplace(
                    '/helpdesk_admin/',
                    '/' . (string)Mage::app()->getConfig()->getNode('admin/routers/adminhtml/args/frontName') . '/',
                    $config->getData()
                )
            );
        } catch (Exception $ex) {
            $config = null;
        }
        if (Mage::getSingleton('adminhtml/session')->getEditorState() == 'hide') {
            $config->setData('hidden', true);
        }
        return $config;
    }

    /**
     * @return Mage_Adminhtml_Block_Widget_Button
     */
    protected function _getApplyQuickResponseButton()
    {
        $url = $this->getUrl('helpdesk_admin/adminhtml_ticket/ajaxGetQuickResponse');
        $button = $this->getLayout()->createBlock('adminhtml/widget_button');
        $button->setData(
            array(
                'label' => $this->__('Insert at cursor'),
                'on_click' => "awHDU3TicketView.insertQuickResponse('{$url}')",
                'class' => 'aw-hdu3-apply-quick-response'
            )
        );
        return $button;
    }

    protected function _prepareQuickResponseValueList()
    {
        $quickResponseValueList = array();
        $storeCollection = Mage::getSingleton('adminhtml/system_store')->getStoreCollection();
        $storeIds = array();
        foreach ($storeCollection as $store) {
            $storeIds[] = $store->getId();
        }

        foreach ($storeIds as $storeId) {
            $collection = Mage::getModel('aw_hdu3/template')->getCollection();
            $collection->addFilterByStoreId($storeId);
            $collection->addActiveFilter();
            if (count($collection) > 0) {
                $quickResponseValueList[$storeId] = $collection->getAllIds();
            }

        }
        return $quickResponseValueList;
    }

    protected function _prepareObserver()
    {
        $quickResponseValueList = json_encode($this->_prepareQuickResponseValueList());

        $url = $this->getUrl('helpdesk_admin/adminhtml_quickresponse/list');
        $quickResponseListHtml = "<p id=\'quick_response_list\'>Quick Responses are not set up. You can set it up ";
        $quickResponseListHtml .= "<a href=\'{$url}\'>here</a></p>";


        $html = '<script>';
        $html .= "$('quick_response').up().innerHTML += '{$quickResponseListHtml}';";
        $html .= "var optionAvailableList = {$quickResponseValueList};
                  Event.observe($('store_ids'), 'change', function(){
                      awHDU3TicketView.prepareQuickResponseList(optionAvailableList);
                  });
                  Event.observe(window, 'load', function(){
                      awHDU3TicketView.prepareQuickResponseList(optionAvailableList);
                  });";
        $html .= '</script>';
        return $html;

    }

    protected function _showHideObserver()
    {
        $html = '<script>';
        $html .= "Event.observe('toggleawhdu3_content', 'click', function(){
                      if ($('awhdu3_content').getStyle('display') == 'none') {
                          $('awhdu3_content_state').value = 'show';
                      } else {
                          $('awhdu3_content_state').value = 'hide';
                      };
                  });";
        $html .= '</script>';
        return $html;
    }

}

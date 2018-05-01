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


class AW_Helpdesk3_Block_Adminhtml_Ticket_Edit_Form_Reply extends Mage_Adminhtml_Block_Widget_Form
{

    /**
     * @return AW_Helpdesk3_Model_Ticket
     */
    public function getTicket()
    {
        return Mage::registry('current_ticket');
    }


    /**
     * @return AW_Helpdesk3_Model_Resource_Template_Collection
     */
    public function getTemplateCollection()
    {
        $ticket = $this->getTicket();
        $collection = Mage::getModel('aw_hdu3/template')->getCollection();
        $collection->addFilterByStoreId($ticket->getStoreId());
        return $collection;
    }

    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(
            array(
                'id'      => 'awhdu3-ticket-edit-reply-form',
                'action'  => $this->getUrl(
                        'helpdesk_admin/adminhtml_ticket/replyPost',
                        array('id' => $this->getTicket()->getId())
                    ),
                'enctype' => 'multipart/form-data',
                'method'  => 'post'
            )
        );
        $form->setUseContainer(true);
        $this->setForm($form);

        $fieldset = $form->addFieldset('awhdu3-ticket-edit-reply-fieldset', array('legend' => $this->__('Reply')));
        $quickResponseValueList = $this->getTemplateCollection()->addActiveFilter()->toOptionHash();
        if (count($quickResponseValueList) > 0) {
            $fieldset->addField(
                'quick_response', 'select',
                array(
                    'label'  => $this->__('Use Quick Response'),
                    'name'   => 'quick_response',
                    'values' => $quickResponseValueList,
                    'after_element_html' => $this->_getApplyQuickResponseButton()->toHtml()
                )
            );
        } else {
            $fieldset->addField(
                'quick_response', 'link',
                array(
                    'label' => $this->__('Use Quick Response'),
                    'href'  => $this->getUrl('helpdesk_admin/adminhtml_quickresponse/list'),
                    'before_element_html' => $this->__('Quick Responses are not set up. You can set it up '),
                    'value' => $this->__('here')
                )
            );
        }
        $afterElementHtml = "<div style='float:right;padding-top:10px;'>"
            . $this->_getNewStatusSelectHtml() . '&nbsp' . $this->_getPostReplyButton()->toHtml()
            . "</div>"
        ;

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
                'style'  => 'width:99%;height:400px;',
                'config' => $this->_getWysiwygConfig(),
                'after_element_html' => $afterElementHtml . $this->_showHideObserver()
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
        return parent::_prepareForm();
    }

    /**
     * @return Mage_Adminhtml_Block_Widget_Button
     */
    protected function _getApplyQuickResponseButton()
    {
        $url = $this->getUrl(
            'helpdesk_admin/adminhtml_ticket/ajaxGetQuickResponse',
            array('id' => $this->getTicket()->getId())
        );
        $button = $this->getLayout()->createBlock('adminhtml/widget_button');
        $button->setData(
            array(
                'label' => $this->__('Insert at cursor'),
                'on_click' => "awHDU3TicketView.insertQuickResponse('{$url}')"
            )
        );
        return $button;
    }

    /**
     * @return Mage_Adminhtml_Block_Widget_Button
     */
    protected function _getPostReplyButton()
    {
        $button = $this->getLayout()->createBlock('adminhtml/widget_button');
        $button->setData(
            array(
                'label' => $this->__('Reply'),
                'style' => 'min-width:100px;min-height:25px;',
                'type' => 'submit'
            )
        );
        return $button;
    }

    /**
     * @return string
     */
    protected function _getNewStatusSelectHtml()
    {
        $selectHtml = "<select name='status' style='width:auto;font-size:16px;'>";
        $statusOptionHash = AW_Helpdesk3_Model_Source_Ticket_Status::toOptionHash(Mage::app()->getStore()->getId());
        foreach ($statusOptionHash as $statusId => $statusLabel) {
            if ($statusId === AW_Helpdesk3_Model_Source_Ticket_Status::NEW_VALUE) {
                continue;
            }
            switch (intval($this->getTicket()->getStatus())) {
                case AW_Helpdesk3_Model_Source_Ticket_Status::NEW_VALUE:
                case AW_Helpdesk3_Model_Source_Ticket_Status::OPEN_VALUE:
                    $isSelected = $statusId == AW_Helpdesk3_Model_Source_Ticket_Status::WAITING_VALUE;
                    break;
                default:
                    $isSelected = $statusId == $this->getTicket()->getStatus();
            }
            $selectHtml .= "<option value='{$statusId}' "
                . ($isSelected?"selected":"")
                .  ">{$statusLabel}</option>";
        }
        $selectHtml .= "</select>";
        $label = $this->__('New Status:');
        return $label . '&nbsp' . $selectHtml;
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

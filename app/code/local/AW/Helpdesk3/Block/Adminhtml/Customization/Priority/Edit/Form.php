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


class AW_Helpdesk3_Block_Adminhtml_Customization_Priority_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(
            array(
                'id'     => 'edit_form',
                'action' => $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'))),
                'method' => 'post',
            )
        );
        $form->setUseContainer(true);
        $this->setForm($form);

        $generalFieldset = $form->addFieldset(
            'aw_hdu3_priority_form', array('legend' => $this->__('General Details'))
        );

        $_cfg = array(
            'label'  => $this->__('Status'),
            'name'   => 'status',
            'values' => AW_Helpdesk3_Model_Source_Status::toOptionArray()
        );
        /**
         * @var $currentPriority AW_Helpdesk3_Model_Ticket_Priority
         */
        $currentPriority = Mage::registry('current_priority');
        if ($currentPriority->getIsSystem()) {
            $_cfg['note'] = $this->__('System Priority cannot be disabled or deleted');
            if ($currentPriority->isEnabled()) {
                $_cfg['disabled'] = true;
            }
        }
        $generalFieldset->addField('status', 'select', $_cfg);

        $generalFieldset->addField(
            'font_color', 'text',
            array(
                'label'    => $this->__('Font Color'),
                'name'     => 'font_color',
            )
        );
        $generalFieldset->addField(
            'background_color', 'text',
            array(
                'label'    => $this->__('Background Color'),
                'name'     => 'background_color',
            )
        );
        Varien_Data_Form::setFieldsetElementRenderer(
            $this->getLayout()->createBlock('aw_hdu3/adminhtml_form_fieldset_renderer_labelElement',
                '', array('label_values' => Mage::registry('current_priority')->getLabelValues())
            )
        );
        $titlesFieldset = $form->addFieldset('aw_hdu3_priority_form_titles', array('legend' => $this->__('Manage Titles')));
        $titlesFieldset->addField(
            'id', 'hidden',
            array(
                'name' => 'id',
            )
        );
        $form->setValues(Mage::registry('current_priority')->getData());
        return parent::_prepareForm();
    }
}

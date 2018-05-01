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


class AW_Helpdesk3_Block_Adminhtml_Rejecting_Pattern_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
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

        $fieldset = $form->addFieldset(
            'aw_hdu3_rejecting_pattern_form', array('legend' => $this->__('General Information'))
        );
        $fieldset->addField(
            'title', 'text',
            array(
                'name'     => 'title',
                'label'    => $this->__('Title'),
                'required' => true,
            )
        );

        $fieldset->addField(
            'is_active', 'select',
            array(
                'name'     => 'is_active',
                'label'    => $this->__('Status'),
                'required' => true,
                'values'   => AW_Helpdesk3_Model_Source_Status::toOptionArray()
            )
        );
        $fieldset->addField(
            'types', 'multiselect',
            array(
                'name'     => 'types',
                'label'    => $this->__('Scope'),
                'required' => true,
                'values'   => AW_Helpdesk3_Model_Source_Gateway_Mail_RejectPattern::toOptionArray()
            )
        );
        $fieldset->addField(
            'pattern', 'text',
            array(
                'name'     => 'pattern',
                'label'    => $this->__('Pattern'),
                'required' => true,
            )
        );

        $form->setValues(Mage::registry('current_pattern')->getData());
        return parent::_prepareForm();
    }
}

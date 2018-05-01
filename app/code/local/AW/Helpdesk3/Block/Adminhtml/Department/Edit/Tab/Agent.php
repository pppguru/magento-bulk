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


class AW_Helpdesk3_Block_Adminhtml_Department_Edit_Tab_Agent
    extends Mage_Adminhtml_Block_Widget_Form
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    public function getTabLabel()
    {
        return $this->__('Help Desk Agents');
    }

    public function getTabTitle()
    {
        return $this->__('Help Desk Agents');
    }

    public function canShowTab()
    {
        return true;
    }

    public function isHidden()
    {
        return false;
    }

    protected function _prepareForm()
    {
        $this->_initForm();
        return parent::_prepareForm();
    }

    protected function _initForm()
    {
        $form = new Varien_Data_Form();
        $fieldset = $form->addFieldset(
            'aw_hdu3_department_agent_form',
            array('legend' => $this->__('Agents'))
        );
        $fieldset->addField(
            'primary_agent_id', 'select',
            array(
                'label'  => $this->__('Primary User'),
                'name'   => 'primary_agent_id',
                'values' => Mage::getModel('aw_hdu3/department_agent')->getCollection()->addActiveFilter()->toOptionArray(),
            )
        );
        $fieldset->addField(
            'agent_ids', 'multiselect',
            array(
                'label'    => $this->__('Agents'),
                'name'     => 'agent_ids[]',
                'values'   => Mage::getModel('aw_hdu3/department_agent')->getCollection()->addActiveFilter()->toOptionArray(),
                'required' => true
            )
        );
        $form->setValues(Mage::registry('current_department')->getData());
        $this->setForm($form);
    }
}

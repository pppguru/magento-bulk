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


class AW_Helpdesk3_Block_Adminhtml_Department_Edit_Tab_General
    extends Mage_Adminhtml_Block_Widget_Form
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    public function getTabLabel()
    {
        return $this->__('General');
    }

    public function getTabTitle()
    {
        return $this->__('General');
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
            'aw_hdu3_department_general_form',
            array('legend' => $this->__('Department Details'))
        );
        $_cfg = array(
            'label'  => $this->__('Status'),
            'name'   => 'status',
            'values' => AW_Helpdesk3_Model_Source_Status::toOptionArray()
        );
        /**
         * @var $currentDepartment AW_Helpdesk3_Model_Department
         */
        $currentDepartment = Mage::registry('current_department');
        if ($currentDepartment->isPrimary()) {
            $_cfg['note'] = $this->__('Primary Department cannot be disabled or deleted');
            if ($currentDepartment->isEnabled()) {
                $_cfg['disabled'] = true;
            }
        }
        $fieldset->addField('status', 'select', $_cfg);
        $fieldset->addField(
            'store_ids', 'multiselect',
            array(
                'name'   => 'store_ids[]',
                'label'  => $this->__('Visible on'),
                'image'  => $this->getSkinUrl('images/grid-cal.gif'),
                'values' => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true),
            )
        );
        $fieldset->addField(
            'title', 'text',
            array(
                'label'    => $this->__('Title'),
                'name'     => 'title',
                'required' => true,
            )
        );
        $fieldset->addField(
            'sort_order', 'text',
            array(
                'label' => $this->__('Sort Order'),
                'name'  => 'sort_order',
            )
        );
        $form->setValues(Mage::registry('current_department')->getData());
        $this->setForm($form);
    }
}

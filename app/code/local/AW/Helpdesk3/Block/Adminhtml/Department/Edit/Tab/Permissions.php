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


class AW_Helpdesk3_Block_Adminhtml_Department_Edit_Tab_Permissions
    extends Mage_Adminhtml_Block_Widget_Form
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    public function getTabLabel()
    {
        return $this->__('Admin Permissions');
    }

    public function getTabTitle()
    {
        return $this->__('Admin Permissions');
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
            array('legend' => $this->__('Admin Permissions'))
        );
        $departmentOptionValues = AW_Helpdesk3_Model_Source_Department::toOptionArray(
            Mage::registry('current_department')->getId()
        );
        unset($departmentOptionValues[0]);
        $fieldset->addField(
            'department_ids', 'multiselect',
            array(
                'label'  => $this->__('Allowed for Departments'),
                'name'   => 'permission[department_ids]',
                'values' => $departmentOptionValues,
                'note'   => $this->__(
                        'All members of selected departments can view and edit tickets of this department'
                        . ' regardless of other "Admin Permissions" settings'
                    ),
            )
        );
        $fieldset->addField(
            'admin_role_ids', 'multiselect',
            array(
                'label'  => $this->__('Allowed for Roles'),
                'name'   => 'permission[admin_role_ids]',
                'values' => Mage::getModel('admin/roles')->getCollection()->toOptionArray(),
                'note'   => $this->__(
                        'All members of selected roles can view and edit tickets of this department'
                        . ' regardless of other "Admin Permissions" settings'
                    ),
            )
        );
        $permissionData = Mage::registry('current_department')->getData('permission');
        if (null === $permissionData) {
            $permissionData = Mage::registry('current_department')->getPermission()->getData();
        }
        $form->setValues($permissionData);
        $this->setForm($form);
    }
}
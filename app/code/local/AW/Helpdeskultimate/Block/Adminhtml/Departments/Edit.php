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
 * @package    AW_Helpdeskultimate
 * @version    2.10.7
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Helpdeskultimate_Block_Adminhtml_Departments_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        $this->_objectId = 'id';
        $this->_controller = 'adminhtml_departments';
        $this->_blockGroup = 'helpdeskultimate';

        parent::__construct();

        $this->_updateButton('save', 'label', $this->__('Save department'));

        $departmentId = $this->getRequest()->getParam($this->_objectId);
        if (!empty($departmentId)) {
            $this->_updateButton('delete', 'onclick', "deleteConfirm("
                . "'" . $this->__('Are you sure you want to do this?') . "',"
                . "'" . $this->getUrl('*/*/delete/id/' . $this->getRequest()->getParam('id')) . "')"
            );
        }
        $this->_formScripts[] = "
            Validation.add(
                'validate-uniq-email',
                '" . $this->__('This email is already use for gateway! Please use other email for department.') . "',
                function(value) {
                    var gatewayEmails = '" . implode(',', Mage::helper('helpdeskultimate')->getGatewayEmails()) . "';
                    gatewayEmails = gatewayEmails.split(',');
                    if(!gatewayEmails.include(value)) {
                        return true;
                    }
                }
            );
        ";
    }

    public function getHeaderText()
    {
        if (Mage::registry('department')->getId()) {
            return $this->__('Edit Department "%s"', $this->htmlEscape(Mage::registry('department')->getName()));
        } else {
            return $this->__('New Department');
        }
    }
}

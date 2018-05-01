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
 * @package    AW_Marketsuite
 * @version    2.1.0
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Marketsuite_Block_Adminhtml_Filter_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        $this->_objectId = 'id';
        $this->_blockGroup = 'marketsuite';
        $this->_controller = 'adminhtml_filter';

        parent::__construct();

        $this->_updateButton('save', 'label', Mage::helper('marketsuite')->__('Save Rule'));
        $this->_updateButton('save', 'onclick', 'saveTab()');
        $this->_updateButton('delete', 'label', Mage::helper('marketsuite')->__('Delete Rule'));
        $this->_addButton(
            'save_and_continue',
            array(
                 'label'   => Mage::helper('customer')->__('Save And Continue Edit'),
                 'onclick' => "saveAndContinueEdit('{$this->_getSaveAndContinueUrl()}')",
                 'class'   => 'save',
            ),
            10
        );

        if (Mage::registry('marketsuitefilters_data')->getId()) {
            $this->_addButton(
                'save_as',
                array(
                     'label'   => Mage::helper('customer')->__('Save As'),
                     'onclick' => 'saveAs()',
                     'class'   => 'save',
                ),
                10
            );
        }

        $this->_addButton(
            'reindex',
            array(
                 'label'   => Mage::helper('customer')->__('Save And Reindex'),
                 'onclick' => 'saveAndReindex()',
                 'class'   => 'save',
            ),
            10
        );
    }

    protected function _getSaveAndContinueUrl()
    {
        return $this->getUrl(
            '*/*/saveRule',
            array(
                 '_current'   => true,
                 'back'       => 'edit',
                 'active_tab' => '{{tab_id}}',
            )
        );
    }

    public function getHeaderText()
    {
        $rule = Mage::registry('marketsuitefilters_data');
        if ($rule->getFilterId()) {
            return Mage::helper('marketsuite')->__("Edit Rule '%s'", $this->escapeHtml($rule->getName()));
        } else {
            return Mage::helper('marketsuite')->__('New Rule');
        }
    }
}
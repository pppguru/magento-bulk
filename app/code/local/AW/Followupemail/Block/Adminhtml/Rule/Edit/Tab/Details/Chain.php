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
 * @package    AW_Followupemail
 * @version    3.5.8
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */

class AW_Followupemail_Block_Adminhtml_Rule_Edit_Tab_Details_Chain
    extends Mage_Adminhtml_Block_Widget
    implements Varien_Data_Form_Element_Renderer_Interface
{
    public function __construct()
    {
        $this->setTemplate('followupemail/rule/edit/details/chain.phtml');
    }

    public function isMultiWebsites()
    {
        return !Mage::app()->isSingleStoreMode();
    }

    public function getEmailTemplates()
    {
        $result = array(0 => $this->__('--- Select Template ---'));
        $result = array_merge($result, Mage::getModel('followupemail/source_rule_template')->getEmailTemplates());
        return $result;
    }

    public function getValues()
    {
        $data = $this->getElement()->getValue();
        if (!is_array($data)) {
            $data = array();
        }
        return $data;
    }

    protected function _prepareLayout()
    {
        $this->setChild(
            'add_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')->setData(
                array(
                    'label' => $this->__('Add email'),
                    'onclick' => 'emailsControl.addItem()',
                    'class' => 'add'
                )
            )
        );
        return parent::_prepareLayout();
    }

    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $this->setElement($element);
        return $this->toHtml();
    }

    public function getAddButtonHtml()
    {
        return $this->getChildHtml('add_button');
    }
}

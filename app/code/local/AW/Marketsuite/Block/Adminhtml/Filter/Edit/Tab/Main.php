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


class AW_Marketsuite_Block_Adminhtml_Filter_Edit_Tab_Main
    extends Mage_Adminhtml_Block_Widget_Form
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    public function getTabLabel()
    {
        return $this->__('Rule Information');
    }

    public function getTabTitle()
    {
        return $this->__('Rule Information');
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
        $model = Mage::registry('marketsuitefilters_data');
        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('rule_');

        $fieldset = $form->addFieldset(
            'base_fieldset', array('legend' => $this->__('General Information'))
        );

        if ($model['filter_id']) {
            $fieldset->addField(
                'filter_id',
                'hidden',
                array('name' => 'filter_id')
            );
        }

        $fieldset->addField(
            'name',
            'text',
            array(
                 'name'     => 'name',
                 'label'    => Mage::helper('catalogrule')->__('Rule Name'),
                 'title'    => Mage::helper('catalogrule')->__('Rule Name'),
                 'required' => true,
            )
        );

        $fieldset->addField(
            'is_active',
            'select',
            array(
                 'label'   => Mage::helper('catalogrule')->__('Status'),
                 'title'   => Mage::helper('catalogrule')->__('Status'),
                 'name'    => 'is_active',
                 'options' => array(
                     '1' => Mage::helper('catalogrule')->__('Active'),
                     '0' => Mage::helper('catalogrule')->__('Inactive'),
                 ),
            )
        );

        $fieldset->addField(
            'save_as_flag',
            'hidden',
            array(
                 'name'  => 'save_as_flag',
                 'value' => 0,
            )
        );

        $form->setValues($model);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}

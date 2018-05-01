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


class AW_Helpdesk3_Block_Adminhtml_Quickresponse_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
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

        $fieldset = $form->addFieldset('template_details', array('legend' => $this->__('Template details')));
        $fieldset->addField(
            'is_active', 'select',
            array(
                'label'  => $this->__('Status'),
                'name'   => 'is_active',
                'values' => AW_Helpdesk3_Model_Source_Status::toOptionArray()
            )
        );
        $fieldset->addField(
            'title', 'text', array(
                'label'    => $this->__('Title'),
                'name'     => 'title',
                'required' => true,
            )
        );

        $fieldset->addField(
            'store_ids', 'multiselect',
            array(
                'name'   => 'store_ids[]',
                'label'  => $this->__('Store'),
                'image'  => $this->getSkinUrl('images/grid-cal.gif'),
                'values' => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true),
                'required' => true,
            )
        );

        $fieldset->addField(
            'content', 'editor',
            array(
                'label'  => $this->__('Content'),
                'name'   => 'content',
                'style'  => 'width:500px; height:400px;',
                'config' => $this->_getWysiwygConfig(),
            )
        );

        $form->setValues(Mage::registry('current_template')->getData());
        return parent::_prepareForm();
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
        return $config;
    }
}
<?php
class Bulksupplements_CustomReports_Block_Adminhtml_Skubycustomer_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();

		$form->setHtmlIdPrefix('sku_by_customer_report_');

        $fieldset = $form->addFieldset('items_fieldset', array(
                'legend' => '',
                'class' => 'sku_by_customer_report',
            )
        );
		
		$reportHeader = $fieldset->addField('reportheadersection', 'text', array('label' => '', 'name' => 'reportheader'));
        $headerBlock = Mage::getSingleton('core/layout')->createBlock('customreports/adminhtml_skubycustomer_components_header','header_block',array());
		$headerBlock->setTemplate('customreports/skubycustomer/components/header.phtml');
        $reportHeader->setRenderer($headerBlock);
		
		$skubycustomergrid = $fieldset->addField('skubycustomergrid', 'text', array('label' => '', 'name' => 'skubycustomergrid'));
		$gridBlock = Mage::getSingleton('core/layout')->createBlock('customreports/adminhtml_skubycustomer_components_customergrid', 'skubycustomer_grid_block', array());
		$skubycustomergrid->setRenderer($gridBlock);
		
		$this->setForm($form);
        return parent::_prepareForm();
    }
}

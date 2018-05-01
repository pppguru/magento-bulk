<?php


class MDN_SmartReport_Block_Report_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    public function getReport()
    {
        return Mage::registry('smart_report_current_report');
    }

    protected function _prepareForm()
    {
        $report = $this->getReport();

        $form = new Varien_Data_Form(array(
            'id'        => 'edit_form',
            'action'    => $this->getUrl('*/*/save', array('id' => $report->getId())),
            'method'    => 'post'
        ));

        /**
         * MAIN
         */
        $fieldset = $form->addFieldset('report_details', array('legend' => Mage::helper('SmartReport')->__('Report Details'), 'class' => 'fieldset-wide'));

        $element = $fieldset->addField('name', 'text', array(
            'label'     => Mage::helper('SmartReport')->__('Name'),
            'required'  => true,
            'name'      => 'data[name]'
        ));


        $element = $fieldset->addField('type', 'select', array(
            'label'     => Mage::helper('SmartReport')->__('Type'),
            'name'      => 'data[type]',
            'values'    => Mage::getModel('SmartReport/System_Config_Source_ReportType')->toOptionArray(),
        ));

        $element = $fieldset->addField('display_in_dashboard', 'select', array(
            'label'     => Mage::helper('SmartReport')->__('Show in dashboard'),
            'required'  => true,
            'values'    => Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray(),
            'name'      => 'data[display_in_dashboard]'
        ));

        /**
         * FILTERS
         */
        $fitlerFieldset = $form->addFieldset('report_filters', array('legend' => Mage::helper('SmartReport')->__('Filters'), 'class' => 'fieldset-wide'));

        /**
         * Aggreagates
         */
        $aggregatesFieldset = $form->addFieldset('report_aggregates', array('legend' => Mage::helper('SmartReport')->__('Aggregates'), 'class' => 'fieldset-wide'));

        $form->setUseContainer(true);
        $values = $report->getData();
        $form->setValues($values);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    protected function getPriorityValues()
    {
        $values = array();
        for($i=1;$i<=10;$i++)
        {
            $values[$i] = $i;
        }
        return $values;
    }

    protected function addContentForCustomFormula(&$contentSet)
    {

        $contentSet->addField('content[minimum_price]', 'text', array(
            'label'     => Mage::helper('Mpm')->__('Standard minimum price'),
            'required'  => true,
            'name'      => 'content[minimum_price]',
            'note'   => $this->__('Formula used to calculate the minimum price when behaviour is standard')
        ));

        $contentSet->addField('content[aggressive_minimum_price]', 'text', array(
            'label'     => Mage::helper('Mpm')->__('Aggressive minimum price'),
            'required'  => true,
            'name'      => 'content[aggressive_minimum_price]',
            'note'   => $this->__('Formula used to calculate the minimum price when behaviour is aggressive')
        ));

        $contentSet->addField('content[regular_price]', 'text', array(
            'label'     => Mage::helper('Mpm')->__('Price without competitor'),
            'required'  => true,
            'name'      => 'content[regular_price]',
            'note'   => $this->__('Formula used to calculate the price if there is no competitor')
        ));

        $contentSet->addField('content[maximum_price]', 'text', array(
            'label'     => Mage::helper('Mpm')->__('Maximum price'),
            'name'      => 'content[maximum_price]',
            'note'   => 'Maximum price to not exceed (used for imposed sell price)'
        ));

    }

    protected function addContentForCost(&$contentSet)
    {
        $contentSet->addField('content[cost_formula]', 'text', array(
            'label'     => Mage::helper('Mpm')->__('Cost formula'),
            'required'  => true,
            'name'      => 'content[cost_formula]',
            'note'      => $this->__('Formula used to calculate your cost')
        ));
    }

    protected function addContentForMargin($contentSet)
    {
        $contentSet->addField('content[standard_margin]', 'text', array(
            'label'     => Mage::helper('Mpm')->__('Conservative margin'),
            'required'  => true,
            'name'      => 'content[standard_margin]',
            'note'      => $this->__('Minimum margin % for conservative behaviour')
        ));

        $contentSet->addField('content[agressive_margin]', 'text', array(
            'label'     => Mage::helper('Mpm')->__('Moderate margin'),
            'required'  => true,
            'name'      => 'content[agressive_margin]',
            'note'   => $this->__('Minimum margin % for moderate behaviour')
        ));

    }

    protected function addContentForShipping($contentSet)
    {
        $contentSet->addField('content[shipping_method]', 'select', array(
            'label'     => Mage::helper('Mpm')->__('Shipping method'),
            'required'  => true,
            'values'    => Mage::getModel('Mpm/System_Config_ShippingMethod')->toOptionArray(),
            'name'      => 'content[shipping_method]'
        ));

        $contentSet->addField('content[shipping_coefficient]', 'text', array(
            'label'     => Mage::helper('Mpm')->__('Shipping coefficient'),
            'required'  => true,
            'name'      => 'content[shipping_coefficient]',
            'note'      => $this->__('Shipping rate will be multiplied by this coefficient (ie : 1.1 coefficient will increase shipping rate of 10%)')
        ));

        $contentSet->addField('content[shipping_allow_zero]', 'select', array(
            'label'     => Mage::helper('Mpm')->__('Allow zero shipping cost'),
            'required'  => true,
            'values'    => Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray(),
            'name'      => 'content[shipping_allow_zero]',
            'note'      => $this->__('If disabled, an exception is raised if shipping cost calculation is zero')
        ));

    }

    protected function addContentForEnable($contentSet)
    {
        $contentSet->addField('content[enable]', 'select', array(
            'label'     => Mage::helper('Mpm')->__('Enable'),
            'required'  => true,
            'values'    => Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray(),
            'name'      => 'content[enable]',
            'note'      => $this->__('Select if you want to enable repricing for products')
        ));
    }

    protected function addContentForAdditionalCost($contentSet)
    {

        $contentSet->addField('content[additional_cost_param]', 'text', array(
            'label'     => Mage::helper('Mpm')->__('Additional cost param'),
            'required'  => true,
            'name'      => 'content[additional_cost_param]',
            'note'      => $this->__('Use {base_cost} or {shipping} code (or any product attribute) to write the formula')
        ));
    }

    protected function addContentForAdjustment($contentSet)
    {
        $contentSet->addField('content[adjustment_compete_with]', 'select', array(
            'label'     => Mage::helper('Mpm')->__('Compete with'),
            'required'  => true,
            'values'    => Mage::getModel('Mpm/System_Config_CompeteWith')->toOptionArray(),
            'name'      => 'content[adjustment_compete_with]',
            'note'      => $this->__('Select the seller you want to compete with')
        ));

        $contentSet->addField('content[adjustment_method]', 'select', array(
            'label'     => Mage::helper('Mpm')->__('Adjustment method'),
            'required'  => true,
            'values'    => Mage::getModel('Mpm/System_Config_AdjustmentMethod')->toOptionArray(),
            'name'      => 'content[adjustment_method]',
            'note'      => $this->__('Used to adjust your price : substract a percent or a fixed value from best competitor price to calculate your price')
        ));

        $contentSet->addField('content[adjustment_value]', 'text', array(
            'label'     => Mage::helper('Mpm')->__('Adjustment value'),
            'required'  => true,
            'name'      => 'content[adjustment_value]',
            'note'      => $this->__('Fill the percent or the value to apply to the adjustment method')
        ));

    }

    protected function addContentForMinPrice($contentSet)
    {
        $contentSet->addField('content[minimum_price]', 'text', array(
            'label'     => Mage::helper('Mpm')->__('Minimum price'),
            'required'  => true,
            'name'      => 'content[minimum_price]'
        ));
    }

    protected function addContentForMaxPrice($contentSet)
    {
        $contentSet->addField('content[maximum_price]', 'text', array(
            'label'     => Mage::helper('Mpm')->__('Maximum price'),
            'required'  => true,
            'name'      => 'content[maximum_price]'
        ));
    }

    protected function addContentForCommission($contentSet)
    {
        $contentSet->addField('content[commission]', 'text', array(
            'label'     => Mage::helper('Mpm')->__('Commission %'),
            'required'  => true,
            'name'      => 'content[commission]'
        ));
    }

    protected function addContentForNoCompetitor($contentSet)
    {
        $contentSet->addField('content[no_competitor_mode]', 'select', array(
            'label'     => Mage::helper('Mpm')->__('Mode'),
            'required'  => true,
            'values'    => Mage::getModel('Mpm/System_Config_NoCompetitorMode')->toOptionArray(),
            'name'      => 'content[no_competitor_mode]'
        ));

        $contentSet->addField('content[no_competitor_value]', 'text', array(
            'label'     => Mage::helper('Mpm')->__('Value'),
            'required'  => true,
            'name'      => 'content[no_competitor_value]'
        ));
    }

    protected function addContentForShippingPrice($contentSet)
    {
        $contentSet->addField('content[shipping_price_mode]', 'select', array(
            'label'     => Mage::helper('Mpm')->__('Mode'),
            'required'  => true,
            'values'    => Mage::getModel('Mpm/System_Config_ShippingCalculation')->toOptionArray(),
            'name'      => 'content[shipping_price_mode]'
        ));

        $contentSet->addField('content[shipping_price_value]', 'text', array(
            'label'     => Mage::helper('Mpm')->__('Value'),
            'required'  => true,
            'name'      => 'content[shipping_price_value]'
        ));
    }
}

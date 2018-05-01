<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @copyright  Copyright (c) 2009 Maison du Logiciel (http://www.maisondulogiciel.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @package MDN_CrmTicket
 * @version 1.2
 */
class MDN_BackgroundTask_Block_Widget_Grid_Column_Filter_MultiSelect extends Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Abstract
{
    protected function _getOptions()
    {
        $emptyOption = array('value' => null, 'label' => '');

        $optionGroups = $this->getColumn()->getOptionGroups();
        if ($optionGroups) {
            array_unshift($optionGroups, $emptyOption);
            return $optionGroups;
        }

        $colOptions = $this->getColumn()->getOptions();
        if (!empty($colOptions) && is_array($colOptions) ) {
            $options = array($emptyOption);
            foreach ($colOptions as $value => $label) {
                $options[] = array('value' => $value, 'label' => $label);
            }
            return $options;
        }
        return array();
    }

    public function getHtml()
    {
        //parse options
        $value = $this->getValue();
        $checkboxes = '';
        $selectedLabels = array();
        $grid_id=$this->getColumn()->getGrid()->getId();
        $prefixName= $grid_id.'_'.$this->_getHtmlName();

        foreach ($this->_getOptions() as $option)
        {
            if (!$option['label'])
                continue;
            $label = $option['label'];
            $checked = (isset($value[$option['value']]) ? ' checked="checked"' : '' );
            $checkboxes .= '<input '.$checked.' type="checkbox" name="'.$this->_getHtmlName().'['.$option['value'].']" id="'.$this->_getHtmlId().'" value="1">&nbsp;'.$label.'<br>';
            if ($checked != '')
                $selectedLabels[] =  $label;
        }

        //build selected options string
        $labelDivName = $prefixName.'_selected_labels';
        $html = '<div id="'.$labelDivName.'" name="'.$labelDivName.'">';
        $html .= '<img src="'.$this->getSkinUrl('images/fam_page_white_edit.gif').'" onclick="toggleFilterMultiSelectForm(\''.$prefixName.'\')">';
        $html .= implode(', ', $selectedLabels);
        $html .= '</div>';

        //add checkboxes form
        $checkboxDivName = $prefixName.'_checkboxes';
        $html .= '<div id="'.$checkboxDivName.'" name="'.$checkboxDivName.'" style="display: none;">';
        $html .= $checkboxes;
        $html .= '</div>';

        return $html;
    }

    public function getCondition()
    {
        $result = null;

        if (is_array($this->getValue()))
        {
            if(!in_array(MDN_BackgroundTask_Helper_Data::RESULT_NOT_EXECUTED,array_keys($this->getValue()))){
                $result = array('in' => array_keys($this->getValue()));
            }else{
                $result = array(array(MDN_BackgroundTask_Helper_Data::RESULT_NOT_EXECUTED => true),array('in' => array_keys($this->getValue())));
            }
        }

        return $result;
    }

}

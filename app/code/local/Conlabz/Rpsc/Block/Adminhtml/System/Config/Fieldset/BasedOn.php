<?php

/**
 * @package Conlabz_Rpsc
 * @author Cornelius Adams (conlabz GmbH) <cornelius.adams@conlabz.de>
 */
class Conlabz_Rpsc_Block_Adminhtml_System_Config_Fieldset_BasedOn
    extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
    const NAME_TYPE_ID  = 'type_id';
    const NAME_BASED_ON = 'based_on';

    /**
     *
     * @var array
     */
    protected $_rendererSelects = array(
        self::NAME_TYPE_ID  => '_getTypeIdOptions',
        self::NAME_BASED_ON => '_getBasedOnOptions'
    );

    public function __construct()
    {
        $this->addColumn(
            self::NAME_TYPE_ID,
            array(
                'label' => Mage::helper('rpsc')->__('Product Type'),
                'renderer' => 'select',
                'style' => 'width:120px'
            )
        );
        $this->addColumn(
            self::NAME_BASED_ON,
            array(
                'label' => Mage::helper('rpsc')->__('Based on'),
                'style' => 'width:120px'
            )
        );
        $this->_addAfter = false;
        $this->_addButtonLabel = Mage::helper('rpsc')->__('Add Type');
        parent::__construct();
    }

    /**
     * {@inheritDoc}
     */
    protected function _renderCellTemplate($columnName)
    {
        if (!isset($this->_rendererSelects[$columnName])) {
            return parent::_renderCellTemplate($columnName);
        }

        $inputName  = $this->getElement()->getName() . '[#{_id}][' . $columnName . ']';
        $callback   = $this->_rendererSelects[$columnName];
        $options    = $this->{$callback}();

        $rendered = '<select name="' . $inputName . '">';
        foreach ($options as $option) {
            if (!empty($option['value'])) {
                $rendered .= sprintf(
                    '<option value="%s">%s</option>',
                    $option['value'],
                    $option['label']
                );
            }
        }
        $rendered .= '</select>';
        return $rendered;
    }

    /**
     *
     * @return array
     */
    protected function _getTypeIdOptions()
    {
        return Mage::getModel('catalog/product_type')->getAllOptions();
    }

    /**
     *
     * @return array
     */
    protected function _getBasedOnOptions()
    {
        return Mage::getModel('rpsc/system_config_source_basedOn')->toOptionArray();
    }

    /**
     *
     * @return string
     */
    protected function _toHtml()
    {
        $html = '<div id="system_rpsc_based_on">' . parent::_toHtml() . '</div>';
        $html.= '<script type="text/javascript">';
        foreach ($this->getArrayRows() as $row) {
            $id = $row->getData('_id');
            foreach (array_keys($this->_rendererSelects) as $field) {
                $html .= '$$("#' . $id . ' select[name=\\"groups[rpsc][fields][based_on][value]['. $id . '][' . $field . ']\\"")[0].value = "' . $row->getData($field) . '";' . PHP_EOL;
            }
        }
        $html.= '</script>';
        return $html;
    }
}

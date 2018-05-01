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
 * @package    AW_Advancedreports
 * @version    2.5.3
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


/**
 * Setup helper
 */
class AW_Advancedreports_Helper_Setup extends Varien_Object
{
    const TEMPLATE = 'advancedreports/setup.phtml';

    const DATA_KEY_REPORT_ID = 'aw_advancedreports_current_report_id';

    protected $_predefinedDataKeys = array(
        'process_orders'        => 'advancedreports/configuration/process_orders',
        'crossreport_filters'   => 'advancedreports/configuration/crossreport_filters',
        'recently_filter_count' => 'advancedreports/configuration/recently_filter_count',
        'order_datefilter'      => 'advancedreports/configuration/order_datefilter',
        # etc...
    );

    /**
     * Retrieves Advanced Grid
     *
     * @return AW_Advancedreports_Block_Advanced_Grid
     */
    public function getGrid()
    {
        if (!$this->getData('current_grid')) {
            $grid = Mage::app()->getLayout()->createBlock('advancedreports/' . $this->getReportRoute() . '_grid');
            $this->setData('current_grid', $grid);
        }
        return $this->getData('current_grid');
    }

    /**
     * Retrieves link html
     *
     * @return string
     */
    public function getHtml()
    {
        $block = Mage::app()->getLayout()->createBlock('advancedreports/adminhtml_setup');
        if ($block) {
            $block->setTemplate(self::TEMPLATE)->setSetup($this);
            return $block->toHtml();
        }
        return '';
    }

    /**
     * Retrieves customized config data
     *
     * @param string $path
     *
     * @return string
     */
    public function getCustomConfig($path)
    {
        if ($reportId = $this->getReportId()) {
            $value = Mage::getModel('advancedreports/option')
                ->load3params($reportId, $this->getAdminId(), $path)
                ->getValue()
            ;
            if ($value !== null) {
                return $value;
            }
        }
        return null;
    }

    /**
     * Retrieves Admin Id
     *
     * @return integer
     */
    public function getAdminId()
    {
        return Mage::getSingleton('admin/session')->getUser()->getId();
    }

    public function isDefault($key)
    {
        $data = $this->getPreparedData();
        return isset($data[$key . '_use_default']) && $data[$key . '_use_default'];
    }

    /**
     * Retrieves preapred data with collection and some differned magic
     *
     * @param Mage_Core_Model_Mysql4_Collection_Abstract $collection
     *
     * @return array
     */
    public function getPreparedData($collection = null)
    {
        if (!$this->getData('prepared_data')) {
            $data = array();
            foreach ($this->_getPredefinedKeys() as $dataKey => $dataPath) {
                $isDefault = true;
                $value = Mage::getStoreConfig($dataPath);
                foreach ($collection as $item) {
                    if ($item->getPath() == $dataPath) {
                        $value = $item->getValue();
                        $isDefault = false;
                    }
                }
                $data[$dataKey] = $value;
                $data[$dataKey . '_use_default'] = $isDefault;
            }

            if ($grid = $this->getGrid()) {
                if (count($options = $grid->getCustomOptionsRequired())) {
                    foreach ($options as $option) {
                        $value = $option['default'];
                        if (($savedValue = $this->getCustomConfig($option['id'])) !== null) {
                            $value = $savedValue;
                        }
                        $data[$option['id']] = $value;
                    }
                }
            }
            $this->setData('prepared_data', $data);
        }
        return $this->getData('prepared_data');
    }

    /**
     * Predefined default keys
     *
     * @return array
     */
    protected function _getPredefinedKeys()
    {
        return $this->_predefinedDataKeys;
    }

    protected function json2serialize($value)
    {
        try {
            return serialize(json_decode($value));
        } catch (Exception $e) {
        }
        return $value;
    }

    /**
     * Save customized option's data
     *
     * @param array $post
     * @param int $reportId
     *
     * @return AW_Advancedreports_Helper_Setup
     */
    public function savePostData($post, $reportId)
    {
        foreach ($this->_getPredefinedKeys() as $dataKey => $dataPath) {
            # 1. Clean
            if (isset($post[$dataKey . '_use_default']) && $post[$dataKey . '_use_default']) {
                $resource = Mage::getModel('advancedreports/option')->getResource();
                $resource->clearReportOptions($reportId, $this->getAdminId());
            }
        }
        foreach ($this->_getPredefinedKeys() as $dataKey => $dataPath) {
            # 2. Save
            if (isset($post[$dataKey])) {
                $value = $post[$dataKey];
                $this->_saveData($reportId, $dataPath, $value);

            }
        }
        # 3. Save custom options
        if ($grid = $this->getGrid()) {
            if (count($options = $grid->getCustomOptionsRequired())) {
                foreach ($options as $option) {
                    $value = $post[$option['id']];

                    if (isset($option['prepare_value'])) {
                        $method = $option['prepare_value'];
                        $value = $this->$method($value);
                    }

                    $this->_saveData($reportId, $option['id'], $value);
                }
            }
        }
        return $this;
    }

    protected function _saveData($reportId, $dataPath, $value)
    {
        if (is_array($value)) {
            $value = implode(',', $value);
        }

        $option = Mage::getModel('advancedreports/option')->load3params($reportId, $this->getAdminId(), $dataPath);

        if (!$option->getId()) {
            $option
                ->setReportId($reportId)
                ->setAdminId($this->getAdminId());
        }
        $option
            ->setPath($dataPath)
            ->setValue($value)
            ->save();
    }

    /**
     *
     * @return string
     */
    public function getSecureCode()
    {
        return base64_decode(Mage::app()->getRequest()->getParam('sc'));
    }

    /**
     *
     * @return string
     */
    public function getReportTitle()
    {
        return base64_decode(Mage::app()->getRequest()->getParam('title'));
    }

    public function getReportRoute()
    {
        return base64_decode(Mage::app()->getRequest()->getParam('route'));
    }

    public function getReportId()
    {
        return Mage::registry(self::DATA_KEY_REPORT_ID)
            ?
            Mage::registry(self::DATA_KEY_REPORT_ID)
            :
            Mage::app()->getRequest()->getParam('report_id');
    }

    /**
     * Retrieves rendfered Use Default Checkbox
     *
     * @param Varien_Data_Form_Element_Abstract $field
     * @param string                            $name
     * @param boolean                           $checked
     *
     * @return string
     */
    public function getCheckboxScopeHtml($field, $name, $checked = false)
    {
        $checkedHtml = '';
        if ($checked) {
            $checkedHtml = ' checked="checked"';
        }
        $checkbox = '<input type="checkbox" id="' . $field->getId()
            . '_use_default" class="product-option-scope-checkbox" name="' . $field->getId()
            . '_use_default" onchange="chahged' . $field->getId() . '();" value="1" ' . $checkedHtml . '/>';
        $checkbox .= '<label class="normal" for="' . $field->getId() . '_use_default">Use Default Value</label>';
        $checkbox .= "
        <script type=\"text/javascript\">
            function chahged{$field->getId()}(){
                if (typeof($('{$field->getId()}')) != 'undefined'){
                    $('{$field->getId()}').disabled = $('{$field->getId()}_use_default').checked;
                }
            }
        </script>
        ";
        return $checkbox;
    }
}

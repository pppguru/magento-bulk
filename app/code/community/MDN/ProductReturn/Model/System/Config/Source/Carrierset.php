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
 * @author     : Olivier ZIMMERMANN
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MDN_ProductReturn_Model_System_Config_Source_Carrierset extends Mage_Core_Model_Abstract
{
    public function getAllOptions()
    {
        if (!$this->_options) {

            $options  = array();
            $carriers = Mage::getStoreConfig('carriers', 0);

            foreach ($carriers as $carrierKey => $item) {
                if (!isset($item['model']))
                    continue;
                $instance = mage::getModel($item['model']);
                $code = $item['model'];
                if ($item['model']) {
                    try
                    {
                        $model = mage::getModel($item['model']);
                        $allowedMethods = $model->getAllowedMethods();
                        if ($allowedMethods) {
                            foreach ($allowedMethods as $methodKey => $method) {
                                $options[] = array('value' => $carrierKey . '_' . $methodKey, 'label' => $instance->getConfigData('title') . ' - ' . $method
                                );
                            }
                        }
                    }
                    catch(Exception $ex)
                    {
                        Mage::logException($ex);
                    }
                }
            }
            $this->_options = $options;

        }

        return $this->_options;
    }

    public function toOptionArray()
    {
        return $this->getAllOptions();
    }
}
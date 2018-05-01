<?php
/**
 * Magento Fianet Extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   Gr
 * @package    Gr_Fianet
 * @author     Nicolas Fabre <nicolas.fabre@groupereflect.net>
 * @copyright  Copyright (c) 2008 Nicolas Fabre
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MDN_Orderpreparation_Model_Source_Sac_ShippingMethods extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    public function getAllOptions()
    {
        if (!$this->_options) {
	        $config = Mage::getStoreConfig('carriers');
	        foreach ($config as $code => $methodConfig) {
	        	if (Mage::getStoreConfigFlag('carriers/'.$code.'/active')) {
                    if(array_key_exists('title', $methodConfig)){
                        $this->_options[] = array(
					                    'value' => $code,
					                    'label' => !empty($methodConfig['title']) ? $methodConfig['title'] : 'empty',
					                );
                    }
	        	}
	        }
	        //Ajoute un elt vide
    		$this->_options[] = array(
                    'value' => '',
                    'label' => mage::helper('Orderpreparation')->__('none'),
                );
        }
        return $this->_options;
    }
    
	public function toOptionArray()
	{
		return $this->getAllOptions();
	}
}
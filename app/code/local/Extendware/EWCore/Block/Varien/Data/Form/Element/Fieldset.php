<?php

class Extendware_EWCore_Block_Varien_Data_Form_Element_Fieldset extends Varien_Data_Form_Element_Fieldset {
	public function __construct($attributes=array())
    {
        parent::__construct($attributes);
        $this->addType('direct_row', 'Extendware_EWCore_Block_Varien_Data_Form_Element_Direct_Row');
        $this->addType('direct_value', 'Extendware_EWCore_Block_Varien_Data_Form_Element_Direct_Value');
        $this->addType('date_label', 'Extendware_EWCore_Block_Varien_Data_Form_Element_Date_Label');
    }
    
	public function toHtml()
    {
    	$html = parent::toHtml();
    	// [[if normal]]
    	if (@class_exists('Extendware_EWCore_Model_Autoload')) {
	    	$tips = $this->_getExtendwareTips($this, 'hover', array('opentip' => array('tipJoint' => 'top right')));
	    	if (empty($tips) === false) {
	    		$id = 'ew-' . Mage::helper('core')->getRandomString(8);
	    		$code = '<div class="ewcore-tooltip-fieldset-label" id="' . $id . '"></div>';
	    		$html = preg_replace('/<div\s+?class="form-buttons">/s', '<div class="form-buttons">' . $code, $html);
	    		foreach ($tips as $tip) {
		        	if (array_key_exists('title', $tip) === false) {
	        			$tip['title'] = (string)$this->getData('legend');
	        		}
			        		
		        	$scripts[] = $this->_getExtendwareTipJs('$("' . $id . '")', $tip);
		        }
	    	}
	    	
	    	foreach ($this->getElements() as $element) {
	    		$rowHtml = $this->_extractRowHtml($element->getId(), $html);
	    		if (empty($rowHtml)) continue;
	    		
	    		$tips = $this->_getExtendwareTips($element, Mage::helper('ewcore/config')->getDefaultFormHelpTriggerMode());
	    		foreach ($tips as $tip) {
	    			if (array_key_exists('title', $tip) === false) {
						$tip['title'] = (string)$element->getData('label');
					}
	
					if (strpos($tip['mode'], 'label') !== false or strpos($tip['mode'], 'both') !== false) {
						if (preg_match('/<td\s+?class="label">(.+?)<\/td>/is', $rowHtml, $match)) {
							$id = 'ew-' . Mage::helper('core')->getRandomString(8);
							$classes = 'ewcore-tooltip-label ' . (strpos($tip['mode'], 'click') !== false ? 'ewcore-tooltip-label-clickable' : '');
							$label = preg_replace('/<label([^>]*?)>(.*?)<\/label>/si', '<label\\1><span id="' . $id . '" class="' . $classes . '">\\2</span></label>', $match[0]);
							$html = str_replace($rowHtml, str_replace($match[0], $label, $rowHtml), $html);
							$scripts[] = $this->_getExtendwareTipJs('$("' . $id. '")', $tip);
						}
					}
					
					if (strpos($tip['mode'], 'input') !== false or strpos($tip['mode'], 'both') !== false) {
						if (!preg_match('/<select\s+/si', $rowHtml)) {
							$scripts[] = $this->_getExtendwareTipJs('$("' . $element->getHtmlId() . '")', $tip);
						}
					}
					
					if ($tip['mode'] == 'hover') {
						$id = 'ew-' . Mage::helper('core')->getRandomString(8);
						$newRowHtml = str_replace('<tr>', '<tr id="' . $id . '">', $rowHtml);
						$html = str_replace($rowHtml, $newRowHtml, $html);
						$scripts[] = $this->_getExtendwareTipJs('$("' . $id . '")', $tip);
					}
	    		}
	    	}
	
	    	if (empty($scripts) === false) {
	        	$html .= '<script type="text/javascript">try{';
	        	foreach ($scripts as $script) {
	        		$html .= $script . "\n";
	        	}
	        	$html .= '} catch(e) {}</script>';
	        }
    	}
    	// [[/if]]
        return $html;
    }
    
    protected function _extractRowHtml($id, $html) {
    	if (preg_match_all('/<tr>.*?<td\s+?class="label">\s*?<label\s+?for="(.*?)">.*?<\/td>.*?<td\s+?class="value">.*?<\/td>.*?<\/tr>/is', $html, $matches, PREG_SET_ORDER)) {
    		foreach ($matches as $match) {
    			if ($match[1] == $id) {
    				return $match[0];
    			}
    		}
    	}
    	return null;
    }
    
	protected function _getExtendwareTipJs($trigger, array $tip) {
    	return Mage::helper('ewcore/misc')->getTipJs($trigger, $tip);
    }
    
	protected function _getExtendwareTips($field, $defaultMode, array $options = array()) {
		$ewhelp = $field->getData('ewhelp');
		if (empty($ewhelp) === true) {
			return array();
		}
		
		$config = array();
		if (is_array($ewhelp) === true) {
			$config = $ewhelp;
		} else {
			$ewhelp = array('text' => $ewhelp);
			if ($field->getData('ewhelp_max_width')) {
				$ewhelp['max_width'] = $field->getData('ewhelp_max_width');
			}
			$config[] = $ewhelp;
		}
		
		return Mage::helper('ewcore/misc')->getTipsFromArray($config, $defaultMode, $options);
    }
}

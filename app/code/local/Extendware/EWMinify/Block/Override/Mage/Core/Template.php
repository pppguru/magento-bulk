<?php
class Extendware_EWMinify_Block_Override_Mage_Core_Template extends Extendware_EWMinify_Block_Override_Mage_Core_Template_Bridge
{
	public function fetchView($fileName)
    {
    	if ($this->getShowTemplateHints()) {
    		return parent::fetchView($fileName);
    	}
    	
    	$minifierType = null;
    	if (is_object($this->getAction()) and is_object($this->getAction()->getLayout())) {
	        switch (strtolower($this->getAction()->getLayout()->getArea())) {
	        	case 'frontend':
	        		$minifierType = strtolower($this->getAction()->getLayout()->getArea());
	        		break;
	        }
    	} else return parent::fetchView($fileName);
    	
        $phpMinifier = null;
    	if ($minifierType) {
    		try {
	        	$phpMinifier = Mage::getSingleton('ewminify/template_' . $minifierType);
    		} catch (Exception $e) {
    			return parent::fetchView($fileName);
    		}
        }

        if (!$phpMinifier or $phpMinifier->isMinifyEnabled($this->_viewDir . DS . $fileName) === false) {
        	return parent::fetchView($fileName);
        }

        extract ($this->_viewVars);
        $do = $this->getDirectOutput();

        if (!$do) {
            ob_start();
        }
        
        try {
	        $phpMinifier->setSourceDirectory($this->_viewDir);
	        $phpMinifier->setSourceFilename($fileName);
	        include $phpMinifier->getFile();
        } catch (Exception $e) {
            ob_get_clean();
            throw $e;
        }
        
        if (!$do) {
            $html = ob_get_clean();
        } else {
            $html = '';
        }
        
        return $html;
    }
}

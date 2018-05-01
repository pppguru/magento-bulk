<?php
class Extendware_EWCore_Block_Adminhtml_Userguide_Edit_Tab_General extends Extendware_EWCore_Block_Mage_Adminhtml_Widget_Form
{
	protected function _prepareForm()
    {
        $form = new Extendware_EWCore_Block_Varien_Data_Form();
		
        $fieldset = $form->addFieldset('main', array(
        	'legend' => $this->__('%s User Guide', $this->getModule()->getFriendlyName()),
        	'class' => 'fieldset-wide',
        ));

		$fieldset->addField('userguide', 'direct_row', array(
        	'value' => '<iframe src="' . $this->getGuideUrl() . '" style="width: 99%; text-align: center; border: 0px; height: 800px;"></iframe>'
        ));

		$form->setUseContainer(false);
        $this->setForm($form);
        
		return parent::_prepareForm();
	}
	
    public function getGuideUrl() {
    	$ewcore = Mage::getSingleton('ewcore/module')->load('Extendware_EWCore');
    	$params = array(
    		'iid' => $this->getCoreModule()->getSerial()->getInstallationId(),
    		'sid' => $this->getModule()->getId(),
    		'type' => 'large',
    		'reset' => 1,
    	);
    	return $this->mHelper()->getGuideUrl('rwsoftware/guide/iframe', $params);
    }
    
	protected function _toHtml()
    {
    	if (!$this->canShow()) return;
    	
        return parent::_toHtml();
    }
    
    public function canShow() {
    	if (!$this->getCoreModule()->hasSerial()) return false;
    	if (!$this->hasModule()) return false;
    	if (!$this->getModule()->isExtendware()) return false;
    	if ($this->getModule()->isForMainsite()) return false;
    	return true;
    }
    
	public function getCoreModule() {
		static $module = null;
		if ($module === null) $module = Mage::getSingleton('ewcore/module')->load('Extendware_EWCore');
		return $module;
	}
}

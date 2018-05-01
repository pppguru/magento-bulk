<?php
if (defined('COMPILER_INCLUDE_PATH')) {
	require_once('Extendware_EWCore_Controller_Adminhtml_Action.php');
} else {
	require_once('Extendware/EWCore/Controller/Adminhtml/Action.php');
}
?><?php

class Extendware_EWCore_Ewcore_RedirectController extends Extendware_EWCore_Controller_Adminhtml_Action
{
	public function helpAction() {
		return $this->redirectTo('help');
	}
	
	public function extendwareAction() {
		return $this->redirectTo('extendware');
	}
	
	public function productAction() {
		return $this->redirectTo('product');
	}
	
	protected function redirectTo($to)
	{
		$model = Mage::getSingleton('ewcore/module')->load('Extendware_EWCore');
		$url = Mage::helper('ewcore/utility')->convertExtendwareHostname('http://www.extendware.com/');
		$url .= 'rwcore/redirect/normal/to/' . rawurlencode($to) . '/';
		
		$from = array('ewore' => 1, 'version' => $model->getVersion());
		if ($model->hasSerial() === true) {
			$from['instance_id'] = $model->getInstanceId();
		}
		$url .= 'from/' . $this->encodeUrlParam($from) . '/';
		
		$params = $this->getParams();
		unset($params['key']);
		
		$url .= 'params/' . $this->encodeUrlParam($params) . '/';

		return $this->getResponse()->setHeader('Location', $url);
	}
	
	protected function encodeUrlParam($param) {
		 return @strtr(base64_encode(json_encode($param)), '+/=', '-_,');
	}
	
	protected function decodeUrlParam($param) {
		return @json_decode(base64_decode(strtr($param, '-_,', '+/=')), true);
	}
}
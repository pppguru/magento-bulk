<?php
interface Extendware_EWPageCache_Model_Injector_Interface
{
	public function process($content, array $params = array(), array $request = array());
	public function getInjection(array $params = array(), array $request = array());
	public function setId($value);
	public function setDataKey($value);
}

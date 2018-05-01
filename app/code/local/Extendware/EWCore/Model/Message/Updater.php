<?php

final class Extendware_EWCore_Model_Message_Updater extends Extendware_EWCore_Model_Varien_Object
{
	public function _construct()
	{
		$this->setApi(Mage::getModel('ewcore/extendware_api'));
		
		return parent::_construct();
	}

	public function update()
	{
		if ($this->mHelper('config')->isWhiteLabeled()) {
			return null;
		}
		
		if ($this->mHelper('config')->isMessageUpdatingEnabled() === false) {
			return null;
		}
		
		if (Mage::getSingleton('ewcore/module')->load('Extendware_EWCore')->hasSerial() === false) {
			return null;
		}
		
		$response = $this->getApi()->getMessages(array(
			'last_request_time' => $this->mHelper('config')->getLastMessagesUpdatedServerTime(),
		));
		
		if ($response === false) {
			return false;
		}
		
		if ($response['item_type'] != 'messages') {
			$this->mHelper('system')->log($this->__('Message updater did not receive the correct response item type'));
			return false;
		}
		 
		$this->mHelper('config')->setLastMessagesUpdatedServerTime($response['time']);
		
		try {
			foreach ($response['data']['actions'] as $action) {
				if (empty($action['conditions']) === false or empty($action['message']['conditions']) === false) {
					$action['type'] = 'delete';
				}
				
				if (in_array($action['type'], array('add', 'update'))) {
					$model = Mage::getModel('ewcore/message')->loadByReferenceId($action['message']['id']);
					// ensure we do not add messages based on an update
					if ($action['type'] == 'add' or $model->getId() > 0) {
						$model->setReferenceId($action['message']['id']);
						$model->setSentAt(date('Y-m-d H:i:s', $action['message']['sent_at']));
						foreach ($action['message'] as $key => $value) {
							if (in_array($key, array('id', 'sent_at'))) continue;
							$model->setData($key, $value);
						}
						$model->save();
					}
				} elseif ($action['type'] == 'delete') {
					Mage::getModel('ewcore/message')->loadByReferenceId($action['message']['id'])->delete();
				}
			}
		} catch (Exception $e) {
			Mage::logException($e);
			return false;
		}
		
		return true;
	}
}
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
 * @author : Olivier ZIMMERMANN
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MDN_BackgroundTask_Model_Task extends Mage_Core_Model_Abstract
{

	public function _construct()
	{
		parent::_construct();
		$this->_init('BackgroundTask/Task');
	}	
	
	/**
	 * Execute task
	 *
	 */
	public function execute()
	{
		$error = false;
		$status = 'success';
		$chronometer = mage::helper('BackgroundTask/Chronometer');
		$chronometer->bwruntime();
			
		try 
		{
			if (!$this->getbt_helper())
				throw new Exception('Empty helper name !');

			//Collect helper
			$helper = mage::helper($this->getbt_helper());				
			$params = unserialize($this->getbt_params());
			$helper->{$this->getbt_method()}($params);
			
		}
		catch (Exception  $ex)
		{
			$error = $ex->getMessage();
			$error .= $ex->getTraceAsString();
			$status = 'error';
			
			//if error, notify developer
			$developerEmail = mage::getStoreConfig('backgroundtask/general/developer_email');
			if ($developerEmail)
			{
				$body = 'Error for task id #'.$this->getId()."\n";
				$body .= 'Method : '.$this->getbt_helper().' / '.$this->getbt_method()."\n";
				$body .= $ex->getMessage()."\n";
				$body .= $ex->getMessage()."\n";
				$body .= $ex->getTraceAsString()."\n";
				mail($developerEmail, 'Backgroundtask error', $body);
			}
		}
		
		$duration = (int)($chronometer->totaltime() * 1000);
		
		//Save execution information
		$this->setbt_executed_at(date('Y-m-d H:i'))
			 ->setbt_result_description($error)
			 ->setbt_result($status)
			 ->setbt_duration($duration)
			 ->save();
			 
		return $this;
	}
}
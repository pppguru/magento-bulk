<?php
/**
 * 
 *
 */
class MDN_Organizer_Model_TaskCategory  extends Mage_Core_Model_Abstract
{
	
	/*****************************************************************************************************************************
	* ***************************************************************************************************************************
	* Constructeur
	*
	*/
	public function _construct()
	{
		parent::_construct();
		$this->_init('Organizer/TaskCategory');
	}
	
}
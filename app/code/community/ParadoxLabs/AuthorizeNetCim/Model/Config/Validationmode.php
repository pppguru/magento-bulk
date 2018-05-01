<?php
/**
 * Paradox Labs, Inc.
 * http://www.paradoxlabs.com
 * 717-431-3330
 * 
 * Need help? Open a ticket in our support system:
 *  http://support.paradoxlabs.com
 * 
 * Want to customize or need help with your store?
 *  Phone: 717-431-3330
 *  Email: sales@paradoxlabs.com
 *
 * @category	ParadoxLabs
 * @package		AuthorizeNetCim
 * @author		Ryan Hoerr <magento@paradoxlabs.com>
 * @license		http://store.paradoxlabs.com/license.html
 */

class ParadoxLabs_AuthorizeNetCim_Model_Config_Validationmode
{
	/**
	 * Options getter
	 *
	 * @return array
	 */
	public function toOptionArray()
	{
		return array(
			array('value' => 'liveMode', 'label'=>'Live ($0.01 test transaction - recommended)'),
			array('value' => 'testMode', 'label'=>'Test (Card number validation only)'),
			array('value' => 'none',     'label'=>'None (No validation performed)')
		);
	}
	
	/**
	 * Get options in "key-value" format
	 *
	 * @return array
	 */
	public function toArray()
	{
		return array(
			'liveMode'  => 'Live ($0.01 test transaction - recommended)',
			'testMode'  => 'Test (Card number validation only)',
			'none'      => 'None (No validation performed)'
		);
	}
}

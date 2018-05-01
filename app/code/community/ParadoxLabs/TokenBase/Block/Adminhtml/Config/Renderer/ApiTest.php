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
 * @package		TokenBase
 * @author		Ryan Hoerr <magento@paradoxlabs.com>
 * @license		http://store.paradoxlabs.com/license.html
 */

class ParadoxLabs_TokenBase_Block_Adminhtml_Config_Renderer_ApiTest extends Varien_Data_Form_Element_Label
{
	public function getElementHtml()
	{
		$html = parent::getElementHtml();
		
		if( strpos( $html, 'success' ) !== false ) {
			$html = '<strong style="color:#0a0;">' . $html . '</strong>';
		}
		else {
			$html = '<strong class="error">' . $html . '</strong>';
		}
		
		return $html;
	}
}

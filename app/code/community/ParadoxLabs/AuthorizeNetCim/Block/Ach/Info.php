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

class ParadoxLabs_AuthorizeNetCim_Block_Ach_Info extends ParadoxLabs_AuthorizeNetCim_Block_Info
{
	protected $_isEcheck = true;
	
	protected function _prepareSpecificInformation($transport = null)
	{
		$transport	= parent::_prepareSpecificInformation($transport);
		$data		= array();
		
		// If this is admin, show different info.
		if( Mage::app()->getStore()->isAdmin() ) {
			$type		= $this->getInfo()->getAdditionalInformation('echeck_account_type');
			$accName	= $this->getInfo()->getEcheckAccountName();
			
			if( !empty( $accName ) ) {
				$data[Mage::helper('tokenbase')->__('Name on Account')]	= $accName;
			}
			
			if( !empty( $type ) ) {
				$data[Mage::helper('tokenbase')->__('Type')]			= Mage::helper('authnetcim_ach')->getAchAccountTypes( $type );
			}
		}
		
		return $transport->setData(array_merge($transport->getData(), $data));
	}
}

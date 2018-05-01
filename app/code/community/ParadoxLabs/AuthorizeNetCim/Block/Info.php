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

class ParadoxLabs_AuthorizeNetCim_Block_Info extends ParadoxLabs_TokenBase_Block_Info
{
	protected function _prepareSpecificInformation($transport = null)
	{
		$transport	= parent::_prepareSpecificInformation($transport);
		$data		= array();
		
		// If this is admin, show different info.
		if( $this->_isEcheck() !== true && Mage::app()->getStore()->isAdmin() ) {
			$avs	= $this->getInfo()->getData('cc_avs_status') != '' ? $this->getInfo()->getData('cc_avs_status') : $this->getInfo()->getAdditionalInformation('avs_result_code');
			$ccv	= $this->getInfo()->getData('cc_cid_status') != '' ? $this->getInfo()->getData('cc_cid_status') : $this->getInfo()->getAdditionalInformation('card_code_response_code');
			$cavv	= $this->getInfo()->getData('cc_status') != '' ? $this->getInfo()->getData('cc_status') : $this->getInfo()->getAdditionalInformation('cavv_response_code');
			
			if( !empty( $avs ) ) {
				$data[Mage::helper('tokenbase')->__('AVS Response')]	= Mage::helper('authnetcim')->translateAvs( $avs );
			}
			
			if( !empty( $ccv ) ) {
				$data[Mage::helper('tokenbase')->__('CCV Response')]	= Mage::helper('authnetcim')->translateCcv( $ccv );
			}
			
			if( !empty( $cavv ) ) {
				$data[Mage::helper('tokenbase')->__('CAVV Response')]	= Mage::helper('authnetcim')->translateCavv( $cavv );
			}
		}
		
		return $transport->setData(array_merge($transport->getData(), $data));
	}
}

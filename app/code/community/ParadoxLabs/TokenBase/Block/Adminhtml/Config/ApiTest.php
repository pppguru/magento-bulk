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

abstract class ParadoxLabs_TokenBase_Block_Adminhtml_Config_ApiTest extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{
	protected $_code		= 'tokenbase';
	protected $_storeId		= null;
	protected $_renderer	= null;
	
	public function render( Varien_Data_Form_Element_Abstract $element )
	{
		/**
		 * Self-healing: Fix common setup problems. Convenient spot.
		 */
		if( Mage::registry('tokenbase_self_healed') !== 1 ) {
			try {
				$setup = Mage::getResourceModel( 'core/setup', 'core_setup' );
				
				/**
				 * Hash column
				 */
				$table = $setup->getTable('tokenbase/card');
				if( $setup->getConnection()->tableColumnExists( $table, 'hash' ) !== true ) {
					$setup->run("ALTER TABLE {$table} 
						CHANGE `method` `method` VARCHAR(32),
						ADD `hash` VARCHAR(40) NULL COMMENT 'Unique Hash',
						ADD UNIQUE (`hash`);");
					$setup->run("UPDATE {$table} SET `hash`=SHA1( CONCAT('tokenbase', customer_id, customer_email, method, profile_id, payment_id) ) WHERE `hash` IS NULL;");
				}
				
				/**
				 * Payment columns
				 */
				$table = $setup->getTable('sales/quote_payment');
				if( $setup->getConnection()->tableColumnExists( $table, 'tokenbase_id' ) !== true ) {
					$setup->getConnection()->addColumn(
						$table,
						'tokenbase_id',
						"INT(11) UNSIGNED NULL COMMENT 'ParadoxLabs_TokenBase Card ID'"
					);
				}
				
				$table = $setup->getTable('sales/order_payment');
				if( $setup->getConnection()->tableColumnExists( $table, 'tokenbase_id' ) !== true ) {
					$setup->getConnection()->addColumn(
						$table,
						'tokenbase_id',
						"INT(11) UNSIGNED NULL COMMENT 'ParadoxLabs_TokenBase Card ID'"
					);
				}
				
				Mage::register( 'tokenbase_self_healed', 1 );
			}
			catch( Exception $e ) {
				Mage::helper('tokenbase')->log( $this->_code, (string)$e );
			}
		}
		
		/**
		 * Test the API creds
		 */
		$test = $this->_testApi();
		
		if( $test !== false ) {
			$element->addType( 'tokenbase_apitest', 'ParadoxLabs_TokenBase_Block_Adminhtml_Config_Renderer_ApiTest' );
			
			$field = $element->addField( $this->_code . '_apitest', 'tokenbase_apitest', array(
				'name'  => $this->_code . '_apitest',
				'label' => 'API Test Results',
				'value' => $test
			))->setRenderer($this->_getRenderer());
			
			return $field->toHtml();
		}
	}
	
	protected function _getRenderer()
	{
		if( is_null( $this->_renderer ) ) {
			$this->_renderer = Mage::getBlockSingleton('adminhtml/system_config_form_field');
		}
		
		return $this->_renderer;
	}
	
	protected function _getStoreId()
	{
		if( is_null( $this->_storeId ) ) {
			if( Mage::app()->getRequest()->getParam('store') != '' ) {
				$this->_storeId = Mage::getModel('core/store')->load( Mage::app()->getRequest()->getParam('store') )->getId();
			}
			elseif( Mage::app()->getRequest()->getParam('website') != '' ) {
				$this->_storeId = Mage::getModel('core/website')->load( Mage::app()->getRequest()->getParam('website') )->getDefaultGroup()->getDefaultStoreId();
			}
			else {
				$this->_storeId = 0;
			}
		}
		
		return $this->_storeId;
	}
	
	abstract protected function _testApi();
}

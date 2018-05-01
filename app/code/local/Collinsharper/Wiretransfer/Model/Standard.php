<?php
//Mage::log('Does this work'); 
 
/**
* Our test CC module adapter
*/
class Collinsharper_Wiretransfer_Model_Standard extends Mage_Payment_Model_Method_Abstract
{
    /**
    * unique internal payment method identifier
    *
    * @var string [a-z0-9_]
    */
    protected $_code = 'wiretransfer';

	 protected $_formBlockType = 'wiretransfer/standard_form';
	 protected $_infoBlockType = 'wiretransfer/standard_info';

	
    protected $_isGateway               = false;
    protected $_canAuthorize            = false;
    protected $_canCapture              = false;
    protected $_canCapturePartial       = false;
    protected $_canRefund               = false;
    protected $_canVoid                 = false;
    protected $_canUseInternal          = true;
    protected $_canUseCheckout          = true;
    protected $_canUseForMultishipping  = false;


 	    /* Can be used in regular checkout
     *
     * @return bool
     */
    public function canUseCheckout()
    {
	 if($this->getConfigData('activefe') == true)
        return $this->_canUseCheckout;
	return false;
    }

	
	  /**
     * Using internal pages for input payment data
     *
     * @return bool
     */
    public function canUseInternal()
    {
        return true;
    }

    /**
     * Using for multiple shipping address
     *
     * @return bool
     */
    public function canUseForMultishipping()
    {
        return false;
    }
	

  public function createFormBlock($name)
    {
        $block = $this->getLayout()->createBlock('wiretransfer/standard_form', $name)
            ->setMethod('wiretransfer')
            ->setPayment($this->getPayment())
            ->setTemplate('wiretransfer/standard/form.phtml');

        return $block;
    }
	
  public function createInfoBlock($name)
    {
  $block = $this->getLayout()->createBlock('wiretransfer/standard_info', $name)
            ->setPayment($this->getPayment())
            ->setTemplate('wiretransfer/standard/info.phtml');

        return $block;
    }

      /**
     * Assign data to info model instance
     *
     * @param   mixed $data
     * @return  Mage_Payment_Model_Method_Checkmo
     */
    public function assignData($data)
    {
        $details = array();
 
        if ($this->getWiretransferInstructions()) {
            $details['wiretransfer_instructions'] = $this->getWiretransferInstructions();
        }
		if ($this->getWiretransferInstructionsEmail()) {
            $details['wiretransfer_instructionsEmail'] = $this->getWiretransferInstructionsEmail();
        }
        if (!empty($details)) {
            $this->getInfoInstance()->setAdditionalData(serialize($details));
        }
        return $this;
    }



    public function getWiretransferInstructions()
    {
        return $this->getConfigData('wire_instructions');
    }	

	
    public function getWiretransferInstructionsEmail()
    {
        return $this->getConfigData('wire_instructions_email');
    }	

	
 
	
}
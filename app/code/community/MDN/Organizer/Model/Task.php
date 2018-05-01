<?php
/**
 * 
 *
 */
class MDN_Organizer_Model_Task  extends Mage_Core_Model_Abstract
{
    //Native magento Entities
	const ENTITY_TYPE_CUSTOMER = 'customer';
    const ENTITY_TYPE_PRODUCT = 'product';
    const ENTITY_TYPE_ORDER = 'order';
    const ENTITY_TYPE_CREDIT_MEMO = 'credit_memo';

    //Regarding RMA module
    const ENTITY_TYPE_RMA = 'rma';

    //Regarding ERP purchase module
    const ENTITY_TYPE_SUPPLIER = 'supplier';
    const ENTITY_TYPE_PURCHASE_ORDER = 'purchase_order';






	/*****************************************************************************************************************************
	* ***************************************************************************************************************************
	* Constructeur
	*
	*/
	public function _construct()
	{
		parent::_construct();
		$this->_init('Organizer/Task');
	}
	
	
	/**
	 * Link to display entity link
	 *
	 */
	public function getEntityLink()
	{
		$link = '';
        $helper =  Mage::helper('adminhtml');

        $entityId = $this->getot_entity_id();
        $entityType = $this->getot_entity_type();
        
		if($entityId && $entityType){
            switch($entityType)
            {
                case self::ENTITY_TYPE_ORDER:
                    $link = $helper->getUrl('adminhtml/sales_order/view', array('order_id' => $entityId));
                    break;
                case self::ENTITY_TYPE_CUSTOMER:
                    $link = $helper->getUrl('adminhtml/customer/edit', array('id' => $entityId));
                    break;
                case self::ENTITY_TYPE_RMA:
                    $link = $helper->getUrl('adminhtml/ProductReturn_Admin/Edit', array('rma_id' => $entityId));
                    break;
                case self::ENTITY_TYPE_PURCHASE_ORDER:
                    $link = $helper->getUrl('adminhtml/Purchase_Orders/Edit', array('po_num' => $entityId));
                    break;
                case self::ENTITY_TYPE_PRODUCT:
                    $link = $helper->getUrl('adminhtml/catalog_product/edit', array('id' => $entityId));
                    break;
                case self::ENTITY_TYPE_SUPPLIER:
                    $link = $helper->getUrl('adminhtml/Purchase_Suppliers/Edit', array('sup_id' => $entityId));
                    break;
                case self::ENTITY_TYPE_CREDIT_MEMO:
                    $link = $helper->getUrl('adminhtml/sales_creditmemo/view', array('creditmemo_id' => $entityId));
                    break;
            }
        }
		
		return $link;
	}
	
	/**
	 * M�thode pour savoir si une tache est en retard
	 *
	 */
	public function isLate()
	{
		$retour = false;
		if ($this->getot_finished() == 0)
		{
			if ($this->getot_deadline() != '')
			{
				$deadline = strtotime($this->getot_deadline());
				if ($deadline < time())
					$retour = true;
			}
		}
		return $retour;
	}
	
	/**
	 * M�thode pour savoir si la tache est termin�e
	 *
	 * @return unknown
	 */
	public function isFinished()
	{
		return ($this->getot_finished() == 1);
	}
	
	/**
	 * Return task author
	 *
	 * @return unknown
	 */
	public function getAuthor()
	{
		$authorId = $this->getot_author_user();
		return mage::getModel('admin/user')->load($authorId);
	}
	
	/**
	 * Notify Task Target
	 *
	 */
	public function notifyTarget()
	{
		//retrieve target email
		$target = mage::getModel('admin/user')->load($this->getot_target_user());
			
		$translate = Mage::getSingleton('core/translate');
        /* @var $translate Mage_Core_Model_Translate */
        $translate->setTranslateInline(false);

        $templateId = Mage::getStoreConfig('organizer/notification/email_template');
        $identityId = Mage::getStoreConfig('organizer/notification/email_identity');
        
        $data = array
        	(
        		'task_link' => $this->getEntityLink(),
        		'entity_name' => $this->getot_entity_description(),
        		'author' => $this->getAuthor()->getName(),
        		'subject' => $this->getot_caption(),
        		'comments' => $this->getot_description()
        	);
        	
        //send email
        Mage::getModel('core/email_template')
            ->setDesignConfig(array('area'=>'adminhtml', 'store'=>0))
            ->sendTransactional(
                $templateId,
                $identityId,
                $target->getemail(),
                '',
                $data,
                null,
                null);

        $translate->setTranslateInline(true);

        return $this;
	}
	
}
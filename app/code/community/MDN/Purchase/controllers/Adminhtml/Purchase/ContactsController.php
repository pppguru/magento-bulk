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

class MDN_Purchase_Adminhtml_Purchase_ContactsController extends Mage_Adminhtml_Controller_Action
{

	public function indexAction()
    {

    }

    /**
     * Affiche la liste des
     *
     */
	public function ListAction()
    {
    	$this->loadLayout();

        $this->_setActiveMenu('erp');
        $this->getLayout()->getBlock('head')->setTitle($this->__('Contacts'));

        $this->renderLayout();
    }
    
    /**
     * Nouveau
     *
     */
	public function NewAction()
    {
    	$this->loadLayout();

        $this->_setActiveMenu('erp');
        $this->getLayout()->getBlock('head')->setTitle($this->__('New Contact'));

        $this->renderLayout();
    }
    
    /**
     * Creation
     *
     */
    public function CreateAction()
    {
    	
    	
    }
    
    /**
	 * Edition d'un manufacturer
	 *
	 */
	public function EditAction()
    {
    	$this->loadLayout();

        $this->_setActiveMenu('erp');
        $this->getLayout()->getBlock('head')->setTitle($this->__('Edit Contact'));

        $this->renderLayout();
    }
    
    /**
     * Enregistre les modifs faite sur le manufacturer
     *
     */
    public function SaveAction()
    {
    	//Charge le contact
    	$Contact = Mage::getModel('Purchase/Contact')->load($this->getRequest()->getParam('pc_num'));
    	
    	//Enregistre les modifs
		$Contact->setpc_firstname($this->getRequest()->getParam('pc_firstname'));
		$Contact->setpc_lastname($this->getRequest()->getParam('pc_lastname'));
		$Contact->setpc_function($this->getRequest()->getParam('pc_function'));
		$Contact->setpc_phone($this->getRequest()->getParam('pc_phone'));
		$Contact->setpc_fax($this->getRequest()->getParam('pc_fax'));
		$Contact->setpc_mobile($this->getRequest()->getParam('pc_mobile'));
		$Contact->setpc_email($this->getRequest()->getParam('pc_email'));
		$Contact->setpc_country($this->getRequest()->getParam('pc_country'));
		$Contact->setpc_comments($this->getRequest()->getParam('pc_comments'));
    	$Contact->save();
    	
    	//confirme
    	Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Contact Saved'));
    	
    	//Redirige vers la fiche cr��e
    	$this->_redirect('adminhtml/Purchase_Contacts/Edit/pc_num/'.$Contact->getId());
    	
    }
    
    /**
     * Retourne en ajax les informations sur un contact
     *
     */
    public function GetContactInformationAction()
    {
        //recupere l'objet
    	$object = mage::GetModel('Purchase/Contact')
    				->load($this->getRequest()->getParam('pc_num'));
    	
    	//retourne en ajax
    	$this->getResponse()->setHeader('Content-type', 'application/x-json');
        $this->getResponse()->setBody($object->toJson());	
    }
    
    /**
     * Supprime un contact
     *
     */
    public function DeleteContactAction()
    {
        //recupere l'objet
    	$object = mage::GetModel('Purchase/Contact')
    				->load($this->getRequest()->getParam('pc_num'))
    				->delete();
    		
    }
    
    /**
     * Sav les infos d'un contact
     *
     */
    public function SaveContactAction()
    {
    	//Si on est en cr�ation
    	if ($this->getRequest()->getParam('pc_num') == -1)
    	{
			$contact = Mage::getModel('Purchase/Contact');
			$contact->setpc_firstname($this->getRequest()->getParam('pc_firstname'));
			$contact->setpc_lastname($this->getRequest()->getParam('pc_lastname'));
			$contact->setpc_function($this->getRequest()->getParam('pc_function'));
			$contact->setpc_phone($this->getRequest()->getParam('pc_phone'));
			$contact->setpc_fax($this->getRequest()->getParam('pc_fax'));
			$contact->setpc_mobile($this->getRequest()->getParam('pc_mobile'));
			$contact->setpc_email($this->getRequest()->getParam('pc_email'));
			$contact->setpc_comments($this->getRequest()->getParam('pc_comments'));
			$contact->setpc_type($this->getRequest()->getParam('pc_type'));
			$contact->setpc_entity_id($this->getRequest()->getParam('pc_entity_id'));
			$contact->save();		
    	}
    	else 
    	{
			$contact = Mage::getModel('Purchase/Contact')->load($this->getRequest()->getParam('pc_num'));
			$contact->setpc_firstname($this->getRequest()->getParam('pc_firstname'));
			$contact->setpc_lastname($this->getRequest()->getParam('pc_lastname'));
			$contact->setpc_function($this->getRequest()->getParam('pc_function'));
			$contact->setpc_phone($this->getRequest()->getParam('pc_phone'));
			$contact->setpc_fax($this->getRequest()->getParam('pc_fax'));
			$contact->setpc_mobile($this->getRequest()->getParam('pc_mobile'));
			$contact->setpc_email($this->getRequest()->getParam('pc_email'));
			$contact->setpc_comments($this->getRequest()->getParam('pc_comments'));
			$contact->save();
    	}
    	
    }
    
    /**
     * Retourne une sous liste de contact
     * cad les contacts li�s � un manufacturer ou un supplier
     *
     */
    public function getSubGridAction()
    {
    	//recupere les infos
    	$EntityType = Mage::app()->getRequest()->getParam('entity_type');
    	$EntityId = Mage::app()->getRequest()->getParam('entity_id');
    	
    	//cree le block et le retourne
    	$block = $this->getLayout()->createBlock('Purchase/Contact_SubGrid', 'contacts');
    	$block->setEntityType($EntityType);
    	$block->setEntityId($EntityId);
    	$block->setTemplate('Purchase/Contact/SubGrid.phtml');
    	
    	$this->getResponse()->setBody($block->toHtml());
        
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('admin/erp/purchasing');
    }
}
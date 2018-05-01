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
//Controlleur pour la gestion des manufacturer
class MDN_Purchase_Adminhtml_Purchase_ManufacturersController extends Mage_Adminhtml_Controller_Action
{

	public function indexAction()
    {

    }

    /**
     * Affiche la liste des manufacturer
     *
     */
	public function ListAction()
    {
    	$this->loadLayout();

        $this->_setActiveMenu('erp');
        $this->getLayout()->getBlock('head')->setTitle($this->__('Manufacturers'));

        $this->renderLayout();
    }
    
    /**
     * Nouveau manufacturer
     *
     */
	public function NewAction()
    {
    	$this->loadLayout();

        $this->_setActiveMenu('erp');
        $this->getLayout()->getBlock('head')->setTitle($this->__('New manufacturer'));

        $this->renderLayout();
    }
    
    /**
     * Creation d'un manufacturer
     *
     */
    public function CreateAction()
    {
    	//Charge les donn�es
    	$Manufacturer = mage::getModel('Purchase/Manufacturer');
    	$Manufacturer->setman_name($this->getRequest()->getParam('man_name'));
    	$Manufacturer->setman_contact($this->getRequest()->getParam('man_contact'));
    	$Manufacturer->setman_address1($this->getRequest()->getParam('man_address1'));
    	$Manufacturer->setman_address2($this->getRequest()->getParam('man_address2'));
    	$Manufacturer->setman_zipcode($this->getRequest()->getParam('man_zipcode'));
    	$Manufacturer->setman_city($this->getRequest()->getParam('man_city'));
    	$Manufacturer->setman_country($this->getRequest()->getParam('man_country'));
    	$Manufacturer->setman_tel($this->getRequest()->getParam('man_tel'));
    	$Manufacturer->setman_fax($this->getRequest()->getParam('man_fax'));
    	$Manufacturer->setman_email($this->getRequest()->getParam('man_email'));
    	$Manufacturer->setman_website($this->getRequest()->getParam('man_website'));
    	$Manufacturer->setman_comments($this->getRequest()->getParam('man_comments'));
    	
    	//Cree
    	$Manufacturer->save();
    	
    	//confirme
    	Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Manufacturer Created'));
    	
    	//Redirige vers la fiche cr��e
    	$this->_redirect('adminhtml/Purchase_Manufacturers/Edit/man_id/'.$Manufacturer->getId());
    	
    }
    
    /**
	 * Edition d'un manufacturer
	 *
	 */
	public function EditAction()
    {
    	$this->loadLayout();

        $this->_setActiveMenu('erp');
        $this->getLayout()->getBlock('head')->setTitle($this->__('Edit manufacturer'));
    	
    	$this->getLayout()->getBlock('contacts')->setEntityType('manufacturer');
    	$this->getLayout()->getBlock('contacts')->setEntityId(Mage::app()->getRequest()->getParam('man_id'));
    	    	
        $this->renderLayout();
    }
    
    public function DeleteAction()
    {
    	$manId = $this->getRequest()->getParam('man_id');
    	$Manufacturer = Mage::getModel('Purchase/Manufacturer')->load($manId);
    	$Manufacturer->delete();

    	//confirm & redirect
    	Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Manufacturer deleted'));
    	$this->_redirect('adminhtml/Purchase_Manufacturers/List');
    }
    
    /**
     * Enregistre les modifs faite sur le manufacturer
     *
     */
    public function SaveAction()
    {
    	//Charge le manufacturer
    	$Manufacturer = Mage::getModel('Purchase/Manufacturer')->load($this->getRequest()->getParam('man_id'));
    	
    	//Enregistre les modifs
    	$Manufacturer->setman_name($this->getRequest()->getParam('man_name'));
    	$Manufacturer->setman_contact($this->getRequest()->getParam('man_contact'));
    	$Manufacturer->setman_address1($this->getRequest()->getParam('man_address1'));
    	$Manufacturer->setman_address2($this->getRequest()->getParam('man_address2'));
    	$Manufacturer->setman_zipcode($this->getRequest()->getParam('man_zipcode'));
    	$Manufacturer->setman_city($this->getRequest()->getParam('man_city'));
    	$Manufacturer->setman_country($this->getRequest()->getParam('man_country'));
    	$Manufacturer->setman_tel($this->getRequest()->getParam('man_tel'));
    	$Manufacturer->setman_fax($this->getRequest()->getParam('man_fax'));
    	$Manufacturer->setman_email($this->getRequest()->getParam('man_email'));
    	$Manufacturer->setman_website($this->getRequest()->getParam('man_website'));
    	$Manufacturer->setman_comments($this->getRequest()->getParam('man_comments'));
    	$Manufacturer->setman_attribute_option_id($this->getRequest()->getParam('man_attribute_option_id'));
    	
    	//Si on doit cr�er le manufacturer magento
    	if ($this->getRequest()->getParam('create_magento_manufacturer') == '1')
    	{
    		//cree l'option
    		$option['attribute_id'] = mage::getModel('Purchase/Constant')->GetProductManufacturerAttributeId();
			$option['value'][0][0] = $Manufacturer->getman_name();
			$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
			$setup->addAttributeOption($option);
			
			//recupere son id
			$OptionId = null;
			$sql = "
				SELECT 
					".mage::getModel('Purchase/Constant')->getTablePrefix()."eav_attribute_option_value.option_id id, ".mage::getModel('Purchase/Constant')->getTablePrefix()."eav_attribute_option_value.value name
				from 
					".mage::getModel('Purchase/Constant')->getTablePrefix()."eav_attribute_option,
					".mage::getModel('Purchase/Constant')->getTablePrefix()."eav_attribute_option_value
				where 
					".mage::getModel('Purchase/Constant')->getTablePrefix()."eav_attribute_option.attribute_id = ".mage::getModel('Purchase/Constant')->GetProductManufacturerAttributeId()."
					and ".mage::getModel('Purchase/Constant')->getTablePrefix()."eav_attribute_option.option_id = ".mage::getModel('Purchase/Constant')->getTablePrefix()."eav_attribute_option_value.option_id 
					order by ".mage::getModel('Purchase/Constant')->getTablePrefix()."eav_attribute_option_value.value
				";
							
			//execute la requete
			$data = mage::getResourceModel('sales/order_item_collection')->getConnection()->fetchAll($sql);	
			for ($i=0;$i<count($data);$i++)
			{
				if ($data[$i]['name'] == $Manufacturer->getman_name())
					$OptionId = $data[$i]['id'];
			}
			
			//l'associe
			$Manufacturer->setman_attribute_option_id($OptionId);
    	}
    	
    	$Manufacturer->save();
    	
    	//confirme
    	Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Manufacturer Saved'));
    	
    	//Redirige vers la fiche cr��e
    	$this->_redirect('adminhtml/Purchase_Manufacturers/Edit/man_id/'.$Manufacturer->getId());
    	
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('admin/erp/purchasing');
    }
}
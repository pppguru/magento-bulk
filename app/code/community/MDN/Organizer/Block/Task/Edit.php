<?php

class MDN_Organizer_Block_Task_Edit extends Mage_Adminhtml_Block_Widget_Form
{
	
	private $_task = null;
	private $_entityType = null;
	private $_entityId = null;
	private $_guid = null;
	
	/**
	 * Constructeur: on charge
	 *
	 */
	public function __construct()
	{
		parent::__construct();
		
		//charge
        $ot_id = Mage::app()->getRequest()->getParam('ot_id', false);	

	}
	
	/**
	 * Défini l'id de la tache a éditer
	 *
	 * @param unknown_type $otId
	 */
	public function setotId($otId)
	{
		if ($otId != '')
		{
	        $model = Mage::getModel('Organizer/Task');
			$this->_task = $model->load($otId);					
		}
	}
	public function setGuid($guid)
	{
		$this->_guid = $guid;
		return $this;
	}
	public function getGuid()
	{
		return $this->_guid;
	}
	
	public function getTask()
	{
		if ($this->_task == null)
		{
			$this->_task = Mage::getModel('Organizer/Task');
			$this->_task->setot_author_user(mage::helper('Organizer')->getCurrentUserId());
			$this->_task->setot_entity_type($this->getEntityType());
			$this->_task->setot_entity_id($this->getEntityId());
			$this->_task->setot_entity_description(mage::helper('Organizer')->getEntityDescription($this->getEntityType(), $this->getEntityId()));
		}
		return $this->_task;
	}
	
	/**
	 * Retourne le titre
	 *
	 */
	public function getTitle()
	{
		if (!$this->getTask()->getId())
			return $this->__('New Task');
		else
			return $this->__('Edit Task');
	}
	
	public function setEntityType($EntityType)
	{
		$this->_entityType = $EntityType;
		return $this;
	}
	public function getEntityType()
	{
		return $this->_entityType;
	}
	
	public function setEntityId($EntityId)
	{
		$this->_entityId = $EntityId;
		return $this;
	}
	public function getEntityId()
	{
		return $this->_entityId;
	}
	
}
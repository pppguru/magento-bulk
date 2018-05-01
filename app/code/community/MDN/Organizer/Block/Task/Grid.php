<?php

class MDN_Organizer_Block_Task_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
	private $_guid = null;
	
	static $_guidSequence = 1;
	
    private $_EntityId;
	private $_EntityType;
	private $_Mode;
	private $_EnableAdd = 1;
	private $_EnableSortFilter = 1;
	
	private $_ShowTarget = true;
	private $_ShowEntity = true;
	
	private $_Title = 'Organizer';
	
	public function __construct()
    {
        parent::__construct();
        $this->setId('OrganizerGrid'.$this->getGuid());
        $this->setDefaultSort('ot_id');
        $this->setDefaultDir('DESC');
        $this->_parentTemplate = $this->getTemplate();
        //$this->setTemplate('Organizer/Task/List.phtml');	
        $this->setEmptyText($this->__('No items'));
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        
        //$this->setDefaultFilter(array('ot_target_user'=>mage::helper('Organizer')->getCurrentUserId()));
        
    }
    
    /**
     * Gestion des entit�s (type & id)
     *
     * @param unknown_type $EntityId
     * @return unknown
     */
    public function setEntityId($EntityId)
    {
    	$this->_EntityId = $EntityId;
    	return $this;
    }
    public function getEntityId()
    {
    	return $this->_EntityId;
    }
    public function setEntityType($EntityType)
    {
    	$this->_EntityType = $EntityType;
    	return $this;
    }
    public function getEntityType()
    {
    	return $this->_EntityType;
    }
    public function setMode($mode)
    {
    	$this->_Mode = $mode;
    }
    public function getMode()
    {
    	return $this->_Mode;
    }
    
    public function setEnableAdd($enableAdd)
    {
    	$this->_EnableAdd = $enableAdd;
    }
    public function getEnableAdd()
    {
    	return $this->_EnableAdd;
    }
    
    public function setEnableSortFilter($enableSortFilter)
    {
    	$this->_EnableSortFilter = $enableSortFilter;
    	$this->setFilterVisibility($enableSortFilter);
    }
    public function getEnableSortFilter()
    {
    	return $this->_EnableSortFilter;
    }
    
    /**
     * Affichage de colonnes
     *
     * @param unknown_type $value
     */
    public function setShowTarget($value)
    {
    	$this->_ShowTarget = $value;
    	return $this;
    }
    public function setShowEntity($value)
    {
    	$this->_ShowEntity = $value;
    	return $this;
    }
    
    public function setTitle($title)
    {
    	$this->_Title = $title;
    }
    public function getTitle()
    {
    	return $this->_Title;
    }
    
    /**
     * Charge la collection
     *
     * @return unknown
     */
    protected function _prepareCollection()
    {		            
    	
        $collection = Mage::getResourceModel('Organizer/Task_Collection')
        	->getTasksForEntity($this->_EntityType, $this->_EntityId, $this->getMode());


        $this->setCollection($collection);
        return parent::_prepareCollection();
    }
    
   /**
     * D�fini les colonnes du grid
     *
     * @return unknown
     */
    protected function _prepareColumns()
    {
         $this->addColumn('ot_id', array(
            'header'=> Mage::helper('Organizer')->__('Id'),
            'index' => 'ot_id',
            'type'	=> 'number',
            'width'	=> '20px'
        ));
           
        if (true)
        {
        	$this->addColumn('Entity', array(
         	   'header'=> Mage::helper('Organizer')->__('Entity'),
	           'index' => 'ot_entity_description',
               'renderer'  => 'MDN_Organizer_Block_Widget_Column_Renderer_Entity',
        	));
        }
          
        $this->addColumn('ot_created_at', array(
            'header'=> Mage::helper('Organizer')->__('Date'),
            'index' => 'ot_created_at',
            'type'	=> 'date',
            'width'	=> '120px'
        ));
        
        $this->addColumn('Author', array(
            'header'=> Mage::helper('Organizer')->__('Author'),
            'index' => 'ot_author_user',
            'type'  => 'options',
            'options' => $this->getUsersAsArray()
        ));
        
        if (true)
        {
        	$this->addColumn('ot_target_user', array(
         	   'header'=> Mage::helper('Organizer')->__('Target'),
	            'index' => 'ot_target_user',
	            'type'  => 'options',
	            'options' => $this->getUsersAsArray()
        	));
        }

        $this->addColumn('ot_caption', array(
            'header'=> Mage::helper('Organizer')->__('Caption'),
            'index' => 'ot_caption',
            'filter_index' => 'ot_caption'
        ));

        $this->addColumn('ot_priority', array(
            'header'=> Mage::helper('Organizer')->__('Priority'),
            'index' => 'ot_priority',
            'type'	=> 'number',
            'width'	=> '80px',
            'sortable'  => true
        ));
                             
        $this->addColumn('Deadline', array(
            'header'=> Mage::helper('Organizer')->__('Deadline'),
            'index' => 'ot_deadline',
            'type'	=> 'date',
            'width'	=> '120px'
        ));

        $this->addColumn('Misc', array(
            'header'=> Mage::helper('Organizer')->__('Misc'),
            'index' => 'ot_id',
            'renderer'  => 'MDN_Organizer_Block_Widget_Column_Renderer_Misc',
            'align'	=> 'center',
            'filter'    => 'MDN_Organizer_Block_Widget_Column_Filter_Misc',
            'sortable'  => false
        ));
                                               
        $this->addColumn('Action', array(
            'header'=> Mage::helper('Organizer')->__('Action'),
            'index' => 'xx',
            'renderer'  => 'MDN_Organizer_Block_Widget_Column_Renderer_Action',
            'filter'    => false,
            'sortable'  => false,
            'align'	=> 'center',
            'guid' => $this->getGuid()
        ));
        
        return parent::_prepareColumns();
    }

     public function getGridUrl()
    {
        return $this->getUrl('adminhtml/Organizer_Task/EntityList',
        					array(
        						'entity_type'=>$this->_EntityType, 
        						'entity_id' => $this->_EntityId,
        						'show_target' => $this->_ShowTarget,
        						'show_entity' => $this->_ShowEntity,
        						'guid'	=> $this->_guid,
        						'mode'	=> $this->_Mode,
        						'enable_sort_filter' => $this->_EnableSortFilter
        						)
        					);
    }

    public function getGridParentHtml()
    {
        $templateName = Mage::getDesign()->getTemplateFilename($this->_parentTemplate, array('_relative'=>true));
        return $this->fetchView($templateName);
    }
    
    /**
     * Retourne la liste des utilisateurs sous la forme d'un array
     *
     */
    public function getUsersAsArray()
    {
    	//recupere la liste des utilisateurs
		$collection = mage::getModel('admin/user')
			->getCollection()
			->addFieldToFilter('is_active', 1);
		
		$retour = array();
		foreach ($collection as $item)
		{
			$retour[$item->getuser_id()] = $item->getusername();
		}
		
		
		return $retour;
    }

    /**
     * Retourne la liste des cat�gories sous la forme d'un array
     *
     */
    public function getCategoriesAsArray()
    {
		//recupere la liste des utilisateurs
		$collection = mage::getModel('Organizer/TaskCategory')
			->getCollection();
		
		$retour = array();
		foreach ($collection as $item)
		{
			$retour[$item->getotc_id()] = $item->getotc_name();
		}
		
		return $retour;
    }
    
    public function getGuid()
    {
    	if ($this->_guid == null)
    	{
    		if ($this->getRequest()->getParam('guid') != '')
    		{
    			$this->_guid = $this->getRequest()->getParam('guid');
    		}
    		else 
    		{
	    		$this->_guid = MDN_Organizer_Block_Task_Grid::$_guidSequence;
	    		MDN_Organizer_Block_Task_Grid::$_guidSequence += 1;
    		}
    	}
    	return $this->_guid;
    }
}

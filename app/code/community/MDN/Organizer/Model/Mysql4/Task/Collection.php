<?php
 
class MDN_Organizer_Model_Mysql4_Task_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('Organizer/Task');
    }
    

    /**
     * Retourne les taches pour une entité
     *
     * @param unknown_type $EntityType
     * @param unknown_type $EntityId
     */
    public function getTasksForEntity($EntityType, $EntityId, $Mode)
    {
    	//Rajoute les filtres
    	if ($EntityId != null)
    		$this->getSelect()->where('ot_entity_id=?',$EntityId);
    	if ($EntityType != null)
    		$this->getSelect()->where('ot_entity_type=?',$EntityType);

    	//ajoute les relations pour afficher le nom des utilisateurs (author & target)
    	$this->getSelect()->join(array('user_author'=>$this->getTable('admin/user')),
                '`user_author`.user_id=`main_table`.ot_author_user',
                array('user_author.username'=>'username'));
   	
    	$this->getSelect()->joinLeft(array('user_target'=>$this->getTable('admin/user')),
                '`user_target`.user_id=`main_table`.ot_target_user',
                array('user_target.username'=>'username'));
        
    	$this->getSelect()->joinLeft(array('tbl_category'=>$this->getTable('Organizer/TaskCategory')),
                '`tbl_category`.otc_id=`main_table`.ot_category',
                array('tbl_category.otc_name'=>'otc_name'));
        
        switch ($Mode) {
        	case 'late':
		    	$this->getSelect()->where('ot_finished=?', 0);
		    	$this->getSelect()->where('ot_deadline<=?', date('Y-m-d'));
		    	$this->getSelect()->where('ot_target_user is NULL or ot_target_user=?', mage::helper('Organizer')->getCurrentUserId());
        		break;
        	case 'notification':
		    	$this->getSelect()->where('ot_notification_read=?', 0);
		    	$this->getSelect()->where('ot_notify_date<=?', date('Y-m-d'));
		    	$this->getSelect()->where('ot_target_user is NULL or ot_target_user=?', mage::helper('Organizer')->getCurrentUserId());
        		break;
        	case 'mine':
		    	$this->getSelect()->where('ot_target_user='.mage::helper('Organizer')->getCurrentUserId().' or ot_author_user='.mage::helper('Organizer')->getCurrentUserId());
        		break;
        }
        
                
        return $this;
    }
   
}
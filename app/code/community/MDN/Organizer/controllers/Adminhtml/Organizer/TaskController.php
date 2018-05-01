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
class MDN_Organizer_Adminhtml_Organizer_TaskController extends Mage_Adminhtml_Controller_Action {

    /**
     * Display organizer list
     *
     */
    public function ListAction() {
        $this->loadLayout();

        $this->_setActiveMenu('erp');
        $this->getLayout()->getBlock('head')->setTitle($this->__('Organizers'));

        $this->renderLayout();
    }

    /**
     * Display organizer dashboard
     *
     */
    public function DashboardAction() {
        $this->loadLayout();

        $this->_setActiveMenu('erp');
        $this->getLayout()->getBlock('head')->setTitle($this->__('My Dashboard'));

        $this->renderLayout();
    }

    /**
     * New organizer
     *
     */
    public function NewAction() {
        $this->loadLayout();

        $this->_setActiveMenu('erp');
        $this->getLayout()->getBlock('head')->setTitle($this->__('New Organizer'));

        $this->renderLayout();
    }

    /**
     * Organizer Edition
     *
     */
    public function EditAction() {
        //recupere les infos
        $ot_id = Mage::app()->getRequest()->getParam('ot_id');

        //cree le block et le retourne
        $block = $this->getLayout()->createBlock('Organizer/Task_Edit', 'taskedit');
        $block->setotId($ot_id);
        $block->setGuid(Mage::app()->getRequest()->getParam('guid'));
        $block->setTemplate('Organizer/Task/Edit.phtml');

        $this->getResponse()->setBody($block->toHtml());
    }

    /**
     * Met a jour (en ajax)
     *
     */
    public function SaveAction() {
        $ok = true;
        $msg = Mage::helper('Organizer')->__('Task saved');


        try {
            //save
            $Task = Mage::getModel('Organizer/Task')
                            ->load($this->getRequest()->getPost('ot_id'))
                            ->setot_author_user($this->getRequest()->getPost('ot_author_user'))
                            ->setot_caption($this->getRequest()->getPost('ot_caption'))
                            ->setot_description($this->getRequest()->getPost('ot_description'))
                            ->setot_entity_type($this->getRequest()->getPost('ot_entity_type'))
                            ->setot_entity_id($this->getRequest()->getPost('ot_entity_id'))
                            ->setot_entity_description($this->getRequest()->getPost('ot_entity_description'))
                            ->setot_finished($this->getRequest()->getPost('ot_finished'));

            $target = $this->getRequest()->getPost('ot_target_user');
            if ($target > 0)
                $Task->setot_target_user($target);
            else
                $Task->setot_target_user('');

            if ($this->getRequest()->getPost('ot_deadline') != '')
                $Task->setot_deadline($this->getRequest()->getPost('ot_deadline'));
            if ($this->getRequest()->getPost('ot_notify_date') != '')
                $Task->setot_notify_date($this->getRequest()->getPost('ot_notify_date'));
            if ($this->getRequest()->getPost('ot_id') == '')
                $Task->setot_created_at(date('Y-m-d H:i'));

            $Task->setot_priority($this->getRequest()->getPost('ot_priority'));


            $Task->save();

            //Test if we have to notify target
            if ($this->getRequest()->getPost('notify_target') == 1) {
                if ($target > 0) {
                    $Task->notifyTarget();
                }
            }

            $ok = true;
        } catch (Exception $ex) {
            $msg = $ex->getMessage();
            $ok = false;
        }

        //Retourne
        $response = array(
            'error' => (!$ok),
            'message' => $this->__($msg)
        );
        $response = Zend_Json::encode($response);
        $this->getResponse()->setBody($response);
    }

    /**
     * Returns block with list of task
     *
     */
    public function EntityListAction() {
        //recupere les infos
        $entity_type = Mage::app()->getRequest()->getParam('entity_type');
        $entity_id = Mage::app()->getRequest()->getParam('entity_id');

        //cree le block et le retourne
        $block = $this->getLayout()->createBlock('Organizer/Task_Grid', 'tasklist');
        $block->setEntityId($entity_id);
        $block->setEntityType($entity_type);
        $block->setShowEntity(Mage::app()->getRequest()->getParam('show_entity'));
        $block->setMode(Mage::app()->getRequest()->getParam('mode'));
        $block->setShowTarget(Mage::app()->getRequest()->getParam('show_target'));
        $block->setEnableSortFilter(Mage::app()->getRequest()->getParam('enable_sort_filter'));

        $this->getResponse()->setBody($block->toHtml());
    }

    /**
     * Supprime une tache
     *
     */
    public function DeleteAction() {
        $ok = true;
        $msg = Mage::helper('Organizer')->__('Task deleted');

        try {
            //recupere les infos
            $otId = Mage::app()->getRequest()->getParam('ot_id');
            $Task = mage::getModel('Organizer/Task')->load($otId);
            $Task->delete();
        } catch (Exception $ex) {
            $msg = $ex->getMessage();
            $ok = false;
        }

        //Retourne
        $response = array(
            'error' => (!$ok),
            'message' => $this->__($msg)
        );
        $response = Zend_Json::encode($response);
        $this->getResponse()->setBody($response);
    }

    /**
     * Notify target
     *
     */
    public function NotifyAction() {
        $otId = Mage::app()->getRequest()->getParam('ot_id');
        $Task = mage::getModel('Organizer/Task')->load($otId);
        $Task->notifyTarget();
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('admin/erp/organizer');
    }

}

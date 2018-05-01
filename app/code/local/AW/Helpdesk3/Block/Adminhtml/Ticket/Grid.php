<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This software is designed to work with Magento community edition and
 * its use on an edition other than specified is prohibited. aheadWorks does not
 * provide extension support in case of incorrect edition use.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Helpdesk3
 * @version    3.3.0
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Helpdesk3_Block_Adminhtml_Ticket_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    protected $_unnecessaryColumn = array();

    public function __construct()
    {
        parent::__construct();
        $this->setId('awhdu3TicketGrid');
        $this->setDefaultSort('last_message_date');
        $this->setDefaultDir('DESC');
        $this->setDefaultFilter(
            array(
                'status' => AW_Helpdesk3_Model_Source_Ticket_Status::NEW_AND_OPEN_VALUE,
            )
        );
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        /** @var AW_Helpdesk3_Model_Resource_Ticket_Collection $collection */
        $collection = Mage::getModel('aw_hdu3/ticket')->getCollection();
        $collection->addNotArchivedFilter();
        $collection->joinMessagesTable();
        if ($this->_userMode && $this->getCustomerName()) {
            $collection->addFilterByCustomer($this->getCustomerName());
        }
        if ($this->_userMode && $this->getCustomerEmail()) {
            $collection->addFilterByCustomer($this->getCustomerEmail());
        }
        if ($this->_userMode && $this->getOrderIncrementId()) {
            $collection->addFilterByOrder($this->getOrderIncrementId());
        }
        $storeId = $this->getRequest()->getParam('store', null);
        if (null !== $storeId) {
            $collection->addFilterByStoreId($storeId);
        }
        $search = $this->getRequest()->getParam('search', '');
        if (!empty($search)) {
            $collection->addFilterBySearch(base64_decode($search));
        }
        if (!$this->_userMode) {
            $collection->addAdminUserFilter(Mage::getSingleton('admin/session')->getUser());
        }
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'id',
            array(
                'header' => $this->__('ID'),
                'align'  => 'right',
                'width'  => 80,
                'index'  => 'uid',
            )
        );
        $this->addColumn(
            'last_message_date',
            array(
                'header'   => $this->__('Last Message'),
                'type'     => 'datetime',
                'index'    => 'last_message_date',
                'align'    => 'right',
                'width'    => 50,
                'filter_index' => Mage::getModel('aw_hdu3/ticket')->getCollection()->getLastMessageDateFilterIndex(),
                'renderer' => 'AW_Helpdesk3_Block_Adminhtml_Ticket_Grid_Column_Renderer_Datetime'
            )
        );
        $this->addColumn(
            'department',
            array(
                'header'    => $this->__('Department'),
                'align'     =>'left',
                'index'     => 'department_id',
                'filter_index' => 'main_table.department_id',
                'type'      => 'options',
                'options'   => Mage::getModel('aw_hdu3/department')->getCollection()->addNotDeletedFilter()->toOptionHash(),
                'width'     => 100,
            )
        );
        $this->addColumn(
            'agent',
            array(
                'header'    => $this->__('Help Desk Agent'),
                'align'     =>'left',
                'index'     => 'department_agent_id',
                'type'      => 'options',
                'options'   => $this->_getAgentList(),
                'width'     => 100,
            )
        );
        $this->addColumn(
            'title',
            array(
                'header'   => $this->__('Subject'),
                'align'    => 'left',
                'index'    => 'subject',
                'truncate' => 80,
                'escape' => true,
                'renderer' => 'AW_Helpdesk3_Block_Adminhtml_Ticket_Grid_Column_Renderer_Title'
            )
        );
        $this->addColumn(
            'order_number',
            array(
                'header'   => $this->__('Order #'),
                'align'    => 'left',
                'index'    => 'order_increment_id',
                'truncate' => 80,
                'renderer' => 'AW_Helpdesk3_Block_Adminhtml_Ticket_Grid_Column_Renderer_Order'
            )
        );
        $this->addColumn(
            'customer',
            array(
                'header' => $this->__('Customer'),
                'align'  => 'left',
                'index'  => 'customer_name',
                'width'     => 200,
                'filter_condition_callback' => array($this, 'filterCustomer'),
                'renderer' => 'AW_Helpdesk3_Block_Adminhtml_Ticket_Grid_Column_Renderer_Customer'
            )
        );
        $this->addColumn(
            'priority',
            array(
                'header'           => $this->__('Priority'),
                'align'            => 'center',
                'index'            => 'priority',
                'type'             => 'options',
                'options'          => AW_Helpdesk3_Model_Source_Ticket_Priority::toOptionHashExpanded(),
                'width'            => 100,
                'renderer'         => 'AW_Helpdesk3_Block_Adminhtml_Ticket_Grid_Column_Renderer_Priority'
            )
        );
        $this->addColumn(
            'store_view',
            array(
                'header'    => $this->__('Store View'),
                'align'     =>'left',
                'index'     => 'store_id',
                'type'      => 'store',
                'sortable'  => false,
                'renderer'  => 'aw_hdu3/adminhtml_widget_grid_renderer_storeIds',
                'filter_index' => 'main_table.store_id',
                'width'     => 200,
            )
        );
        $this->addColumn(
            'messages_count',
            array(
                'header' => $this->__('Messages'),
                'align'  => 'left',
                'type'  => 'number',
                'index' => 'messages_count',
                'filter_index' => Mage::getModel('aw_hdu3/ticket')->getCollection()->getMessagesCountFilterIndex(),
                'width' => 50,
            )
        );
        $this->addColumn(
            'status',
            array(
                'header'           => $this->__('Status'),
                'align'            => 'center',
                'index'            => 'status',
                'type'             => 'options',
                'options'          => AW_Helpdesk3_Model_Source_Ticket_Status::toOptionHashExpanded() + AW_Helpdesk3_Model_Source_Ticket_Status::getNewAndOpenStatusToOptionHash(),
                'width'            => 135,
                'filter_condition_callback' => array($this, 'filterStatus'),
                'renderer'         => 'AW_Helpdesk3_Block_Adminhtml_Ticket_Grid_Column_Renderer_Status'
            )
        );

        $this->addColumn(
            'rate',
            array(
                'header' => $this->__('Rate'),
                'align' => 'center',
                'index' => 'rate',
                'type' => 'number',
                'width' => 50,
            )
        );

        $this->addColumn(
            'lock',
            array(
                'header'    => $this->__('Lock'),
                'align'     =>'left',
                'index'     => 'is_locked',
                'type'      => 'options',
                'options'   => AW_Helpdesk3_Model_Source_Ticket_Lock::toOptionHash(),
                'width'     => 50,
            )
        );
        $this->addColumn(
            'created_at',
            array(
                'header'   => $this->__('Created'),
                'type'     => 'datetime',
                'index'    => 'created_at',
                'align'    => 'right',
                'width'    => 100,
                'renderer' => 'AW_Helpdesk3_Block_Adminhtml_Ticket_Grid_Column_Renderer_Datetime'
            )
        );
        if (!$this->_userMode) {
            $this->addColumn(
                'action',
                array(
                    'type' => 'text',
                    'header'    => $this->__('Action'),
                    'width'     => 70,
                    'renderer' => 'AW_Helpdesk3_Block_Adminhtml_Ticket_Grid_Column_Renderer_Assign',
                    'filter'    => false,
                    'sortable'  => false,
                    'is_system' => true,
                )
            );
            $this->addExportType('*/*/exportCsv', $this->__('CSV'));
            $this->addExportType('*/*/exportXml', $this->__('Excel XML'));
        }
        parent::_prepareColumns();
        $availableColumnList = Mage::helper('aw_hdu3/config')->getTicketDisplayColumn();

        $isAllColumnsDisplayed = true;
        if ($availableColumnList) {
            $availableColumnList = explode(',', $availableColumnList);
            $isAllColumnsDisplayed = false;
        } else {
            $availableColumnList = array();
        }

        foreach($this->getColumns() as $column) {
            if ((array_search($column->getId(),$availableColumnList) === false && !$isAllColumnsDisplayed)
                || array_search($column->getId(),$this->_unnecessaryColumn) !== false) {

                $this->removeColumn($column->getId());
            }
        }
        return $this;
    }

    protected function filterStatus($collection, $column)
    {
        if ($column->getFilter()->getValue() == AW_Helpdesk3_Model_Source_Ticket_Status::NEW_AND_OPEN_VALUE) {
            $collection->addFilterByStatusIds(
                array(
                    AW_Helpdesk3_Model_Source_Ticket_Status::OPEN_VALUE,
                    AW_Helpdesk3_Model_Source_Ticket_Status::NEW_VALUE
                )
            );
        } else {
            $collection->addFilterByStatus($column->getFilter()->getValue());
        }
        return $this;
    }

    protected function filterCustomer($collection, $column)
    {
        $collection->addFilterByCustomer($column->getFilter()->getValue());
        return $this;
    }

    public function getRowUrl($row)
    {
        return false;
    }

    protected function _prepareMassaction()
    {
        if (!$this->_userMode) {
            $this->setMassactionIdField('main_table.id');
            $this->getMassactionBlock()->setFormFieldName('ticketIds');
            $this->getMassactionBlock()->addItem(
                'assign_to_department',
                array(
                    'label' => $this->__('Assign to Department'),
                    'url'   => $this->getUrl('*/*/massAssignToDepartment'),
                    'additional' => array(
                        'visibility' => array(
                            'name'   => 'department_list',
                            'type'   => 'select',
                            'class'  => 'required-entry',
                            'label'  => $this->__('Department'),
                            'values' => Mage::getModel('aw_hdu3/department')->getCollection()->addActiveFilter()->toOptionHash()
                        )
                    )
                )
            );
            $this->getMassactionBlock()->addItem(
                'assign_to_agent',
                array(
                    'label' => $this->__('Assign to Agent'),
                    'url'   => $this->getUrl('*/*/massAssignToAgent'),
                    'additional' => array(
                        'visibility' => array(
                            'name'   => 'agent_list',
                            'type'   => 'select',
                            'class'  => 'required-entry',
                            'label'  => $this->__('Agent'),
                            'values' => Mage::getModel('aw_hdu3/department_agent')->getCollection()->addActiveFilter()->toOptionHash()
                        )
                    )
                )
            );
            $this->getMassactionBlock()->addItem(
                'change_status',
                array(
                    'label' => $this->__('Change Status'),
                    'url'   => $this->getUrl('*/*/massChangeStatus'),
                    'additional' => array(
                        'visibility' => array(
                            'name'   => 'status_list',
                            'type'   => 'select',
                            'class'  => 'required-entry',
                            'label'  => $this->__('Status'),
                            'values' => AW_Helpdesk3_Model_Source_Ticket_Status::toOptionHash()
                        )
                    )
                )
            );
            $this->getMassactionBlock()->addItem(
                'change_priority',
                array(
                    'label' => $this->__('Change Priority'),
                    'url'   => $this->getUrl('*/*/massChangePriority'),
                    'additional' => array(
                        'visibility' => array(
                            'name'   => 'priority_list',
                            'type'   => 'select',
                            'class'  => 'required-entry',
                            'label'  => $this->__('Priority'),
                            'values' => AW_Helpdesk3_Model_Source_Ticket_Priority::toOptionHash()
                        )
                    )
                )
            );
            $this->getMassactionBlock()->addItem(
                'change_lock_status',
                array(
                    'label' => $this->__('Change Lock Status'),
                    'url'   => $this->getUrl('*/*/massChangeLockStatus'),
                    'additional' => array(
                        'visibility' => array(
                            'name'   => 'lock_list',
                            'type'   => 'select',
                            'class'  => 'required-entry',
                            'label'  => $this->__('Status'),
                            'values' => AW_Helpdesk3_Model_Source_Ticket_Lock::toOptionHash()
                        )
                    )
                )
            );
            $this->getMassactionBlock()->addItem(
                'change_lock_status',
                array(
                    'label' => $this->__('Delete'),
                    'url'   => $this->getUrl('*/*/massDelete'),
                    'additional' => array(
                        'visibility' => array(
                            'name'   => 'answer',
                            'type'   => 'select',
                            'class'  => 'required-entry',
                            'label'  => $this->__('Delete?'),
                            'values' => Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray(),
                        )
                    )
                )
            );
        }
        return $this;
    }

    public function setOnePage()
    {
        $this->_defaultLimit = 0;
        return $this;
    }

    public function setUserMode($id = 1)
    {
        $this->_userMode = $id;
    }

    public function setUnnecessaryColumn($columns)
    {
        if (is_array($columns)) {
            $this->_unnecessaryColumn = $columns;
        } else {
            $this->_unnecessaryColumn = array($columns);
        }
    }

    /**
     * Remove existing column
     *
     * @param string $columnId
     * @return Mage_Adminhtml_Block_Widget_Grid
     */
    public function removeColumn($columnId)
    {
        if (isset($this->_columns[$columnId])) {
            unset($this->_columns[$columnId]);
            if ($this->_lastColumnId == $columnId) {
                $this->_lastColumnId = key($this->_columns);
            }
        }
        return $this;
    }

    public function getGridUrl()
    {
        $params = Mage::app()->getRequest()->getParams();
        if (isset($params['filter']) && $params['filter'] === '') {
            $params['filter'] = " ";
        }
        if ($this->_userMode) {
            $params = array('active_tab' => 'Tickets');
        }
        return $this->getCurrentUrl($params);
    }

    /**
     * @return array
     */
    protected function _getAgentList()
    {
        $agentNameList = array();
        $agentCollection = Mage::getModel('aw_hdu3/department_agent')->getCollection();
        foreach($agentCollection as $agent) {
            if ($agent->getStatus() == AW_Helpdesk3_Model_Source_Status::DELETED_VALUE && !count($agent->getTicketCollection())) {
                continue;
            }
            if(count($agent->getDepartmentCollection())) {
                $agentNameList[$agent->getId()] = $agent->getName();
            }
        }
        return $agentNameList;
    }
}

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
 * @package    AW_Marketsuite
 * @version    2.1.0
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_Marketsuite_Adminhtml_FilterController extends Mage_Adminhtml_Controller_Action
{
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('marketsuite/managerules');
    }

    public function indexAction()
    {
        $this->_title($this->__('MSS'))->_title($this->__('Manage Rules'));
        $this->loadLayout();
        $this->_setActiveMenu('marketsuite');
        $this->renderLayout();
    }

    public function newAction()
    {
        $this->_forward('edit');
    }

    public function editAction()
    {
        $filterId = $this->getRequest()->getParam('id', null);
        $model = Mage::getModel('marketsuite/filter')->load($filterId);

        if (!is_null($filterId) && !$model->getId()) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('marketsuite')->__('This filter no longer exists')
            );
            $this->_redirect('*/*');
            return;
        }

        $model->getConditions()->setJsFormObject('rule_conditions_fieldset');
        $data = Mage::getSingleton('adminhtml/session')->getPageData();
        Mage::getSingleton('adminhtml/session')->setPageData(null);
        if (!empty($data)) {
            $data['conditions'] = $data['rule']['conditions'];
            unset($data['rule']);
            $model->loadPost($data);
        }

        Mage::register('marketsuitefilters_data', $model);

        $this->_title($this->__('MSS'));
        if ($model->getId()) {
            $this->_title($this->__('Edit Rule'));
        } else {
            $this->_title($this->__('New Rule'));
        }

        $this->loadLayout();
        $this->_setActiveMenu('marketsuite');
        $this->renderLayout();
    }

    public function saveRuleAction()
    {
        if ($data = $this->getRequest()->getPost()) {
            $result = array(
                'error'    => false,
                'parts'    => 0,
                'redirect' => "",
            );
            $redirectBack = $this->getRequest()->getParam('back', false);
            $activeTab = $this->getRequest()->getParam('active_tab', false);
            try {
                $filterId = $this->getRequest()->getParam('filter_id', null);
                $ruleModel = Mage::getModel('marketsuite/filter')->load($filterId);
                if (!is_null($filterId)) {
                    if (!$ruleModel->getId()) {
                        Mage::throwException(Mage::helper('marketsuite')->__('Wrong rule specified!'));
                    }
                }

                $data['conditions'] = $data['rule']['conditions'];
                unset($data['rule']);
                $ruleModel->loadPost($data);

                if ($this->getRequest()->getParam('save_as_flag')) {
                    $ruleModel
                        ->setId(null)
                        ->setProgressPercent(null)
                    ;
                }

                if ($this->getRequest()->getParam('reindex', false)) {
                    $ruleModel
                        ->setProgressPercent(0)
                        ->setUpdatedAtFlag(true)
                        ->save()
                    ;

                    Mage::getModel('marketsuite/index_customer')->clearIndexByRule($ruleModel);
                    Mage::getModel('marketsuite/index_order')->clearIndexByRule($ruleModel);

                    Mage::getSingleton('adminhtml/session')->addSuccess(
                        Mage::helper('marketsuite')->__(
                            'Rule "%s" has been saved. Reindex has been successfully done.', $ruleModel->getName()
                        )
                    );
                } else {
                    $ruleModel
                        ->setProgressPercent(null)
                        ->setUpdatedAtFlag(true)
                        ->save()
                    ;

                    Mage::getModel('marketsuite/index_customer')->clearIndexByRule($ruleModel);
                    Mage::getModel('marketsuite/index_order')->clearIndexByRule($ruleModel);

                    Mage::getSingleton('adminhtml/session')->addSuccess(
                        Mage::helper('marketsuite')->__('Rule "%s" has been saved.', $ruleModel->getName())
                    );
                }

                Mage::getSingleton('adminhtml/session')->setPageData(false);
                $result['rule_id'] = $ruleModel->getId();

                $result['parts'] = Mage::helper('marketsuite/progress')->getPageCount();
                if ($redirectBack) {
                    $params = array(
                        'id' => $ruleModel->getId(),
                    );
                    if ($activeTab) {
                        $params['active_tab'] = $activeTab;
                    }
                    $result['redirect'] = $this->getUrl('*/*/edit', $params);
                } else {
                    $result['redirect'] = $this->getUrl('*/*/');
                }
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setPageData($data);
                $result['error'] = true;

                $params = array(
                    'id' => $this->getRequest()->getParam('filter_id'),
                );
                if ($activeTab) {
                    $params['active_tab'] = $activeTab;
                }
                $result['redirect'] = $this->getUrl('*/*/edit', $params);
            }
            $this->getResponse()
                ->setHeader('Content-Type', 'application/json', true)
                ->setBody(Mage::helper('core')->jsonEncode($result));
        }
    }

    public function isReindexRunningAction()
    {
        $result = array(
            'is_reindex_running' => false,
        );

        $filterId = $this->getRequest()->getParam('filter_id', null);
        $ruleModel = Mage::getModel('marketsuite/filter')->load($filterId);
        if (is_null($ruleModel->getId())) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('marketsuite')->__('Wrong rule specified!'));
            $this->_redirect('*/*/');
        }

        if ($ruleModel->isReindexRunning()) {
            $result['is_reindex_running'] = true;
        }

        $this->getResponse()
            ->setHeader('Content-Type', 'application/json', true)
            ->setBody(Mage::helper('core')->jsonEncode($result));
    }

    public function checkAction()
    {
        $result = Mage::helper('marketsuite/filter')->getFilterCollectionGridData();
        $this->getResponse()
            ->setHeader('Content-Type', 'application/json', true)
            ->setBody(Mage::helper('core')->jsonEncode($result));
    }

    public function updateIndexAction()
    {
        $currentPage = (int)$this->getRequest()->getParam('page', null);
        $pageCount = (int)$this->getRequest()->getParam('parts', null);
        $ruleId = (int)$this->getRequest()->getParam('rule', null);

        $result = array(
            'error'   => false,
            'msg'     => array(),
            'percent' => 0,
        );

        $ruleModel = Mage::getModel('marketsuite/filter')->load($ruleId);

        $productCollection = Mage::getResourceModel('catalog/product_collection');
        $productCollection->addWebsiteFilter(array_keys(Mage::app()->getWebsites()));
        $ruleModel->getConditions()->collectValidatedAttributes($productCollection);

        if (is_null($currentPage) || is_null($pageCount) || is_null($ruleModel->getId())) {
            $result['error'] = true;
            $result['msg'][] = Mage::helper('marketsuite')->__('Invalid data');
        }
        if (!$result['error']) {

            try {
                Mage::getModel('marketsuite/index_customer')->processPage($ruleModel, $currentPage);
                Mage::getModel('marketsuite/index_order')->processPage($ruleModel, $currentPage);
            } catch (Exception $e) {
                Mage::getModel('marketsuite/index_customer')->clearIndexByRule($ruleModel);
                Mage::getModel('marketsuite/index_order')->clearIndexByRule($ruleModel);
                $result['msg'][] = $e->getMessage();
            }

            $progress = 100;
            if ($currentPage !== $pageCount) {
                $progress = Mage::helper('marketsuite/progress')->getCurrentProgress($currentPage, $pageCount);
            }
            $ruleModel->setProgressPercent($progress);
            $ruleModel->save();
            $result['percent'] = $progress;
        }

        $this->getResponse()
            ->setHeader('Content-Type', 'application/json', true)
            ->setBody(Mage::helper('core')->jsonEncode($result))
        ;
    }

    public function deleteAction()
    {
        $filterId = $this->getRequest()->getParam('id', null);
        $filterModel = Mage::getModel('marketsuite/filter')->load($filterId);
        if (!is_null($filterModel->getId())) {
            try {
                if ($filterModel->isReindexRunning()) {
                    Mage::throwException(
                        Mage::helper('marketsuite')->__('Reindex process running. Please wait and try again')
                    );
                }
                $filterModel->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('marketsuite')->__('Rule was successfully deleted')
                );
                $this->_redirect('*/*/');
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        Mage::getSingleton('adminhtml/session')->addError(
            Mage::helper('marketsuite')->__('Unable to find a rule to delete')
        );
        $this->_redirect('*/*/');
    }

    public function newConditionHtmlAction()
    {
        $id = $this->getRequest()->getParam('id');
        $typeArr = explode('|', str_replace('-', '/', $this->getRequest()->getParam('type')));
        $type = $typeArr[0];

        $model = Mage::getModel($type)
            ->setId($id)
            ->setType($type)
            ->setRule(Mage::getModel('marketsuite/filter'))
            ->setPrefix('conditions');
        if (!empty($typeArr[1])) {
            $model->setAttribute($typeArr[1]);
        }

        if ($model instanceof Mage_Rule_Model_Condition_Abstract) {
            $model->setJsFormObject($this->getRequest()->getParam('form'));
            $html = $model->asHtmlRecursive();
        } else {
            $html = '';
        }
        $this->getResponse()->setBody($html);
    }
}
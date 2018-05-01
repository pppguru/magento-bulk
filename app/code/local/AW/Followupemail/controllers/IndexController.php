<?php
class AW_Followupemail_IndexController extends Mage_Core_Controller_Front_Action
{
    /*
     * Unsubscribes customer
     */
    public function unsubscribeAction()
    {
        $code = $this->getRequest()->getParam('code');
        if (!$code || !$queue = Mage::getModel('followupemail/queue')->loadByCode($code)) {
            Mage::getSingleton('core/session')->addError($this->__('Wrong unsubscription code specified'));
            $this->_redirect('/');
            return;
        }
        $unsubscribeFromAll = (bool)$this->getRequest()->getParam('from_all');
        $customerEmail = $queue->getData('recipient_email');

        if ($customerEmail && $queue->getRuleId()) {
            $rule = Mage::getModel('followupemail/rule')->load($queue->getRuleId());
            if ($queue->getParam('customer_id')) {
                $rule->unsubscribeCustomer($queue->getParam('customer_id'))->save();
            }
            // Cancel all scheduled and 'Ready to go' messages to this email
            $queuedEmails = Mage::getModel('followupemail/queue')->getCollection();
            $queuedEmails->addFieldToFilter(
                'status', AW_Followupemail_Model_Source_Queue_Status::QUEUE_STATUS_READY
            );
            $queuedEmails->addFieldToFilter('recipient_email', $customerEmail);
            if (!$unsubscribeFromAll) {
                $queuedEmails->addFieldToFilter('rule_id', $rule->getId());
            }
            foreach ($queuedEmails as $email) {
                $email->cancel();
            }
            $unsubscribeRuleId = $unsubscribeFromAll ? AW_Followupemail_Model_Rule::ALL_RULES : $rule->getId();

            $unsubscribeRecordsCount = Mage::getModel('followupemail/unsubscribe')->getCollection()
                ->addEmailFilter($customerEmail)
                ->addRuleFilter($unsubscribeRuleId)
                ->addStoreFilter(Mage::app()->getStore()->getId())
                ->addIsUnsubscribedFilter(true)
                ->count();
            if ($unsubscribeRecordsCount) {
                Mage::getSingleton('core/session')->addError(
                    $this->__('You are already unsubscribed.')
                );
            } else {
                $unsubscribedCustomer = Mage::getModel('followupemail/unsubscribe');
                $unsubscribedCustomer
                    ->setStoreId(Mage::app()->getStore()->getId())
                    ->setCustomerId($queue->getParam('customer_id'))
                    ->setCustomerEmail($customerEmail)
                    ->setRuleId($unsubscribeRuleId)
                    ->setIsUnsubscribed(1)
                    ->save();

                if ($unsubscribeFromAll) {
                    Mage::getSingleton('core/session')->addSuccess(
                        $this->__('You have been successfully unsubscribed from all follow-up messages.')
                    );
                    Mage::getSingleton('followupemail/log')->logWarning(
                        'unsubscribe from all action, customer ' . $queue->getParam('customer_id'),
                        $this
                    );
                } else {
                    Mage::getSingleton('core/session')->addSuccess(
                        $this->__('You have been successfully unsubscribed from receiving the same messages')
                    );
                    Mage::getSingleton('followupemail/log')->logWarning(
                        'unsubscribe rule action, customer ' . $queue->getParam('customer_id') . ' rule '
                        . $queue->getRuleId(), $this
                    );
                }
            }
        }
        if ($goto = urldecode($this->getRequest()->getParam('goto'))) {
            $this->_redirect($goto);
        } else {
            $this->_redirect('/');
        }
    }

    /*
     * Restores customer session and (if rule type was 'Cart has been abandened') customer's abandoned cart
     */
    public function resumeAction()
    {
        if ($code = $this->getRequest()->getParam('code')) {
            if (!$queue = Mage::getModel('followupemail/queue')->loadByCode($code)) {
                Mage::getSingleton('core/session')->addError($this->__('Wrong resume code specified'));
                $this->_redirect('/');
                return;
            }

            Mage::getModel('followupemail/linktracking')
                ->setId(null)
                ->setQueueId($queue->getId())
                ->setVisitedAt(date(AW_Followupemail_Model_Mysql4_Queue::MYSQL_DATETIME_FORMAT, time()))
                ->setVisitedFrom(isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '')
                ->save();

            $rule = Mage::getModel('followupemail/rule')->load($queue->getRuleId());

            if (AW_Followupemail_Model_Source_Rule_Types::RULE_TYPE_ABANDONED_CART_NEW == $rule->getEventType()) {
                if ($quoteId = $queue->getObjectId()) {
                    $quote = Mage::getModel('sales/quote')->load($quoteId);
                    Mage::getSingleton('checkout/session')->replaceQuote($quote);
                    $message = 'abandoned cart restored, cart_id=' . $quoteId . ', queue_id=' . $queue->getId();
                    $subject = "Abandoned cart restored";
                    Mage::getSingleton('followupemail/log')->logSuccess($message, $this, $subject);
                }
            }

            $customer = Mage::getModel('customer/customer')
                ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
                ->loadByEmail($queue->getRecipientEmail());

            if ($customerId = $customer->getId()) {
                $session = Mage::getSingleton('customer/session');
                if ($session->isLoggedIn() && $customerId != $session->getCustomerId()) {
                    $session->logout();
                }

                //restore persistent cart for customer
                if (Mage::helper('core')->isModuleEnabled('Mage_Persistent') && Mage::helper('persistent')->isEnabled()) {
                    $persistentSession = Mage::getModel('persistent/session')->loadByCustomerId($customerId);
                    $persistentLifeTime = Mage::helper('persistent')->getLifeTime();

                    Mage::getSingleton('core/cookie')->set(
                        Mage_Persistent_Model_Session::COOKIE_NAME,
                        $persistentSession->getKey(),
                        $persistentLifeTime
                    );
                }

                $autoLogIn = Mage::getStoreConfig('followupemail/general/customerautologin');
                if ($autoLogIn) {
                    try {
                        $session->setCustomerAsLoggedIn($customer);
                    } catch (Exception $ex) {
                        Mage::getSingleton('core/session')->addError($this->__("Your account isn't confirmed"));
                        $this->_redirect('/');
                    }
                }
            }
            Mage::getModel('followupemail/events')->customerCameBack($queue);
            $tracking = Mage::helper('followupemail')->getGaConfig($rule);
            if ($goto = urldecode($this->getRequest()->getParam('goto'))) {
                $this->getResponse()->setRedirect(Mage::getUrl($goto) . $tracking);
            } else {
                $this->getResponse()->setRedirect(Mage::getUrl('checkout/cart') . $tracking);
            }
        } else {
            Mage::getSingleton('core/session')->addError($this->__('No resume code specified'));
            $this->_redirect('/');
        }
    }

    public function imageAction()
    {
        $productId = $this->getRequest()->getParam('product_id', 0);
        $dimension = intval($this->getRequest()->getParam(
            'dimension', AW_Followupemail_Model_Filter::THUMBNAIL_DIMENSION_DEFAULT
        ));
        $storeId = $this->getRequest()->getParam('store_id', 0);
        $productModel = Mage::getModel('catalog/product')
            ->setStoreId($storeId)
            ->load($productId);

        $thumbnail = $productModel->getThumbnail();

        $imageHelper = Mage::helper('followupemail/image')
            ->init($productModel, 'thumbnail', $thumbnail)
            ->resize($dimension)
        ;
        $imageHelper->processImage();

        $this
            ->getResponse()
            ->setHeader('Content-type', $imageHelper->getContentType(), true)
            ->setBody($imageHelper->getFileContent())
            ->sendResponse()
        ;
    }
}

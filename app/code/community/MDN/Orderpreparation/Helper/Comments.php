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
class MDN_Orderpreparation_Helper_Comments extends Mage_Core_Helper_Abstract {

    /**
     * Return all comments possible for given order
     * 
     * Return a string for display
     *
     * @param type $orderId
     */
    public function getAll($order){
        $comments = '' ;

        //find order id as $order can be sales/order OR orderpreparation/ordertoprepare
        $orderId = $order->getorder_id();
        if (!$orderId)
            $orderId = $order->getId();

        //organizer of this order
        if (Mage::getStoreConfig('orderpreparation/download_document_options/print_organiser_comments') == 1) {
            $comments .= (string) mage::helper('Organizer')->getEntityCommentsSummary('order', $orderId, false);
        }

        //order comments
        $orderComments = $order->getAllStatusHistory();
        if(count($orderComments) > 0){
            foreach ($orderComments as $historyItem){
                $comment = $historyItem->getData('comment');
                if($comment){

                    if (Mage::getStoreConfig('orderpreparation/download_document_options/print_order_public_comments') == 1) {
                        if ($historyItem->getData('is_visible_on_front')){
                            $comments .= mage::helper('core')->formatDate($historyItem->getData('created_at'), 'medium').' - '.$comment."\n";
                        }
                    }

                    if (Mage::getStoreConfig('orderpreparation/download_document_options/print_order_private_comments') == 1) {
                        if (!$historyItem->getData('is_visible_on_front')){
                            $comments .= mage::helper('core')->formatDate($historyItem->getData('created_at'), 'medium').' - '.$comment."\n";
                        }
                    }
                }
            }
        }

        //GIFT Message
        if ($order->getGiftMessageId()) {
            //get Order gift message
            $orderGiftMessageId = $order->getGiftMessageId();
            $giftMessage = Mage::getModel("giftmessage/message")->load($orderGiftMessageId);
            $comments .= $this->__('Gift message from %s to %s : %s', $giftMessage->getSender(), $giftMessage->getRecipient(), $giftMessage->getMessage())."\n";

            //get order items gift messages
            $orderItems = $order->getItemsCollection();

            foreach ($orderItems as $item){
                if($item->getgift_message_id()>0  && ($item->getgift_message_id() != $orderGiftMessageId)){
                    $giftItemMessage = Mage::getModel("giftmessage/message")->load($item->getgift_message_id());
                    $comments .= $this->__('Gift message for sku : %s from %s to %s : %s',$item->getSku(), $giftItemMessage->getSender(), $giftItemMessage->getRecipient(), $giftItemMessage->getMessage())."\n";
                }
            }
        }

        return  $comments;

    }
    
}


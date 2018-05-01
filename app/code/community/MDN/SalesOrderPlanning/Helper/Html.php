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
class MDN_SalesOrderPlanning_Helper_Html extends Mage_Core_Helper_Abstract {

  /**
   * Display the planning in cart
   *
   * usage : copy this in the template : <?php echo Mage::helper('SalesOrderPlanning/Html')->getPlanningForCart(); ?>
   *
   * recommanded template to use : app/design/frontend/base/default/template/checkout/cart.phtml
   * for example, just before : <table id="shopping-cart-table" class="data-table cart-table">
   *  
   * 
   * @return String HTML
   */
    public function getPlanningForCart() {

      $html = '';
      
      $layout = Mage::getSingleton('core/layout');
      $blockPlanning = $layout->createBlock('SalesOrderPlanning/Planning_Cart');
      $blockPlanning->setTemplate('checkout/planning.phtml');
      $html = $blockPlanning->toHtml();
      
      return $html;
    }

   /**
   * Display the planning for an order
   *
   * usage : copy this in the template : <?php echo Mage::helper('SalesOrderPlanning/Html')->getPlanningForOrder($orderId); ?>
   *
   * recommanded template to use :  /app/design/frontend/base/default/template/sales/order/view.phtml
   * for example, just after :  <?php echo $this->getChildHtml('order_items') ?>
   *    
   *
   * @return String HTML
   */
    public function getPlanningForOrder($orderId) {

      $html = '';

      if($orderId > 0){
        $order  = mage::getModel('sales/order')->load($orderId);
        if($order->getId()){
          $layout = Mage::getSingleton('core/layout');
          $blockPlanning = $layout->createBlock('SalesOrderPlanning/Planning_Graph');
          $blockPlanning->setTemplate('sales/order/planning.phtml');
          $blockPlanning->setOrder($order);
          $html = $blockPlanning->toHtml();
        }
      }

      return $html;
    }

}
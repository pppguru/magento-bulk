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
 * @author     : Olivier ZIMMERMANN
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MDN_ProductReturn_Model_Observer
{

    /**
     * Cron handler to automatically change status for expired product return
     *
     */
    public function UpdateStatusRma()
    {
        $debug = '## UpdateStatusRma' . "\n";

        //load collection
        $collection = Mage::getModel('ProductReturn/Rma')
            ->getCollection()
            ->addFieldToFilter('rma_expire_date', array("lt" => Date('Y-m-d')))
            ->addFieldToFilter('rma_status', array("in" => array('product_return_accepted', 'requested')));

        //process productreturns
        foreach ($collection as $row) {
            try {

                $row->setrma_status(MDN_ProductReturn_Model_Rma::kStatusRmaExpired)->save();
                $debug .= 'Update Rma #' . $row->getId() . "\n";

            } catch (Exception $ex) {
                $debug .= 'Error : ' . $ex->getMessage() . "\n";
            }
        }

        mage::log($debug);

    }

    /**
     * Add rma tab in order preparation screen
     *
     * @param Varien_Event_Observer $observer
     */
    public function orderpreparartion_create_tabs(Varien_Event_Observer $observer)
    {
        //check if enabled
        if (mage::getStoreConfig('productreturn/general/add_tab_in_order_preparation') == 0)
            return;

        $tab = $observer->getEvent()->gettab();

        $block = $tab->getLayout()->createBlock('ProductReturn/OrderPreparation_Grid');

        $tab->addTab('rma', array(
            'label'   => Mage::helper('ProductReturn')->__('Product Return'),
            'content' => $block->setTemplate('ProductReturn/OrderPreparation/Grid.phtml')
                ->toHtml(),
            'active'  => true
        ));
    }

    /**
     * add shipment RMA to order preparation PDF
     *
     * @param Varien_Event_Observer $observer
     */
    public function orderpreparation_print_order_documents(Varien_Event_Observer $observer)
    {
        //retrieve data
        $pdf   = $observer->getEvent()->getpdf();
        $order = $observer->getEvent()->getorder();
        $rma   = mage::helper('ProductReturn')->getRmaFromGeneratedOrder($order);

        //add RMA document
        if ($rma) {
            $obj      = mage::getModel('ProductReturn/Pdf_Rma');
            $obj->pdf = $pdf;
            $pdf      = $obj->getPdf(array($rma));
        }

    }

} 
 
 
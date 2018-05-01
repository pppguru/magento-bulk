<?php



class MDN_Purchase_Model_Order_PoSearch extends Varien_Object
{
    /**
     * Load search results
     *
     * @return MDN_Purchase_Model_Order_PoSearch
     */
    public function load()
    {
        $arr = array();

        if (!$this->hasStart() || !$this->hasLimit() || !$this->hasQuery()) {
            $this->setResults($arr);
            return $this;
        }

        $collection = mage::getModel('Purchase/Order')->getCollection()
            ->join('Purchase/Supplier','po_sup_num=sup_id')
            ->addFieldToFilter('po_order_id', array('like'=> new Zend_Db_Expr('"%'.$this->getQuery().'%"')))
            ->setCurPage($this->getStart())
            ->setPageSize($this->getLimit())
            ->load();

        foreach ($collection as $po) {
            $arr[] = array(
                'id'            => 'po_num/'.$po->getId(),
                'name'          => $po->getpo_order_id(),
                'status'        => $po->getpo_status(),
                'supplier'   => $po->getsup_name(),
            );
        }

        $this->setResults($arr);

        return $this;
    }
}
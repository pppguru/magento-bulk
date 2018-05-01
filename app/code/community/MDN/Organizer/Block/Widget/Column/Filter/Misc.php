<?php

class MDN_Organizer_Block_Widget_Column_Filter_Misc
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Select
{
    protected function _getOptions()
    {
        $selectOptions = array();

        $selectOptions[] = array('label' => $this->__('Finished'), 'value' => '2');
        $selectOptions[] = array('label' => $this->__('Late'), 'value' => '1');
        $selectOptions[] = array('label' => $this->__('Todo'), 'value' => '0');

        return $selectOptions;
    }

    public function getCondition()
    {
        $searchString = $this->getValue();
        if ($searchString == '' || $searchString == 0)
            return;

        $organizersIds = array();

        switch ($searchString)
        {
            //finished
            case '2':
                $organizersIds = mage::getModel('Organizer/Task')
                    ->getCollection()
                    ->addFieldToFilter('ot_finished', 1)
                    ->getAllIds();
                break;

            //late
            case '1':
                $organizersIds = mage::getModel('Organizer/Task')
                    ->getCollection()
                    ->addFieldToFilter('ot_finished', 0)
                    ->addFieldToFilter('ot_deadline', array('lt' => date('Y-m-d')))
                    ->getAllIds();
                break;
        }

        return array('in' => $organizersIds);
    }
}
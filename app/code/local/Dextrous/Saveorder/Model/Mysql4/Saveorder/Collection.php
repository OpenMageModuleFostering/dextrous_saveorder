<?php
class Dextrous_Saveorder_Model_Mysql4_Saveorder_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('saveorder/saveorder');
    }
}
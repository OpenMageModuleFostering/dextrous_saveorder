<?php
class Dextrous_Saveorder_Model_Mysql4_Saveorder extends Mage_Core_Model_Mysql4_Abstract
{
    public function _construct()
    {    
        // Note that the saveorder_id refers to the key field in your database table.
        $this->_init('saveorder/saveorder', 'saveorder_id');
    }
}
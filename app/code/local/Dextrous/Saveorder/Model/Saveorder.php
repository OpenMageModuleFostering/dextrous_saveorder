<?php
class Dextrous_Saveorder_Model_Saveorder extends Mage_Core_Model_Abstract
{
    public function _construct()
    {
        parent::_construct();
        $this->_init('saveorder/saveorder');
    }
}
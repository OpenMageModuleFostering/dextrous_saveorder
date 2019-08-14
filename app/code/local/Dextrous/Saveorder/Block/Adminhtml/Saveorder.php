<?php
class Dextrous_Saveorder_Block_Adminhtml_Saveorder extends Mage_Adminhtml_Block_Widget_Grid_Container
{
	public function __construct()
	{
		$this->_controller = 'adminhtml_saveorder';
		$this->_blockGroup = 'saveorder';
		$this->_headerText = Mage::helper('saveorder')->__('Saved Order Manager');
		$this->_addButtonLabel = Mage::helper('saveorder')->__('Add Item');

		$this->_addButton('create_order_top_button', array(
				'label'     => Mage::helper('sales')->__('Create New Order'),
				'onclick'   => 'setLocation(\'' . $this->getcreateOrderUrl() . '\')',
			), 0, 100, 'header', 'header');
		parent::__construct();
		$this->_removeButton('add');
	}
  
	public function getcreateOrderUrl()
    {
        return $this->getUrl('adminhtml/sales_order_create/start');
    }
}
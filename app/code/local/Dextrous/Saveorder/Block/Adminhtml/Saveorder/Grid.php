<?php
class Dextrous_Saveorder_Block_Adminhtml_Saveorder_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  public function __construct()
  {
      parent::__construct();
      $this->setId('saveorderGrid');
      $this->setDefaultSort('saveorder_id');
      $this->setDefaultDir('ASC');
      $this->setSaveParametersInSession(true);
  }

  protected function _prepareCollection()
  {
      $collection = Mage::getModel('sales/quote')
					->getCollection()
					->addFieldToFilter('from_admin',1)
					->load();
      $this->setCollection($collection);
      return parent::_prepareCollection();
  }

  protected function _prepareColumns()
  {
		$this->addColumn('entity_id', array(
		  'header'    => Mage::helper('saveorder')->__('ID'),
		  'align'     =>'right',
		  'width'     => '50px',
		  'index'     => 'entity_id',
		));

		$this->addColumn('customer_firstname', array(
		  'header'    => Mage::helper('saveorder')->__('Customer Name'),
		  'align'     =>'left',
		  'index'     => 'customer_firstname',
		));
		
		$this->addColumn('customer_email', array(
		  'header'    => Mage::helper('saveorder')->__('Customer Email'),
		  'align'     =>'left',
		  'index'     => 'customer_email',
		));
		$this->addColumn('subtotal', array(
		  'header'    => Mage::helper('saveorder')->__('Order Subtotal'),
		  'align'     =>'left',
		  'index'     => 'subtotal',
		));
	  
        $this->addColumn('action',
            array(
                'header'    =>  Mage::helper('saveorder')->__('Action'),
                'width'     => '100',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('saveorder')->__('Edit'),
                        'url'       => array('base'=>  '*/*/edit'),
                        'field'     => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));
		
		$this->addExportType('*/*/exportCsv', Mage::helper('saveorder')->__('CSV'));
		$this->addExportType('*/*/exportXml', Mage::helper('saveorder')->__('XML'));
	  
      return parent::_prepareColumns();
  }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('saveorder_id');
        $this->getMassactionBlock()->setFormFieldName('saveorder');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('saveorder')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('saveorder')->__('Are you sure?')
        ));
		$this->getMassactionBlock()->addItem('Submit Order', array(
             'label'    => Mage::helper('saveorder')->__('Submit Order'),
             'url'      => $this->getUrl('*/*/massSubmit'),
             'confirm'  => Mage::helper('saveorder')->__('Are you sure?')
        ));
		return $this;
    }

  public function getRowUrl($row)
  {
	return $this->getUrl('*/*/edit', array('id' => $row->getId()));
  }

}
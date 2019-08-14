<?php
$installer = $this;

$installer->startSetup();

$installer->getConnection()
	->addColumn($this->getTable('sales_flat_quote'), 'from_admin', array(
		'nullable' => false,
		'length' => 5,
		'type' => Varien_Db_Ddl_Table::TYPE_SMALLINT,
		'comment'=> 'Created by Admin'
	)
);
$installer->endSetup();
<?php
require_once 'Mage/Adminhtml/controllers/Sales/Order/EditController.php';
class Dextrous_Saveorder_Sales_Order_EditController extends Mage_Adminhtml_Sales_Order_EditController
{
    public function startAction()
    {	
        $this->_getSession()->clear();
        $orderId = $this->getRequest()->getParam('order_id');
        $from 	 = $this->getRequest()->getParam('from');
        $order = Mage::getModel('sales/order')->load($orderId);
		
        try {
            if ($order->getId()) {
                $this->_getSession()->setUseOldShippingMethod(true);
                $this->_getOrderCreateModel()->initFromOrder($order);
				if($from)
				{
					Mage::getModel('sales/order')->load($orderId)->delete();
				}
                $this->_redirect('*/*');
            }
            else {
                $this->_redirect('*/sales_order/');
            }
        } catch (Mage_Core_Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            $this->_redirect('*/sales_order/view', array('order_id' => $orderId));
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addException($e, $e->getMessage());
            $this->_redirect('*/sales_order/view', array('order_id' => $orderId));
        }
    }
}

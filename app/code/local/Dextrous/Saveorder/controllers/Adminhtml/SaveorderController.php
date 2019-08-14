<?php
class Dextrous_Saveorder_Adminhtml_SaveorderController extends Mage_Adminhtml_Controller_action
{
	protected function _getSession()
    {
        return Mage::getSingleton('adminhtml/session_quote');
    }

	protected function _initAction() {
		$this->loadLayout()
			->_setActiveMenu('saveorder/items')
			->_addBreadcrumb(Mage::helper('adminhtml')->__('Items Manager'), Mage::helper('adminhtml')->__('Item Manager'));
		
		return $this;
	}   
 
	public function indexAction() {
		$this->_initAction()
			->renderLayout();
	}

	public function editAction() {
		$quoteId     = $this->getRequest()->getParam('id');
		if($quoteId){
			try {
                $orderId	=	$this->convertQuoteToOrder($quoteId);
				$this->_redirect('adminhtml/sales_order_edit/start',array('order_id' => $orderId,'from'=>'saveorder'));
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
		} else {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('saveorder')->__('Item does not exist'));
			$this->_redirect('*/*/');
		}
	}

	public function massSubmitAction() {
        $quoteIds = $this->getRequest()->getParam('saveorder');
        if(!is_array($quoteIds)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            try {
				$orderIds	=	array();
                foreach ($quoteIds as $quoteId) 
				{
					$orderIds[]	=	$this->convertQuoteToOrder($quoteId);
					
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully converted to order', count($orderIds)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
	
	
    public function massDeleteAction() {
        $quoteIds = $this->getRequest()->getParam('saveorder');
        if(!is_array($quoteIds)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Please select item(s)'));
        } else {
            try {
                foreach ($quoteIds as $quoteId) {
					$collection 	= Mage::getModel('sales/quote')
						->getCollection()
						->addFieldToFilter('entity_id',$quoteId)
						->addFieldToFilter('from_admin',1)
						->load();
						
					if(count($collection) > 0)
					{
						foreach($collection as $quoteObj)
						{	
							$quoteObj->setData('from_admin',0);
							$quoteObj->save();
						}
					}	
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d record(s) were successfully deleted', count($quoteIds)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
  
    public function exportCsvAction()
    {
        $fileName   = 'saveorder.csv';
        $content    = $this->getLayout()->createBlock('saveorder/adminhtml_saveorder_grid')
            ->getCsv();

        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportXmlAction()
    {
        $fileName   = 'saveorder.xml';
        $content    = $this->getLayout()->createBlock('saveorder/adminhtml_saveorder_grid')
            ->getXml();

        $this->_sendUploadResponse($fileName, $content);
    }

    protected function _sendUploadResponse($fileName, $content, $contentType='application/octet-stream')
    {
        $response = $this->getResponse();
        $response->setHeader('HTTP/1.1 200 OK','');
        $response->setHeader('Pragma', 'public', true);
        $response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
        $response->setHeader('Content-Disposition', 'attachment; filename='.$fileName);
        $response->setHeader('Last-Modified', date('r'));
        $response->setHeader('Accept-Ranges', 'bytes');
        $response->setHeader('Content-Length', strlen($content));
        $response->setHeader('Content-type', $contentType);
        $response->setBody($content);
        $response->sendResponse();
        die;
    }
	
	public function convertQuoteToOrder($quoteId)
	{	
		$collection 	= Mage::getModel('sales/quote')
						->getCollection()
						->addFieldToFilter('entity_id',$quoteId)
						->addFieldToFilter('from_admin',1)
						->load();
						
		if(count($collection) > 0)
		{
			try {
                foreach($collection as $quoteObj)
				{	
					$items		=	$quoteObj->getAllItems();
					$quoteObj->collectTotals();
					$quoteObj->reserveOrderId();
					$quotePaymentObj	=	$quoteObj->getPayment();
					
					$quoteObj->setPayment($quotePaymentObj);
					$convertQuoteObj	=	Mage::getSingleton('sales/convert_quote');
					$orderObj 			= 	$convertQuoteObj->addressToOrder($quoteObj->getShippingAddress());
					$orderPaymentObj	=	$convertQuoteObj->paymentToOrderPayment($quotePaymentObj);
					
					$orderObj->setBillingAddress($convertQuoteObj->addressToOrderAddress($quoteObj->getBillingAddress()));
					$orderObj->setShippingAddress($convertQuoteObj->addressToOrderAddress($quoteObj->getShippingAddress()));
					$orderObj->setPayment($convertQuoteObj->paymentToOrderPayment($quoteObj->getPayment()));
					
					foreach ($items as $item) {
						//@var $item Mage_Sales_Model_Quote_Item
						$orderItem = $convertQuoteObj->itemToOrderItem($item);
						if ($item->getParentItem()) {
							$orderItem->setParentItem($orderObj->getItemByQuoteItemId($item->getParentItem()->getId()));
						}
						$orderObj->addItem($orderItem);
					}
					
					$orderObj->setCanShipPartiallyItem(false);
					
					$totalDue	=	$orderObj->getTotalDue();
					$orderObj->place(); 
					$orderObj->save(); 
					$orderId	=	$orderObj->getId();
					//$orderObj->sendNewOrderEmail();
					if($orderId){
						$quoteObj->setData('from_admin',0);
						$quoteObj->save();
					}
					return $orderId;
				}
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
			
		}
		return false;
	}
	
	public function confirmAction()
	{
		$adminQuote	=	Mage::getSingleton('adminhtml/session_quote')->getQuote(); 
		$result		=	array();
		if($adminQuote->getId())
		{	
			try {
				$payment		=	$adminQuote->getPayment();
				$paymentMethod	=	$payment->getMethod();
				if(!$paymentMethod || $paymentMethod == null){
					Mage::getSingleton('adminhtml/session')->addError($this->__('payment method is not valid.'));
					$this->_redirect('*/*/index');
				}
				else{
					$adminQuote->setData('from_admin',1);
					$adminQuote->save();
					Mage::getSingleton('adminhtml/session')->addSuccess($this->__('The order has been saved.'));
					$this->_redirect('*/*/index');
				}
			}catch (Mage_Core_Exception $e){
				$message = $e->getMessage();
				if( !empty($message) ) {
					Mage::getSingleton('adminhtml/session')->addError($message);
				}
				$this->_redirect('*/*/index');
			}
			catch (Exception $e){
				Mage::getSingleton('adminhtml/session')->addException($e, $this->__('Order saving error: %s', $e->getMessage()));
				$this->_redirect('*/*/index');
			}
		}
		else
		{
			$this->_redirect('adminhtml/sales_order/index');
		}
	}
}
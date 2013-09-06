<?php
 /**
 * GoMage.com
 *
 * GoMage LightCheckout Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2010 GoMage.com (http://www.gomage.com)
 * @author       GoMage.com
 * @license      http://www.gomage.com/licensing  Single domain license
 * @terms of use http://www.gomage.com/terms-of-use
 * @version      Release: 1.0
 * @since        Class available since Release 1.0
 */

class GoMage_Checkout_OnepageController extends Mage_Checkout_Controller_Action
{
	
	public function getOnepage(){
		if (empty($this->_onepage)) {
            $this->_onepage = Mage::getSingleton('checkout/type_onepage');
        }
		return $this->_onepage;
	}
	public function getSession(){
		if (empty($this->_session)) {
            $this->_session = Mage::getSingleton('customer/session');
        }
		return $this->_session;
	}
	public function getCheckout()
    {
        if (empty($this->_checkout)) {
            $this->_checkout = Mage::getSingleton('checkout/session');
        }
        return $this->_checkout;
    }
    
    public function indexAction(){
    	
    	$helper = Mage::helper('gomage_checkout');
    	
    	if((bool)$helper->getConfigData('general/enabled') == false){
    		return $this->_redirect('checkout/onepage');
    	}
        $quote = $this->getOnepage()->getQuote();
        
        if (!$quote->hasItems() || $quote->getHasError()) {
            $this->_redirect('checkout/cart');
            return;
        }
        if (!$quote->validateMinimumAmount()) {
            $error = Mage::getStoreConfig('sales/minimum_order/error_message');
            Mage::getSingleton('checkout/session')->addError($error);
            $this->_redirect('checkout/cart');
            return;
        }
        
        Mage::getSingleton('checkout/session')->setCartWasUpdated(false);
        Mage::getSingleton('customer/session')->setBeforeAuthUrl(Mage::getUrl('*/*/*', array('_secure'=>true)));
        $this->getOnepage()->initCheckout();
        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        
        $title = $helper->getConfigData('general/title');
        
        $this->getLayout()->getBlock('head')->setTitle($title ? $title : $this->__('Checkout'));
        $this->renderLayout();
    }
	public function ajaxAction(){
		$action = $this->getRequest()->getParam('action');
		
		$result = new stdClass();
		$result->error = false;
		
		switch($action):
			
			case('get_methods'):
				
				if($billing_address_data = $this->getRequest()->getPost('billing')){
					
					$address = $this->getOnepage()->getQuote()->getBillingAddress();
					
					$address->addData($billing_address_data);
					$address->implodeStreetAddress();
					
					if (!$this->getOnepage()->getQuote()->isVirtual()) {
						
						if(!isset($billing_address_data['use_for_shipping']) || !intval($billing_address_data['use_for_shipping'])){
							
							$shipping_address_data = $this->getRequest()->getPost('shipping');
							
						}else{
							
							$shipping_address_data = $billing_address_data;
							
						}
						
						$address = $this->getOnepage()->getQuote()->getShippingAddress();
						$address->addData($shipping_address_data);
						$address->implodeStreetAddress();
	        			$address->setCollectShippingRates(true);
	        			$address->collectShippingRates();
	        			
	        			$layout = $this->_getShippingMethodsHtml();
	        			
						$result->rates		= $layout->getOutput();
						
						$rates = (array)$layout->getBlock('root')->getShippingRates();
						
						if(count($rates) == 1){
							
							foreach($rates as $rate_code=>$methods){
								
								if(count($methods) == 1){
									foreach($methods as $method){
										
										$address->setShippingMethod($method->getCode());
									
									}
								
								}
								
								break;
							}
							
						}
						
					}
					
					$this->getOnepage()->getQuote()->collectTotals()->save();
					
					$result->section = 'methods';
					
					
					$result->payments	= $this->_getPaymentMethodsHtml();
					$result->totals 	= $this->_getReviewHtml();
					
				}
			break;
			case('get_payment_methods'):
				$billing_address_data = $this->getRequest()->getPost('billing');
				$address = $this->getOnepage()->getQuote()->getBillingAddress();
				$address->addData($billing_address_data);
				$address->implodeStreetAddress();
				
				$this->getOnepage()->getQuote()->collectTotals()->save();
				
				$result->section	= 'payment_methods';
				$result->payments	= $this->_getPaymentMethodsHtml();
				$result->totals = $this->_getReviewHtml();
			break;
			case('get_shipping_methods'):
				if (!$this->getOnepage()->getQuote()->isVirtual()) {
					
					$billing_address_data = $this->getRequest()->getPost('billing');
					
					if(!isset($billing_address_data['use_for_shipping']) || !intval($billing_address_data['use_for_shipping'])){
						
						$shipping_address_data = $this->getRequest()->getPost('shipping');
						
					}else{
						
						$shipping_address_data = $billing_address_data;
						
					}
					
					$address = $this->getOnepage()->getQuote()->getShippingAddress();
					$address->addData($shipping_address_data);
					$address->implodeStreetAddress();
        			$address->setCollectShippingRates(true);
        			$address->collectShippingRates();
        			
					$layout = $this->_getShippingMethodsHtml();
        			
					$result->rates		= $layout->getOutput();
					
					$rates = (array)$layout->getBlock('root')->getShippingRates();
					
					
					if(count($rates) == 1){
						
						foreach($rates as $rate_code=>$methods){
							
							if(count($methods) == 1){
								foreach($methods as $method){
									$address->setShippingMethod($method->getCode());
								}
							}
							
							break;
						}
						
					}
					$this->getOnepage()->getQuote()->collectTotals()->save();
					
					$result->section	= 'shiping_rates';
					$result->totals = $this->_getReviewHtml();
					
				}
			break;
			case('get_totals'):
				
				if($shippingMethod = $this->getRequest()->getPost('shipping_method', false)){
        			
        			$this->getOnepage()->getQuote()->getShippingAddress()->setShippingMethod($shippingMethod);
        			
        		}
        		
        		if (($payment = $this->getRequest()->getPost('payment', false)) && is_array($payment) && isset($payment['method']) && $payment['method']) {
        			try{
        				
                		$this->getOnepage()->getQuote()->getPayment()->importData($payment);
                		
                		
                	}catch(Exception $e){
                		
                		//continue
                		
                	}
            	}
				
				$this->getOnepage()->getQuote()->collectTotals();
				$result->section = 'totals';
				$result->totals = $this->_getReviewHtml();
				
				$this->getOnepage()->getQuote()->save();
			break;
			
		endswitch;
		
		$this->getResponse()->setBody(json_encode($result));
	}
	public function placeAction(){
		try{
			
			$this->getOnepage()->getQuote()->getShippingAddress()->setShippingMethod($this->getSession()->getTempShippingData());
			$this->getOnepage()->getQuote()->getPayment()->importData($this->getSession()->getTempPymentData());
			$this->getOnepage()->getQuote()->collectTotals();
			
			$this->getSession()->setTempShippingData(null);
			$this->getSession()->setTempPymentData(null);
			
			$this->getOnepage()->saveOrder();
			$this->getOnepage()->getQuote()->save();
			$this->getCheckout()->setCustomerAssignedQuote(false);
			$this->getCheckout()->setCustomerAdressLoaded(false);
			
			$redirect = $this->getOnepage()->getCheckout()->getRedirectUrl();
				
			if($redirect){
			
				$this->_redirectUrl($redirect);
			
			}else{
				
				$this->_redirect('checkout/onepage/success');
				
			}
			
			
			
		}catch(Mage_Core_Exception $e){
			Mage::logException($e);
			$this->getSession()->addError($e->getMessage());
			$this->_redirect('checkout/onepage');
		}catch(Mage_Eav_Model_Entity_Attribute_Exception $e){
			
			Mage::logException($e);
			$this->getSession()->addError($e->getMessage());
			$this->_redirect('checkout/onepage');
			
		}catch(Exception $e) {
			Mage::logException($e);
			$this->getSession()->addError($this->__('There was an error processing your order. Please contact us or try again later.'));
			$this->_redirect('checkout/onepage');
			
		}
	}
	
	public function saveAction(){
		
		$helper = Mage::helper('gomage_checkout');
			
		if((bool)$helper->getConfigData('general/enabled') == false){
			return $this->_redirect('checkout/onepage');
		}
		
		if ($this->getRequest()->isPost() && $post = $this->getRequest()->getPost()) {
			
        	try {
        		
        		$result = array('error'=>0, 'message'=>array());
        		
        		$billing_address_data = $this->getRequest()->getPost('billing');
        		
        		if(isset($billing_address_data['day']) && $billing_address_data['month'] && $billing_address_data['year']){
        		try{
        		$dob = sprintf("%02d/%02d/%04d", substr($billing_address_data['day'], 0, 2), substr($billing_address_data['month'], 0, 2), substr($billing_address_data['year'], 0, 4));
       			
        		$dob = Mage::app()->getLocale()->date($dob, null, null, false)->toString('yyyy-MM-dd');
        			
        		$this->getOnepage()->getQuote()->setCustomerDob($dob);}catch(Exception $e){
        			
        			$result['error'] = true;
					$result['message'][] = $this->__('Incorrect date of birdhday');
        			
        		}
        		}
        		
        		if(isset($billing_address_data['taxvat'])){
        		$this->getOnepage()->getQuote()->setCustomerTaxvat($billing_address_data['taxvat']);
        		
        		$billing_address_data['customer_taxvat'] = $billing_address_data['taxvat'];
        		}
        		if((bool)($this->getSession()->getCustomer()->getId()) == false && (intval(Mage::helper('gomage_checkout')->getCheckoutMode()) === 1 || $this->getRequest()->getParam('create_account'))){
        			
        			$this->getOnepage()->getQuote()->setCheckoutMethod('register');
        			$this->getOnepage()->getQuote()->setPasswordHash($this->getSession()->getCustomer()->encryptPassword($billing_address_data['password']));
        			
        			
        			
        			
        			
		        	
		        	$this->getOnepage()->getQuote()->getBillingAddress()->setSaveInAddressBook(true);
					$this->getOnepage()->getQuote()->getShippingAddress()->setSaveInAddressBook(true);
					
		            $this->getSession()->setCreateAccount(true);
	            }else{
	            	
	            	
	            	if((bool)($this->getSession()->getCustomer()->getId()) == false && !$this->getOnepage()->getQuote()->hasVirtual()){
	            		$this->getOnepage()->getQuote()->setCheckoutMethod('guest');
        			}else{
        				$this->getOnepage()->getQuote()->setCheckoutMethod(null);
        			}
	            	
	            	$this->getSession()->setCreateAccount(false);
	            }
	            
	            if(!isset($billing_address_data['use_for_shipping']) || !intval($billing_address_data['use_for_shipping'])){
        			$this->getCheckout()->setShippingAsBilling(false);
        		}else{
        			$this->getCheckout()->setShippingAsBilling(true);
        		}
        		
				$billing_address_result = $this->getOnepage()->saveBilling($billing_address_data, false);
				
				if (!$this->getOnepage()->getQuote()->isVirtual()) {
					
					if(!isset($billing_address_data['use_for_shipping']) || !intval($billing_address_data['use_for_shipping'])){
						
						$shipping_address_data = $post = $this->getRequest()->getPost('shipping');
						$shiping_address_result = $this->getOnepage()->saveShipping($shipping_address_data, false);
						
						if(isset($shiping_address_result['error']) && intval($shiping_address_result['error'])){
					
							$result['error'] = true;
							$messages = array();
							
							foreach((array) $shiping_address_result['message'] as $message){
								$messages[] = $this->__('Shipping Address Error').': '.$message;
							}
							$result['message'] = array_merge($result['message'], $messages);
						}
						
					}
					
				}
				
				if(isset($billing_address_result['error']) && intval($billing_address_result['error'])){
					
					$result['error'] = true;
					$messages = array();
					
					foreach((array)$billing_address_result['message'] as $message){
						$messages[] = $this->__('Billing Address Error').': '.$message;
					}
					$result['message'] = array_merge($result['message'], $messages);
				}
				
				
				
				if($shippingMethod = $this->getRequest()->getPost('shipping_method', false)){
					
					$this->getSession()->setTempShippingData($shippingMethod);
					
            	}
            	
				if ($payment_data = $this->getRequest()->getPost('payment', false)) {
					
					$this->getOnepage()->getQuote()->getPayment()->importData($payment_data);
					
					$this->getSession()->setTempPymentData($payment_data);
					
            	}
            	
        		if($customer_comment = $this->getRequest()->getParam('customer_comment')){
            		$this->getOnepage()->getQuote()->setData('gomage_checkout_customer_comment', nl2br(strip_tags($customer_comment)));
            	}
            	
            	if($helper->getConfigData('general/termsandconditions') && !intval($this->getRequest()->getPost('accept_terms', 0))){
            		
            		$result['error'] = true;
            		$result['message'][] = $this->__('Your must accept Terms and Conditions.');
            		
            	}
        		
        		if(isset($result['error']) && intval($result['error'])){
					throw new Mage_Core_Exception(implode('<br/>', (array)$result['message']));
				}
				
				
				if(intval($this->getRequest()->getParam('subscribe')) > 0){
					
					if($this->getSession()->isLoggedIn()){
						
						Mage::getModel('newsletter/subscriber')->subscribe($this->getSession()->getCustomer()->getEmail());
						
					}else{
					
						Mage::getModel('newsletter/subscriber')->subscribe($this->getOnepage()->getQuote()->getBillingAddress()->getEmail());
					
					}
				
				}
				
        		Mage::dispatchEvent('gomage_checkout_save_quote_before', array('request'=>$this->getRequest(), 'quote' => $this->getOnepage()->getQuote()));
        		
        		
				$this->getOnepage()->getQuote()->save();
				
            	Mage::dispatchEvent('gomage_checkout_save_quote_after', array('request'=>$this->getRequest(), 'quote' => $this->getOnepage()->getQuote()));
        		
				
		        $this->_redirect('gomage_checkout/onepage/place');
            	
        	}catch(Mage_Core_Exception $e) {
        		Mage::logException($e);
            	Mage::helper('checkout')->sendPaymentFailedEmail($this->getOnepage()->getQuote(), $e->getMessage());
            	$this->getOnepage()->getQuote()->save();
            	$this->getSession()->addError($e->getMessage());
            	$this->_redirect('checkout/onepage');
            	
        	}catch(Exception $e) {
        		echo $e;
        		die();
        		Mage::logException($e);
            	Mage::helper('checkout')->sendPaymentFailedEmail($this->getOnepage()->getQuote(), $e->getMessage());
        		$this->getOnepage()->getQuote()->save();
        		$this->getSession()->addError($this->__('There was an error processing your order. Please contact us or try again later.'));
        		$this->_redirect('checkout/onepage');
        		
        	}
			
		}else{
			$this->_redirect('checkout/onepage');
		}
		
	}
	
	
	public function customerLoginAction(){
		
		$result = array('error'=>false);	
		
		$login = $this->getRequest()->getPost('login');
        if (!empty($login['username']) && !empty($login['password'])) {
        	
        	$session = Mage::getSingleton('customer/session');
        	
            try {
                $session->login($login['username'], $login['password']);
            } catch (Mage_Core_Exception $e) {
                switch ($e->getCode()) {
                    case Mage_Customer_Model_Customer::EXCEPTION_EMAIL_NOT_CONFIRMED:
                        $message = Mage::helper('customer')->__('This account is not confirmed. <a href="%s">Click here</a> to resend confirmation email.', Mage::helper('customer')->getEmailConfirmationUrl($login['username']));
                        break;
                    case Mage_Customer_Model_Customer::EXCEPTION_INVALID_EMAIL_OR_PASSWORD:
                        $message = $e->getMessage();
                        break;
                    default:
                        $message = $e->getMessage();
                }
                $result['error'] = true;
                $result['message'] = $message;
            } catch (Exception $e) {
                $result['error'] = true;
                $result['message'] = (string)$e->getMessage();
            }
        } else {
        	$result['error'] = true;
            $result['message'] = $this->__('Login and password are required');
        }
		
		
        echo json_encode($result);
		
	}
	
	protected function _getShippingMethodsHtml()
    {
        $layout = Mage::getModel('core/layout');
        $layout->getUpdate()->load('checkout_onepage_shippingmethod');
        $layout->generateXml()->generateBlocks();
        
        return $layout;
    }
    
    protected function _getPaymentMethodsHtml()
    {
        $layout = Mage::getModel('core/layout');
        $layout->getUpdate()->load('checkout_onepage_paymentmethod');
        $layout->generateXml()->generateBlocks();
        return $layout->getOutput();
    }

    protected function _getAdditionalHtml()
    {
        $layout = Mage::getModel('core/layout');
        $layout->getUpdate()->load('checkout_onepage_additional');
        $layout->generateXml()->generateBlocks();
        return $layout->getOutput();
    }
    
    protected function _getReviewHtml()
    {
        $layout = Mage::getModel('core/layout');
        $layout->getUpdate()->load('checkout_onepage_review');
        $layout->generateXml()->generateBlocks();
        return $layout->getOutput();
    }
}

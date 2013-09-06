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
 * @version      Release: 2.2
 * @since        Class available since Release 1.0
 */

class GoMage_Checkout_OnepageController extends Mage_Checkout_Controller_Action
{
	
	public function getOnepage(){
		if (empty($this->_onepage)) {
            $this->_onepage = Mage::getSingleton('gomage_checkout/type_onestep');
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
        $this->_initLayoutMessages('checkout/session');
        
        $title = $helper->getConfigData('general/title');
        
        $this->getLayout()->getBlock('head')->setTitle($title ? $title : $this->__('Checkout'));
        $this->renderLayout();
    }
	public function ajaxAction(){
		$action = $this->getRequest()->getParam('action');
		
		$result = new stdClass();
		$result->error = false;
		try{
		switch($action):
			
			case('discount'):
				
				$couponCode = $this->getRequest()->getParam('coupon_code');
				
				try{
				    $ugiftcert_cert = Mage::getModel('ugiftcert/cert');				
				}catch (Exception $e) {
                    $ugiftcert_cert = false; 
				}    
				
				if ($this->getRequest()->getParam('remove')>0) {
					$couponCode = '';
				}elseif (!strlen($couponCode)) {
					$result->error	= true;
					$result->message = $this->__('Coupon code "%s" is not valid.', Mage::helper('core')->htmlEscape($couponCode));
				}
								
				if(!$result->error){
				
				try {
            
					$this->getOnepage()->getQuote()->getShippingAddress()->setCollectShippingRates(true);
	            	$this->getOnepage()->getQuote()->setCouponCode($couponCode)->collectTotals()->save();
	            	
					if ($couponCode) {
	                	if ($couponCode == $this->getOnepage()->getQuote()->getCouponCode()) {
							$result->message = $this->__('Coupon code "%s" was applied successfully.', Mage::helper('core')->htmlEscape($couponCode));
				    	} else {
							$result->error	= true;
							$result->message = $this->__('Coupon code "%s" is not valid.', Mage::helper('core')->htmlEscape($couponCode));
				    	}
			    
	            	} else {
						$result->message = $this->__('Coupon code was canceled successfully.');
			    	}
			    	
			    	if ($result->error)
			    	{			    	    
			    	    if ($ugiftcert_cert)
			    	    {
			    	        $ugiftcert_cert->load($couponCode, 'cert_number');
    			    	    if ($ugiftcert_cert->getId() && $ugiftcert_cert->getStatus()=='A' && $ugiftcert_cert->getBalance()>0) 
    			    	    {      			    	        
    			    	        $this->getOnepage()->getQuote()->setTotalsCollectedFlag(false);                              
                                $ugiftcert_cert->addToQuote($this->getOnepage()->getQuote());
                                $this->getOnepage()->getQuote()->collectTotals()->save();

                                $result->error	= false;
                                $result->message = $this->__('Coupon code "%s" was applied successfully.', Mage::helper('core')->htmlEscape($couponCode));
                            }			    	       
			    	    }
			    	}
			    	
			    	$address = $this->getOnepage()->getQuote()->getShippingAddress();
				    $layout = $this->_getShippingMethodsHtml();	        			
    				$result->rates	= $layout->getOutput();    				
    				$rates = (array)$layout->getBlock('root')->getShippingRates();    																
    				if(count($rates) == 1){    					
    					foreach($rates as $rate_code=>$methods){    						
    						if(count($methods) == 1){
    							foreach($methods as $method){									    																			    
    								$address->setShippingMethod($method->getCode());
    								$this->getOnepage()->getQuote()->setTotalsCollectedFlag(false);
    								$this->getOnepage()->getQuote()->collectTotals()->save();																												
    							}    						
    						}    						
    						break;
    					}    					
    				}
			    
			    	$result->section = 'totals';
			    	$result->totals = $this->_getReviewHtml();
			    
		        }catch (Exception $e) {
					$result->error	= true;
				    $result->message = $e->getMessage();
				}
				}
			break;
			
			case('cartplus'):
				
				if($itemId = $this->getRequest()->getParam('id')){
					
					$quote = $this->getOnepage()->getQuote();
					$item = $quote->getItemById($itemId);
					
					$shippingMethod = $this->getOnepage()->getQuote()->getShippingAddress()->getShippingMethod();
					$paymentMethod = $this->getOnepage()->getQuote()->getPayment()->getMethod();
					
					$qty = intval($item->getQty()+1);
					
					$maximumQty = intval(Mage::getModel('catalog/product')->load($item->getProductId())->getStockItem()->getMaxSaleQty());
					if($qty > $maximumQty){
		            	
		            	throw new Mage_Core_Exception($this->__('Maximum Allowed Qty: %s', $maximumQty));
		            	
		            }
		            		            		            
		            if ($item->getHasChildren())
		            {
		                foreach ($item->getChildren() as $child) {
		                    $_product_id = $child->getProductId();
		                    $maximumQty = Mage::getModel('catalog/product')->load($_product_id)->getStockItem()->getQty();
        				    if($qty > $maximumQty){
        		            	
        		            	throw new Mage_Core_Exception($this->__('Maximum Allowed Qty: %s', round($maximumQty)));
        		            	
        		            }           
		                }
		            }
		            else
		            {
		                $_product_id = $item->getProductId();
    		            $maximumQty = Mage::getModel('catalog/product')->load($_product_id)->getStockItem()->getQty();
    				    if($qty > $maximumQty){
    		            	
    		            	throw new Mage_Core_Exception($this->__('Maximum Allowed Qty: %s', round($maximumQty)));
    		            	
    		            }   
		            }
		             					
					$item->setQty($qty);
					$address = $this->getOnepage()->getQuote()->getShippingAddress();
        			
        			$address->setCollectShippingRates(true);
        			$address->collectShippingRates();
        			$address->setCollectShippingRates(true);
				    if($shippingMethod){                			
                		$this->getOnepage()->getQuote()->getShippingAddress()->setShippingMethod($shippingMethod);                			
                	}   
                	try {
                    	if ($paymentMethod){        			
                    	    $this->getOnepage()->getQuote()->getPayment()->importData(array('method' =>	$paymentMethod));
                    	}
                	}
                	catch (Exception $_e)
                	{
                	}	
                	
        			$quote->collectTotals();
        			
        			
					$quote->save();
														
					$result->section = 'methods';
					$result->totals = $this->_getReviewHtml();
					
        			
        			if(!$this->getOnepage()->getQuote()->isVirtual()){
        				$layout = $this->_getShippingMethodsHtml();
						$result->rates		= $layout->getOutput();
					}
					
					$result->payments	= $this->_getPaymentMethodsHtml();
				}
				
			break;
			
			case('cartminus'):
				
				if($itemId = $this->getRequest()->getParam('id')){
					
					$quote = $this->getOnepage()->getQuote();
					$item = $quote->getItemById($itemId);
					
					$shippingMethod = $this->getOnepage()->getQuote()->getShippingAddress()->getShippingMethod();
					$paymentMethod = $this->getOnepage()->getQuote()->getPayment()->getMethod();
					
					$qty = intval($item->getQty()-1);
					
					$minimumQty = intval(Mage::getModel('catalog/product')->load($item->getProductId())->getStockItem()->getMinSaleQty());
					
		            if($qty < $minimumQty){
		            	
		            	throw new Mage_Core_Exception($this->__('Minimal Allowed Qty: %s', $minimumQty));
		            	
		            }
					
					
					if($qty > 0){
					
						$item->setQty($qty);
						$address = $this->getOnepage()->getQuote()->getShippingAddress();
					
					}else{
						
						$quote->removeItem($itemId);
						$address = $this->getOnepage()->getQuote()->getShippingAddress();
						
					}
					
					
					$result->section = 'methods';
					
					
        			
					
					$address->setCollectShippingRates(true);
        			$address->collectShippingRates();
        			$address->setCollectShippingRates(true);
				    if($shippingMethod){                			
                		$this->getOnepage()->getQuote()->getShippingAddress()->setShippingMethod($shippingMethod);                			
                	}
				    try {
                    	if ($paymentMethod){        			
                    	    $this->getOnepage()->getQuote()->getPayment()->importData(array('method' =>	$paymentMethod));
                    	}
                	}
                	catch (Exception $_e)
                	{
                	}
        			$quote->collectTotals();
        			$result->totals = $this->_getReviewHtml();
        			$quote->save();
        			
        			if(!$this->getOnepage()->getQuote()->isVirtual()){
        				$layout = $this->_getShippingMethodsHtml();
						$result->rates		= $layout->getOutput();
					}
					
					$result->payments	= $this->_getPaymentMethodsHtml();
				}
				
			break;
			
			case('cartremove'):
				
				if($itemId = $this->getRequest()->getParam('id')){
					
					
					$quote = $this->getOnepage()->getQuote();
					
					$shippingMethod = $this->getOnepage()->getQuote()->getShippingAddress()->getShippingMethod();
					$paymentMethod = $this->getOnepage()->getQuote()->getPayment()->getMethod();
					
					$quote->removeItem($itemId);
					$address = $this->getOnepage()->getQuote()->getShippingAddress();
					
					$address->setCollectShippingRates(true);
        			$address->collectShippingRates();
        			$address->setCollectShippingRates(true);
				    if($shippingMethod){                			
                		$this->getOnepage()->getQuote()->getShippingAddress()->setShippingMethod($shippingMethod);                			
                	}
				    try {
                    	if ($paymentMethod){        			
                    	    $this->getOnepage()->getQuote()->getPayment()->importData(array('method' =>	$paymentMethod));
                    	}
                	}
                	catch (Exception $_e)
                	{
                	}
        			$quote->collectTotals();
        			
					$quote->save();
					
					if (!$quote->hasItems()) {
						
			            $result->url = Mage::app()->getStore()->getUrl('checkout/cart');
			            
			            
			        }
					$result->section = 'methods';
					
					if(!$this->getOnepage()->getQuote()->isVirtual()){
						$layout = $this->_getShippingMethodsHtml();
						$result->rates		= $layout->getOutput();
					}
					
					
					$result->totals = $this->_getReviewHtml();
				}
				
			break;
			
			
			case('varify_taxvat'):
			case('get_methods'):
				
				if($billing_address_data = $this->getRequest()->getPost('billing')){
					
				    $paymentMethod = $this->getOnepage()->getQuote()->getPayment()->getMethod();
				    
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
	        			
	        			$address->setCollectShippingRates(true);  
        				if($shippingMethod = $this->getRequest()->getPost('shipping_method', false)){
                			
                			$this->getOnepage()->getQuote()->getShippingAddress()->setShippingMethod($shippingMethod);
                			
                		}   
    					try {
                        	if ($paymentMethod){        			
                        	    $this->getOnepage()->getQuote()->getPayment()->importData(array('method' =>	$paymentMethod));
                        	}
                    	}
                    	catch (Exception $_e)
                    	{
                    	}
                    	
    					if(Mage::helper('gomage_checkout')->getConfigData('vat/enabled')){    						
    						$this->getOnepage()->verifyCustomerVat();
    					}
                    	
        			    $this->getOnepage()->getQuote()->collectTotals()->save();
	        			
	        			$layout = $this->_getShippingMethodsHtml();
	        			
						$result->rates		= $layout->getOutput();
						
						$rates = (array)$layout->getBlock('root')->getShippingRates();
																		
						if(count($rates) == 1){
							
							foreach($rates as $rate_code=>$methods){
								
								if(count($methods) == 1){
									foreach($methods as $method){									    																			    
										$address->setShippingMethod($method->getCode());
										$this->getOnepage()->getQuote()->setTotalsCollectedFlag(false);																												
									}
								
								}
								
								break;
							}
							
						}
						
					}
					
					
					
					if(Mage::helper('gomage_checkout')->getConfigData('vat/enabled')){
						
						$result->verify_result = $this->getOnepage()->verifyCustomerVat();
						
						$this->getOnepage()->getQuote()->collectTotals()->save();
						
						$result->section	= 'varify_taxvat';
						$result->payments	= $this->_getPaymentMethodsHtml();
						$result->totals 	= $this->_getReviewHtml();
						
						
					
					}else{
						
						$this->getOnepage()->getQuote()->collectTotals()->save();
						
						$result->section	= 'methods';
						$result->payments	= $this->_getPaymentMethodsHtml();
						$result->totals 	= $this->_getReviewHtml();
						
					}
					
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
					
				    $paymentMethod = $this->getOnepage()->getQuote()->getPayment()->getMethod();
				    
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
        			
        			$address->setCollectShippingRates(true); 
				    if($shippingMethod = $this->getRequest()->getPost('shipping_method', false)){
                			
                			$this->getOnepage()->getQuote()->getShippingAddress()->setShippingMethod($shippingMethod);
                			
                	}     
				    try {
                    	if ($paymentMethod){        			
                    	    $this->getOnepage()->getQuote()->getPayment()->importData(array('method' =>	$paymentMethod));
                    	}
                	}
                	catch (Exception $_e)
                	{
                	}
        			$this->getOnepage()->getQuote()->collectTotals()->save();
        			
					$layout = $this->_getShippingMethodsHtml();
        			
					$result->rates		= $layout->getOutput();
					
					$rates = (array)$layout->getBlock('root')->getShippingRates();
					
					
					if(count($rates) == 1){
						
						foreach($rates as $rate_code=>$methods){
							
							if(count($methods) == 1){
								foreach($methods as $method){
									$address->setShippingMethod($method->getCode());	
									$this->getOnepage()->getQuote()->setTotalsCollectedFlag(false);								
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
			case('load_address'):
				
				$customerAddressId = $this->getRequest()->getParam('id');
				$type = $this->getRequest()->getParam('type');
				$use_for_shipping = $this->getRequest()->getParam('use_for_shipping');
				
				if ($customerAddressId)
				{				
    				$customerAddress = Mage::getModel('customer/address')->load($customerAddressId);
    	            if ($customerAddress->getId()) {
    	                if ($customerAddress->getCustomerId() != $this->getOnepage()->getQuote()->getCustomerId()) {
    	                    $result->error		= true;
    	                    $result->message	= $this->__('Customer Address is not valid.');
    	                }
    	                
    	                if(!$result->error){
    	                	    	                	    	                	
    	                	switch($type):
    	                	
    	                	case('billing'):
    	                	
    	                	$this->getOnepage()->getQuote()->getBillingAddress()->importCustomerAddress($customerAddress);
    	                	
    	                	if ($use_for_shipping)
    	                	{
    	                	    $this->getOnepage()->getQuote()->getBillingAddress()->setData('use_for_shipping', true);    	                	
        	                	$this->getOnepage()->getQuote()->getShippingAddress()->importCustomerAddress($customerAddress);
        	                	$this->getOnepage()->getQuote()->getShippingAddress()->setSameAsBilling(1);        	                	
        	                	Mage::getSingleton('checkout/session')->setShippingSameAsBilling(true);
    	                	}
    	                	else 
    	                	{
    	                	    $this->getOnepage()->getQuote()->getBillingAddress()->setData('use_for_shipping', false);    	                	        	                	
        	                	$this->getOnepage()->getQuote()->getShippingAddress()->setSameAsBilling(0);        	                	
        	                	Mage::getSingleton('checkout/session')->setShippingSameAsBilling(false);
    	                	}
    	                	
    	                	
    	                	break;
    	                	
    	                	case('shipping'):
    	                	
    	                	$this->getOnepage()->getQuote()->getBillingAddress()->setData('use_for_shipping', false);
    	                	
    	                	$this->getOnepage()->getQuote()->getShippingAddress()->importCustomerAddress($customerAddress);
    	                	$this->getOnepage()->getQuote()->getShippingAddress()->setSameAsBilling(0);
    	                	
    	                	Mage::getSingleton('checkout/session')->setShippingSameAsBilling(false);
    	                	
    	                	break;
    	                	
    	                	endswitch;
    	                } 
    	            }
    	            else {
                        $result->error		= true;
    	                $result->message	= $this->__('Customer Address is not valid.');    	                
    	            }    
				}    
                else
                {                                        
                     $result->error		= true;
    	             $result->message	= $this->__('Customer Address is not valid.');   
                }
				
                if(!$result->error){
	                	                	    				
    				if (!$this->getOnepage()->getQuote()->isVirtual()) {
    					
    					$address = $this->getOnepage()->getQuote()->getShippingAddress();							
            			$address->setCollectShippingRates(true);
            			$address->collectShippingRates();
            			
            			$address->setCollectShippingRates(true);         				    
            			$this->getOnepage()->getQuote()->collectTotals()->save();
            			
            			$layout = $this->_getShippingMethodsHtml();
            			
    					$result->rates	= $layout->getOutput();
    					
    					$rates = (array)$layout->getBlock('root')->getShippingRates();
    					
    					if(count($rates) == 1){
    						
    						foreach($rates as $rate_code=>$methods){
    							
    							if(count($methods) == 1){
    								foreach($methods as $method){											
    									$address->setShippingMethod($method->getCode());
    									$this->getOnepage()->getQuote()->setTotalsCollectedFlag(false);										
    								}
    							
    							}
    							
    							break;
    						}
    						
    					}
    					
    				}
    										
    				$this->getOnepage()->getQuote()->collectTotals()->save();
    				
    				$layout = Mage::getModel('core/layout');
            		$layout->getUpdate()->load('gomage_checkout_onepage_index');
            		$layout->generateXml()->generateBlocks();
            		$html = $layout->getBlock('checkout.onepage.address.billing')->toHtml();						
    				$result->content_billing	= trim($html);						
    				$html = $layout->getBlock('checkout.onepage.address.shipping')->toHtml();						
    				$result->content_shipping	= trim($html);
    				
    				$result->section = 'methods';								
    				$result->payments	= $this->_getPaymentMethodsHtml();
    				$result->totals 	= $this->_getReviewHtml();
						
                }    	                
	                
			break;
			case 'prepare_sagepay':
			    
			    $result->message = array();
			    
		        $billing_address_data = $this->getRequest()->getPost('billing');
        		
        		if(isset($billing_address_data['day']) && $billing_address_data['month'] && $billing_address_data['year']){
	        		try{
		        		$dob = sprintf("%02d/%02d/%04d", substr($billing_address_data['day'], 0, 2), substr($billing_address_data['month'], 0, 2), substr($billing_address_data['year'], 0, 4));
		       			
		        		$dob = Mage::app()->getLocale()->date($dob, null, null, false)->toString('yyyy-MM-dd');
		        			
		        		$this->getOnepage()->getQuote()->setCustomerDob($dob);
		        		
		        	}catch(Exception $e){
		        			
		        			$result->error		= true;
							$result->message[]    = $this->__('Incorrect date of birdhday');
		        			
	        		}
        		}
        		
        		if(isset($billing_address_data['taxvat'])){
        			$this->getOnepage()->getQuote()->setCustomerTaxvat($billing_address_data['taxvat']);
        			$billing_address_data['customer_taxvat'] = $billing_address_data['taxvat'];
        		}
        		
        		if((bool)($this->getSession()->getCustomer()->getId()) == false && (intval(Mage::helper('gomage_checkout')->getCheckoutMode()) === 1 || $this->getRequest()->getParam('create_account'))){
        			
        			$this->getOnepage()->getQuote()->setCheckoutMethod(Mage_Checkout_Model_Type_Onepage::METHOD_REGISTER);
		        	
		        	$this->getOnepage()->getQuote()->getBillingAddress()->setSaveInAddressBook(true);
					$this->getOnepage()->getQuote()->getShippingAddress()->setSaveInAddressBook(true);
					
		            $this->getSession()->setCreateAccount(true);
	            }else{
	            	
	            	if((bool)($this->getSession()->getCustomer()->getId()) == false && !$this->getOnepage()->getQuote()->hasVirtual()){
	            		$this->getOnepage()->getQuote()->setCheckoutMethod(Mage_Checkout_Model_Type_Onepage::METHOD_GUEST);
        			}else{
        				$this->getOnepage()->getQuote()->setCheckoutMethod(null);
        			}
        			
        			$customer = $this->getSession()->getCustomer();
        			
        			if($customer->getDefaultBillingAddress() == false){
        				$this->getOnepage()->getQuote()->getBillingAddress()->setSaveInAddressBook(true);
        			}
        			if($customer->getDefaultShippingAddress() == false){
        				$this->getOnepage()->getQuote()->getShippingAddress()->setSaveInAddressBook(true);
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
					
							$result->error = true;
							$messages = array();
							
							foreach((array) $shiping_address_result['message'] as $message){
								$messages[] = $this->__('Shipping Address Error').': '.$message;
							}
							$result->message = array_merge($result->message, $messages);
						}
						
					}
					
					if($method = $this->getRequest()->getPost('shipping_method', false)){
					
					$this->getOnepage()->saveShippingMethod($method);
					
					}
					
				}
				
				if(isset($billing_address_result['error']) && intval($billing_address_result['error'])){
					
					$result->error = true;
					$messages = array();
					
					foreach((array)$billing_address_result['message'] as $message){
						$messages[] = $this->__('Billing Address Error').': '.$message;
					}
					$result->message = array_merge($result->message, $messages);
				}
				
				$this->getOnepage()->getQuote()->save();
				
		        if ($payment_data = $this->getRequest()->getPost('payment', array())) {
					
					$this->getOnepage()->savePaymentMethod($payment_data);
					$this->getOnepage()->getQuote()->save();
					$this->getOnepage()->savePaymentMethod($payment_data);
					
					if($redirect = $this->getOnepage()->getQuote()->getPayment()->getCheckoutRedirectUrl()){
						
						$result->url = $redirect;						
					}					
            	}
				
            	
            	$this->getOnepage()->verifyCustomerVat();
            	$this->getOnepage()->getQuote()->collectTotals();
				
            	
        		if($customer_comment = $this->getRequest()->getParam('customer_comment')){
            		$this->getOnepage()->getQuote()->setData('gomage_checkout_customer_comment', nl2br(strip_tags($customer_comment)));
            	}
            	
            	$helper = Mage::helper('gomage_checkout');
            	
            	if($helper->getConfigData('general/termsandconditions') && !intval($this->getRequest()->getPost('accept_terms', 0))){
            		
            		$result->error = true;
            		$result->message[] = $this->__('Your must accept Terms and Conditions.');
            		
            	}
        										
				if(intval($this->getRequest()->getParam('subscribe')) > 0){
					
					if($this->getSession()->isLoggedIn()){
						
						Mage::getModel('newsletter/subscriber')->subscribe($this->getSession()->getCustomer()->getEmail());
						
					}else{
					
						Mage::getModel('newsletter/subscriber')->subscribe($this->getOnepage()->getQuote()->getBillingAddress()->getEmail());
					
					}
				
				}
				
        		Mage::dispatchEvent('gomage_checkout_save_quote_before', array('request'=>$this->getRequest(), 'quote' => $this->getOnepage()->getQuote()));
        		
        		$customer = $this->getSession()->getCustomer();
        		
        		if($this->getSession()->isLoggedIn() && $customer->getTaxvat() != $this->getOnepage()->getQuote()->getBillingAddress()->getTaxvat()){
        			
        			$customer->setTaxvat($this->getOnepage()->getQuote()->getBillingAddress()->getTaxvat());
        			$customer->save();
        		}
        		
        		if ($result->error)
        		{          		   
        		   $result->message = implode('\n', (array)$result->message);         		   
        		}   

			break;    
			
		endswitch;
		
		}catch(Mage_Core_Exception $e){
			
			$result->error = true;
			$result->message = $e->getMessage();
			
		}catch(Exception $e){
			
		}
		
		$this->getResponse()->setBody(json_encode($result));
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
		        			
		        		$this->getOnepage()->getQuote()->setCustomerDob($dob);
		        		
		        	}catch(Exception $e){
		        			
		        			$result['error'] = true;
							$result['message'][] = $this->__('Incorrect date of birdhday');
		        			
	        		}
        		}
        		
        		if(isset($billing_address_data['taxvat'])){
        			$this->getOnepage()->getQuote()->setCustomerTaxvat($billing_address_data['taxvat']);
        			$billing_address_data['customer_taxvat'] = $billing_address_data['taxvat'];
        		}
        		
        		if((bool)($this->getSession()->getCustomer()->getId()) == false && (intval(Mage::helper('gomage_checkout')->getCheckoutMode()) === 1 || $this->getRequest()->getParam('create_account'))){
        			
        			$this->getOnepage()->getQuote()->setCheckoutMethod(Mage_Checkout_Model_Type_Onepage::METHOD_REGISTER);
		        	
		        	$this->getOnepage()->getQuote()->getBillingAddress()->setSaveInAddressBook(true);
					$this->getOnepage()->getQuote()->getShippingAddress()->setSaveInAddressBook(true);
					
		            $this->getSession()->setCreateAccount(true);
	            }else{
	            	
	            	if((bool)($this->getSession()->getCustomer()->getId()) == false && !$this->getOnepage()->getQuote()->hasVirtual()){
	            		$this->getOnepage()->getQuote()->setCheckoutMethod(Mage_Checkout_Model_Type_Onepage::METHOD_GUEST);
        			}else{
        				$this->getOnepage()->getQuote()->setCheckoutMethod(null);
        			}
        			
        			$customer = $this->getSession()->getCustomer();
        			
        			if($customer->getDefaultBillingAddress() == false){
        				$this->getOnepage()->getQuote()->getBillingAddress()->setSaveInAddressBook(true);
        			}
        			if($customer->getDefaultShippingAddress() == false){
        				$this->getOnepage()->getQuote()->getShippingAddress()->setSaveInAddressBook(true);
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
					
					if($method = $this->getRequest()->getPost('shipping_method', false)){
					
					$this->getOnepage()->saveShippingMethod($method);
					
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
				
				$this->getOnepage()->getQuote()->save();
				
				if ($payment_data = $this->getRequest()->getPost('payment', array())) {
					
					$this->getOnepage()->savePaymentMethod($payment_data);
					$this->getOnepage()->getQuote()->save();
					$this->getOnepage()->savePaymentMethod($payment_data);
					
					if($redirect = $this->getOnepage()->getQuote()->getPayment()->getCheckoutRedirectUrl()){
						
						return $this->_redirectUrl($redirect);
						
					}
					
            	}
            	
            	$this->getOnepage()->verifyCustomerVat();
            	$this->getOnepage()->getQuote()->collectTotals();
				
            	
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
        		
        		$customer = $this->getSession()->getCustomer();
        		
        		if($this->getSession()->isLoggedIn() && $customer->getTaxvat() != $this->getOnepage()->getQuote()->getBillingAddress()->getTaxvat()){
        			
        			$customer->setTaxvat($this->getOnepage()->getQuote()->getBillingAddress()->getTaxvat());
        			$customer->save();
        		}
        		
        		$this->getOnepage()->saveOrder();
        		$this->getOnepage()->getQuote()->save();
            	Mage::dispatchEvent('gomage_checkout_save_quote_after', array('request'=>$this->getRequest(), 'quote' => $this->getOnepage()->getQuote()));
        		
        		$this->getCheckout()->setCustomerAssignedQuote(false);
				$this->getCheckout()->setCustomerAdressLoaded(false);
        		
				$redirect = $this->getOnepage()->getCheckout()->getRedirectUrl();
					
				if($redirect){
					$this->_redirectUrl($redirect);
				}else{
					$this->_redirect('checkout/onepage/success');
				}
		        //$this->_redirect('gomage_checkout/onepage/place');
            	
        	}catch(Mage_Core_Exception $e) {
        		Mage::logException($e);
            	Mage::helper('checkout')->sendPaymentFailedEmail($this->getOnepage()->getQuote(), $e->getMessage());
            	
            	$this->getOnepage()->getQuote()->save();
            	$this->getSession()->addError($e->getMessage());
            	$this->_redirect('checkout/onepage');
            	
        	}catch(Exception $e) {
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
                
				$this->getOnepage()->initCheckout();
				
		        $layout = Mage::getModel('core/layout');
		        $layout->getUpdate()->load(array('default', 'customer_logged_in', 'gomage_checkout_onepage_index'));
		        $layout->generateXml()->generateBlocks();
		        
		        $layout->getBlock('checkout.onepage')->setTemplate('gomage/checkout/form.phtml');
		        
		        if($block = $layout->getBlock('top.links')){
		        
		        $result['links']   = $block->toHtml();
		        
		        }
		        
		        $layout->removeOutputBlock('root');
		        $layout->addOutputBlock('checkout.onepage');
		        
		        $result['content'] = $layout->getOutput();
		        $result['vatstatus'] = $this->getOnepage()->getQuote()->getBillingAddress()->getIsValidVat();
		    	
                
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
        
		
		
        $this->getResponse()->setBody(Zend_Json::encode($result));
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
        $layout->getUpdate()->load('gomage_checkout_onepage_paymentmethod');
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
        $layout->getUpdate()->load('gomage_checkout_onepage_review');
        $layout->generateXml()->generateBlocks();
        return $layout->getOutput();
    }
}

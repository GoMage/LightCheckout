<?php
 /**
 * GoMage.com
 *
 * GoMage LightCheckout Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2010-2012 GoMage.com (http://www.gomage.com)
 * @author       GoMage.com
 * @license      http://www.gomage.com/licensing  Single domain license
 * @terms of use http://www.gomage.com/terms-of-use
 * @version      Release: 3.2
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
        
        if (!$quote->hasItems()) {
            $this->_redirect('checkout/cart');
            return;
        }
        
        if (!$quote->validateMinimumAmount()) {
            $error = Mage::getStoreConfig('sales/minimum_order/error_message');
            Mage::getSingleton('checkout/session')->addError($error);             
            if(!$helper->getConfigData('general/disable_cart')){
	            $this->_redirect('checkout/cart');
	            return;
            }
            $warning = Mage::getStoreConfig('sales/minimum_order/description');
            Mage::getSingleton('checkout/session')->addNotice($warning);
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
				
				$couponCode = urldecode($this->getRequest()->getParam('coupon_code'));
				
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
			    	
					$address->setCollectShippingRates(true);
        			$address->collectShippingRates();
        			$address->setCollectShippingRates(true);			    	
			    	
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
			    	$result->payments	= $this->_getPaymentMethodsHtml();
			    
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
					
					$_product = Mage::getModel('catalog/product')->load($item->getProductId()); 
					
					if ($_product->getStockItem()->getManageStock()){ 					
    					$maximumQty = intval($_product->getStockItem()->getMaxSaleQty());
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
        		            $maximumQty = $_product->getStockItem()->getQty();
        				    if($qty > $maximumQty){
        		            	
        		            	throw new Mage_Core_Exception($this->__('Maximum Allowed Qty: %s', round($maximumQty)));
        		            	
        		            }   
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
					$result->toplinks = $this->_getTopLinksHtml();
				}
				
			break;
			
			case('cartminus'):
				
				if($itemId = $this->getRequest()->getParam('id')){
					
					$quote = $this->getOnepage()->getQuote();
					$item = $quote->getItemById($itemId);
					
					$shippingMethod = $this->getOnepage()->getQuote()->getShippingAddress()->getShippingMethod();
					$paymentMethod = $this->getOnepage()->getQuote()->getPayment()->getMethod();
					
					$qty = intval($item->getQty()-1);
					
					$_product = Mage::getModel('catalog/product')->load($item->getProductId()); 
					
					if ($_product->getStockItem()->getManageStock()){
					
    					$minimumQty = intval($_product->getStockItem()->getMinSaleQty());
    					
    		            if($qty < $minimumQty){
    		            	
    		            	throw new Mage_Core_Exception($this->__('Minimal Allowed Qty: %s', $minimumQty));
    		            	
    		            }
					}
					
					
					if($qty > 0){
					
						$item->setQty($qty);
						$address = $this->getOnepage()->getQuote()->getShippingAddress();
					
					}else{
						
						$quote->removeItem($itemId);
						$address = $this->getOnepage()->getQuote()->getShippingAddress();
						
					}
					
				    if (!$quote->hasItems()) {
						
			            $result->url = Mage::app()->getStore()->getUrl('checkout/cart');
			            
			            
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
					$result->toplinks = $this->_getTopLinksHtml();
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
						if (!$quote->isVirtual()) {
		                    $result->gift_message = Mage::helper('gomage_checkout/giftMessage')->getInline('onepage_checkout', $quote);
						}
						
					}
					
					
					$result->totals = $this->_getReviewHtml();
					$result->toplinks = $this->_getTopLinksHtml();
				}
				
			break;
			
			case('giftwrap'):
			    if($itemId = $this->getRequest()->getParam('id')){
			        
			        $quote = $this->getOnepage()->getQuote();
					$item = $quote->getItemById($itemId);
					
					$shippingMethod = $this->getOnepage()->getQuote()->getShippingAddress()->getShippingMethod();
					$paymentMethod = $this->getOnepage()->getQuote()->getPayment()->getMethod();
					
					$item->setData('gomage_gift_wrap', intval($this->getRequest()->getParam('gomage_gift_wrap')));
					$address = $this->getOnepage()->getQuote()->getShippingAddress();
					
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
			
			case('varify_taxvat'):
			case('get_methods'):
				
				if($billing_address_data = $this->getRequest()->getPost('billing')){
					
					$billing_address_data = $this->_prepareBillingAddressData($billing_address_data);
															
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

					if (Mage::helper('gomage_checkout')->getIsAnymoreVersion(1, 11)){
						$enterprise_giftwrapping = Mage::getModel('enterprise_giftwrapping/observer');
						if ($enterprise_giftwrapping && method_exists($enterprise_giftwrapping, 'checkoutProcessWrappingInfo')){
							Mage::dispatchEvent('checkout_controller_onepage_save_shipping_method',
		                        				array('request'=>$this->getRequest(),
		                              				  'quote'=>$this->getOnepage()->getQuote())
		                        				);
							
							if ($this->getOnepage()->getQuote()->getShippingAddress()) {
								$wrappingInfo = array();
			                    $wrappingInfo['gw_id'] = $this->getOnepage()->getQuote()->getShippingAddress()->getData('gw_id');
			                    $wrappingInfo['gw_allow_gift_receipt'] = $this->getOnepage()->getQuote()->getShippingAddress()->getData('gw_allow_gift_receipt');
			                    $wrappingInfo['gw_add_card'] = $this->getOnepage()->getQuote()->getShippingAddress()->getData('gw_add_card');
			                    
			                    $this->getOnepage()->getQuote()->getBillingAddress()->addData($wrappingInfo);
							}
		                        				    				
							$this->getOnepage()->getQuote()->setTotalsCollectedFlag(false);
		                	$this->getOnepage()->getQuote()->collectTotals();                	
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
				$billing_address_data = $this->_prepareBillingAddressData($billing_address_data);
				
				$address = $this->getOnepage()->getQuote()->getBillingAddress();
				$address->addData($billing_address_data);
				$address->implodeStreetAddress();
				
				$this->getOnepage()->getQuote()->collectTotals()->save();
				
				$result->section	= 'payment_methods';
				$result->payments	= $this->_getPaymentMethodsHtml();
				$result->totals = $this->_getReviewHtml();
			break;
			case('save_payment_methods'):
                
                $billing_address_data = $this->getRequest()->getPost('billing');
                $billing_address_data = $this->_prepareBillingAddressData($billing_address_data);
                
                $address = $this->getOnepage()->getQuote()->getBillingAddress();
				$address->addData($billing_address_data);
				$address->implodeStreetAddress();

                if(!isset($billing_address_data['use_for_shipping']) || !intval($billing_address_data['use_for_shipping'])){
        			$shipping_address_data = $this->getRequest()->getPost('shipping');
        		}else{
        			$shipping_address_data = $billing_address_data;
        		}
        		$address = $this->getOnepage()->getQuote()->getShippingAddress();
        		$address->addData($shipping_address_data);
        		$address->implodeStreetAddress();

				$this->getOnepage()->getQuote()->collectTotals()->save();
                
			    $result->section	= 'centinel';
			    $data = $this->getRequest()->getPost('payment', array());
        		if (empty($data)){                    
                    $result->error	= true;
					$result->message = $this->__('Please select payment method');
                }else{
                    $payment = $this->getOnepage()->getQuote()->getPayment();
                    $payment->importData($data);            
                    $this->getOnepage()->getQuote()->save();
                    $redirectUrl = $this->getOnepage()->getQuote()->getPayment()->getCheckoutRedirectUrl();
                    if (!$redirectUrl) {
                        $method = $this->getOnepage()->getQuote()->getPayment()->getMethodInstance();
                        if ($method->getIsCentinelValidationEnabled()) {
                            $centinel = $method->getCentinelValidator();
                            if ($centinel && $centinel->shouldAuthenticate()) {
			                    $result->centinel = $this->_getCentinelHtml();
                            }
                        }        
                    }
                }
                
			break;    			
			case('get_shipping_methods'):
				if (!$this->getOnepage()->getQuote()->isVirtual()) {
					
				    $paymentMethod = $this->getOnepage()->getQuote()->getPayment()->getMethod();
				    
					$billing_address_data = $this->getRequest()->getPost('billing');
					$billing_address_data = $this->_prepareBillingAddressData($billing_address_data);
					
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
            	
            	if (isset($payment['use_customer_balance'])){
					try{
	            		$this->getOnepage()->getQuote()->getPayment()->importData($payment);
	            		$this->getOnepage()->getQuote()->save();
					}catch(Exception $e){	
                	}	
            	}
            	
            	
            	if ($this->getOnepage()->getQuote()->getUseRewardPoints() && !isset($payment['use_reward_points'])){
            	      $this->getOnepage()->getQuote()->setUseRewardPoints(false);
            	}elseif (!$this->getOnepage()->getQuote()->getUseRewardPoints() && isset($payment['use_reward_points'])){
            	      $this->getOnepage()->getQuote()->setUseRewardPoints(true);
            	}
            	
				if (!$this->getOnepage()->getQuote()->isVirtual()) {

                        $address = $this->getOnepage()->getQuote()->getShippingAddress();
	        			$address->setCollectShippingRates(true);
	        			$address->collectShippingRates();

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


				$this->getOnepage()->getQuote()->collectTotals();
				$result->section = 'totals';
				$result->totals = $this->_getReviewHtml();
				$result->payments	= $this->_getPaymentMethodsHtml();
				
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
            		if ($type == 'billing'){
	            		$html = $layout->getBlock('checkout.onepage.address.billing')->toHtml();						
	    				$result->content_billing	= trim($html);
            		}						
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
		        $billing_address_data = $this->_prepareBillingAddressData($billing_address_data);
        		
        		if(isset($billing_address_data['day']) && $billing_address_data['month'] && $billing_address_data['year']){
	        		try{
		        		$dob = sprintf("%02d/%02d/%04d", substr($billing_address_data['day'], 0, 2), substr($billing_address_data['month'], 0, 2), substr($billing_address_data['year'], 0, 4));
		       			
		        		$dob = Mage::app()->getLocale()->date($dob, null, null, false)->toString('yyyy-MM-dd');
		        			
		        		$this->getOnepage()->getQuote()->setCustomerDob($dob);
		        		
	        			if ($this->getSession()->getCustomer()->getId()){
		        			$this->getOnepage()->getQuote()->getCustomer()->setDob($dob);
		        		}
		        		
		        		$billing_address_data['dob'] = $dob;
		        		
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
               		$this->getOnepage()->getQuote()->save();
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
			
			case('find_exists_customer'):
				
				$result->exists = false;
				
				if ($this->getOnepage()->getCheckoutMode() != 2 && !Mage::getSingleton('customer/session')->isLoggedIn()){
				
					$email = $this->getRequest()->getPost('email');
					
					$customer = Mage::getModel('customer/customer');
			        $customer->setWebsiteId(Mage::app()->getWebsite()->getId());
			        $customer->loadByEmail($email);		        
			        if ($customer->getId()) {
			            $result->exists = true;
			        }
				}
				
			break;	  
			
		endswitch;
		
		}catch(Mage_Core_Exception $e){
			
			$result->error = true;
			$result->message = $e->getMessage();
			
		}catch(Exception $e){
			
		}
		
		$quote = $this->getOnepage()->getQuote();		
        if (!$quote->validateMinimumAmount()) {        	
        	$messages_block = Mage::getSingleton('core/layout')->getMessagesBlock();        	
        	if ($error = Mage::getStoreConfig('sales/minimum_order/error_message')){
        		$messages_block->addError($error);        	
        	}
            if ($warning = Mage::getStoreConfig('sales/minimum_order/description')){
            	$messages_block->addNotice($warning);                         
            }            
            $result->messages_block = $messages_block->getGroupedHtml();            
        }

		$this->getResponse()->setBody(json_encode($result));
	}
	
	public function saveAction(){

	    
		$helper = Mage::helper('gomage_checkout');
		
		$result = array('error'=>false, 'success' => true, 'message'=>array());
					
		if((bool)$helper->getConfigData('general/enabled') == false){			
			$result['redirect'] = Mage::getUrl('checkout/onepage'); 
		}
		
		if ($this->getRequest()->isPost() && $post = $this->getRequest()->getPost()) {
			
        	try {
        		        		        		
        		$pollId = intval($this->getRequest()->getPost('poll_id'));
                $answerId = intval($this->getRequest()->getPost('poll_vote'));
                if ($pollId && $answerId){       
                    $poll = Mage::getModel('poll/poll')->load($pollId);
                    if ($poll->getId() && !$poll->getClosed() && !$poll->isVoted()) {
                        $vote = Mage::getModel('poll/poll_vote')
                            ->setPollAnswerId($answerId)
                            ->setIpAddress(Mage::helper('core/http')->getRemoteAddr(true))
                            ->setCustomerId(Mage::getSingleton('customer/session')->getCustomerId());           
                        $poll->addVote($vote);
                        Mage::getSingleton('core/session')->setJustVotedPoll($pollId);
                    }
                }    
        		                
        		$billing_address_data = $this->getRequest()->getPost('billing');
        		$billing_address_data = $this->_prepareBillingAddressData($billing_address_data);
        		
        		if(isset($billing_address_data['day']) && $billing_address_data['month'] && $billing_address_data['year']){
	        		try{
		        		$dob = sprintf("%02d/%02d/%04d", substr($billing_address_data['day'], 0, 2), substr($billing_address_data['month'], 0, 2), substr($billing_address_data['year'], 0, 4));
		       			
		        		$dob = Mage::app()->getLocale()->date($dob, null, null, false)->toString('yyyy-MM-dd');
		        			
		        		$this->getOnepage()->getQuote()->setCustomerDob($dob);
		        		
		        		if ($this->getSession()->getCustomer()->getId()){
		        			$this->getOnepage()->getQuote()->getCustomer()->setDob($dob);
		        		}
		        		
		        		$billing_address_data['dob'] = $dob;
		        		
		        	}catch(Exception $e){
		        			
		        			$result['error'] = true;
		        			$result['success'] = false;
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
							$result['success'] = false;
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
					$result['success'] = false;
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
												
						$result['redirect'] = $redirect;
						$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
						return;
					}
					
            	}
            	
            	$this->getOnepage()->verifyCustomerVat();
            	$this->getOnepage()->getQuote()->collectTotals();
				
            	
        		if($customer_comment = $this->getRequest()->getParam('customer_comment')){
            		$this->getOnepage()->getQuote()->setData('gomage_checkout_customer_comment', nl2br(strip_tags($customer_comment)));
            	}
            	
            	if($helper->getConfigData('general/termsandconditions') && !intval($this->getRequest()->getPost('accept_terms', 0))){
            		
            		$result['error'] = true;
            		$result['success'] = false;
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
					$result['redirect'] = $redirect;
				}else{
					$result['redirect'] = Mage::getUrl('checkout/onepage/success');															
				}
            	
        	}catch(Mage_Core_Exception $e) {
        		Mage::logException($e);
            	Mage::helper('checkout')->sendPaymentFailedEmail($this->getOnepage()->getQuote(), $e->getMessage());
            	
            	$this->getOnepage()->getQuote()->save();
            	$this->getSession()->addError($e->getMessage());
            	$result['redirect'] = Mage::getUrl('checkout/onepage');
            	
        	}catch(Exception $e) {
        		Mage::logException($e);
            	Mage::helper('checkout')->sendPaymentFailedEmail($this->getOnepage()->getQuote(), $e->getMessage());
        		$this->getOnepage()->getQuote()->save();
        		$this->getSession()->addError($this->__('There was an error processing your order. Please contact us or try again later.'));
        		$result['redirect'] = Mage::getUrl('checkout/onepage');
        		
        	}
			
		}else{
			$result['redirect'] = Mage::getUrl('checkout/onepage');
		}
		
		$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
		
	}
	
	
	public function customerLoginAction(){
		
		$result = array('error'=>false);	
		
		$login = $this->getRequest()->getPost('login');
		
        if (!empty($login['username']) && !empty($login['password'])) {
        	
        	$session = Mage::getSingleton('customer/session');
        	
            try {
                $session->login($login['username'], $login['password']);
                if (method_exists($session, 'renewSession')){
                    $session->renewSession();
                }
                                
				$this->getOnepage()->initCheckout();
				
		        $layout = Mage::getModel('core/layout');
		        $layout->getUpdate()->load(array('default', 'customer_logged_in', 'gomage_checkout_onepage_index'));
		        $layout->generateXml()->generateBlocks();
		        
		        $layout->getBlock('checkout.onepage')->setTemplate('gomage/checkout/form.phtml');

		        if (Mage::helper('gomage_checkout')->getIsAnymoreVersion(1, 8)){		        				        
		        	if($block = $layout->getBlock('header')){
		        		$block->unsetChild('welcome');		        					        
			        	$result['header'] = $block->toHtml();			        
			        }
		        }else{
		        	if($block = $layout->getBlock('top.links')){			        
			        	$result['links']   = $block->toHtml();			        
			        }
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
	
	public function forgotPasswordAction(){
		$result = array('error'=>false);
		
	 	$email = (string) $this->getRequest()->getPost('email');
        if ($email) {
            if (!Zend_Validate::is($email, 'EmailAddress')) {
                $result['error'] = true;
                $result['message'] = $this->__('Invalid email address.');                
            }

            if (!$result['error']){
	            $customer = Mage::getModel('customer/customer')
	                ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
	                ->loadByEmail($email);
	
	            if ($customer->getId()) {
	                try {
	                    $newResetPasswordLinkToken = Mage::helper('customer')->generateResetPasswordLinkToken();
	                    $customer->changeResetPasswordLinkToken($newResetPasswordLinkToken);
	                    $customer->sendPasswordResetConfirmationEmail();
	                } catch (Exception $exception) {
	                    $result['error'] = true;
	                	$result['message'] = $exception->getMessage();
	                }
	            }else{
	            	$result['error'] = true;
                	$result['message'] = $this->__('No found customer with email %s.', Mage::helper('customer')->htmlEscape($email));
	            }
            }
            if (!$result['error']){
            	$result['message'] = Mage::helper('customer')->__('If there is an account associated with %s you will receive an email with a link to reset your password.', Mage::helper('customer')->htmlEscape($email));
            }
            
                
        } else {
        	$result['error'] = true;
            $result['message'] = $this->__('Please enter your email.');
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
    
    protected function _getCentinelHtml()
    {
        $layout = Mage::getModel('core/layout');
        $layout->getUpdate()->load('gomage_checkout_onepage_centinel');
        $layout->generateXml()->generateBlocks();
        return $layout->getOutput();
    }
    
    protected function _getTopLinksHtml(){
	    $layout = Mage::getSingleton('core/layout');
	    
	    if (Mage::helper('gomage_checkout')->getIsAnymoreVersion(1, 8)){
	    	$top_links = $layout->createBlock('checkout/cart_sidebar', 'glc.top.links');
        	$top_links->setTemplate('checkout/cart/cartheader.phtml');
	    }else{
		    $top_links = $layout->createBlock('page/template_links', 'glc.top.links');
	        $checkout_cart_link = $layout->createBlock('checkout/links', 'checkout_cart_link');            
	        $top_links->setChild('checkout_cart_link', $checkout_cart_link);
	        if (method_exists($top_links, 'addLinkBlock')){
	            $top_links->addLinkBlock('checkout_cart_link');
	        }
	        $checkout_cart_link->addCartLink();
	    }  

        return $top_links->renderView(); 
	}

	protected function _prepareBillingAddressData($billing_address_data){
		$params = array('customer_password', 'confirm_password');
		
		foreach ($params as $param){
			if (isset($billing_address_data[$param])){
				$billing_address_data[$param] = urldecode($billing_address_data[$param]);
			}
		}		
		
		return $billing_address_data;
	}
    
}
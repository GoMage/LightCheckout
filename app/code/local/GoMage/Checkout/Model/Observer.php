<?php
/**
 * GoMage LightCheckout Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2010-2013 GoMage (http://www.gomage.com)
 * @author       GoMage
 * @license      http://www.gomage.com/license-agreement/  Single domain license
 * @terms of use http://www.gomage.com/terms-of-use
 * @version      Release: 5.0
 * @since        Class available since Release 1.0
 */

class GoMage_Checkout_Model_Observer {
	
	static public function salesOrderLoad($event) {
		if ($date = $event->getOrder()->getGomageDeliverydate()) {
			$formated_date = Mage::app()->getLocale()->date($date, Varien_Date::DATETIME_INTERNAL_FORMAT)->toString(Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM));
			$event->getOrder()->setGomageDeliverydateFormated($formated_date);
		}
	}
	
	static public function checkK($event) {
		$key = Mage::getStoreConfig('gomage_activation/lightcheckout/key');
		Mage::helper('gomage_checkout')->a($key);
	}
	
	public function setResponseAfterSaveOrder(Varien_Event_Observer $observer) {
		try {
			$paypal_observer = Mage::getModel('paypal/observer');
		}
		catch (Exception $e) {
			//class not exists Mage_Paypal_Model_Observer
			$paypal_observer = null;
		}
		if ($paypal_observer && method_exists($paypal_observer, 'setResponseAfterSaveOrder')) {
			$paypal_observer->setResponseAfterSaveOrder($observer);
		}
		
		try {
			$authorizenet_observer = Mage::getModel('authorizenet/directpost_observer');
		}
		catch (Exception $e) {
			//class not exists Mage_Authorizenet_Model_Directpost_Observer
			$authorizenet_observer = null;
		}
		if ($authorizenet_observer && method_exists($authorizenet_observer, 'addAdditionalFieldsToResponseFrontend')) {
			$authorizenet_observer->addAdditionalFieldsToResponseFrontend($observer);
		}
		
		return $this;
	}
	
	public function checkGoMageCheckout($observer) {
		if (Mage::getStoreConfig('customer/captcha/enable')) {
			$formId = 'gcheckout_onepage';
			$captchaModel = Mage::helper('captcha')->getCaptcha($formId);
			if ($captchaModel->isRequired()) {
				$controller = $observer->getControllerAction();
				$captchaParams = $controller->getRequest()->getPost(Mage_Captcha_Helper_Data::INPUT_NAME_FIELD_VALUE);
				if (! $captchaModel->isCorrect($captchaParams[$formId])) {
					$controller->setFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);
					$result = array(
									'error' => 1, 
									'message' => Mage::helper('captcha')->__('Incorrect CAPTCHA.')
					);
					$controller->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
				}
			}
		}
		return $this;
	}
	
	public function addGiftWrapItem($observer) {
		if (Mage::helper('gomage_checkout')->getConfigData('gift_wrapping/enable') > 0) {
			$cart = $observer->getEvent()->getData('paypal_cart');
			if ($cart) {
				$gift_wrap_amount = 0;
				$salesEntity = $cart->getSalesEntity();
				foreach ($salesEntity->getAllItems() as $item) {
					$gift_wrap_amount += $item->getBaseGomageGiftWrapAmount();
				}
				if ($gift_wrap_amount) {
					$cart->addItem(Mage::helper('gomage_checkout')->getConfigData('gift_wrapping/title'), 1, $gift_wrap_amount);
				}
			}
		}
		return $this;
	}
	
	public function disableShoppingCart($event) {
		$h = Mage::helper('gomage_checkout');
		
		if ($h->getConfigData('general/disable_cart') && $h->getConfigData('general/enabled') && $h->isAvailableWebsite()) {
			$quote = Mage::getSingleton('gomage_checkout/type_onestep')->getQuote();
			if ($quote->hasItems()) {
				$event->getEvent()->getControllerAction()->getResponse()->setRedirect(Mage::getUrl('checkout/onepage'));
			}
		}
	}
	
	public function prepareCustomerOrder($event){
		$customer = $event->getCustomer();
		$account_controller = $event->getAccountController();
		if ($customer && $customer->getId() && $account_controller){
			$order_id = $account_controller->getRequest()->getParam('glc_order_id');
			if ($order_id){
				$order = Mage::getModel('sales/order')->load($order_id);
			    $order->setCustomerId($customer->getId())
			    	  ->setCustomerIsGuest()
			    	  ->setCustomerGroupId($customer->getGroupId());
			    $order->save();
			    $billing = $order->getBillingAddress();
				if ($billing){			
					$address = Mage::getModel('customer/address')
								->setData($billing->getData())
								->setId(null)
								->setIsDefaultBilling(true)			
								->setCustomerId($customer->getId())
							    ->save();
				}		
				$shipping = $order->getShippingAddress();
				if ($shipping){
					$address = Mage::getModel('customer/address')
								->setData($shipping->getData())
								->setId(null)
								->setIsDefaultShipping(true)
								->setCustomerId($customer->getId())								
								->save();
				}						
			}    			
		}
	}

}
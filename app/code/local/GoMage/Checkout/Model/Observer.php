<?php
 /**
 * GoMage LightCheckout Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2010-2012 GoMage (http://www.gomage.com)
 * @author       GoMage
 * @license      http://www.gomage.com/license-agreement/  Single domain license
 * @terms of use http://www.gomage.com/terms-of-use
 * @version      Release: 3.2
 * @since        Class available since Release 1.0
 */
	
class GoMage_Checkout_Model_Observer {
		
	static public function salesOrderLoad($event){
		
		if($date = $event->getOrder()->getGomageDeliverydate()){
			
			$formated_date = Mage::app()->getLocale()->date($date, Varien_Date::DATETIME_INTERNAL_FORMAT)->toString(Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM));
			$event->getOrder()->setGomageDeliverydateFormated($formated_date);
		};
		
	}
	
	static public function checkK($event){
		
		$key = Mage::getStoreConfig('gomage_activation/lightcheckout/key');
		
		Mage::helper('gomage_checkout')->a($key);
		
	}
	
	public function setResponseAfterSaveOrder(Varien_Event_Observer $observer){
		try{
			$paypal_observer = Mage::getModel('paypal/observer');
		}catch (Exception $e){
			//class not exists Mage_Paypal_Model_Observer
			$paypal_observer = null;
		}
		if ($paypal_observer && method_exists($paypal_observer, 'setResponseAfterSaveOrder')){
			$paypal_observer->setResponseAfterSaveOrder($observer);
		}
		
		try{
			$authorizenet_observer = Mage::getModel('authorizenet/directpost_observer');
		}catch (Exception $e){
			//class not exists Mage_Authorizenet_Model_Directpost_Observer
			$authorizenet_observer = null;
		}
		if ($authorizenet_observer && method_exists($authorizenet_observer, 'addAdditionalFieldsToResponseFrontend')){
			$authorizenet_observer->addAdditionalFieldsToResponseFrontend($observer);
		}
				
		return $this;
	}
	
 	public function checkGoMageCheckout($observer)
    {    	
    	if (Mage::getStoreConfig('customer/captcha/enable')){
	        $formId = 'gcheckout_onepage';
	        $captchaModel = Mage::helper('captcha')->getCaptcha($formId);        
	        if ($captchaModel->isRequired()) {
		        $controller = $observer->getControllerAction();
		        $captchaParams = $controller->getRequest()->getPost(Mage_Captcha_Helper_Data::INPUT_NAME_FIELD_VALUE);	        
				if (!$captchaModel->isCorrect($captchaParams[$formId])) {
				    $controller->setFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);			    
				    $result = array('error' => 1, 'message' => Mage::helper('captcha')->__('Incorrect CAPTCHA.'));
				    $controller->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
				}
	        }     
    	}   
        return $this;
    }
    
}
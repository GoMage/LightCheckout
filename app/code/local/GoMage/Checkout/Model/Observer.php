<?php
 /**
 * GoMage LightCheckout Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2010-2012 GoMage (http://www.gomage.com)
 * @author       GoMage
 * @license      http://www.gomage.com/license-agreement/  Single domain license
 * @terms of use http://www.gomage.com/terms-of-use
 * @version      Release: 3.1
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
		$paypal_observer = Mage::getModel('paypal/observer');
		if ($paypal_observer && method_exists($paypal_observer, 'setResponseAfterSaveOrder')){
			return $paypal_observer->setResponseAfterSaveOrder($observer);
		}
		
		return $this;
	}
		
}
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
 * @since        Class available since Release 3.1
 */

class GoMage_KlarnaPayment_Helper_Data extends Mage_Core_Helper_Abstract{

    public function isGoMage_KlarnaPaymentEnabled()
    {
       $_modules = Mage::getConfig()->getNode('modules')->children();
       	   	   
	   $_modulesArray = (array)$_modules;
	   
	   if(!isset($_modulesArray['GoMage_KlarnaPayment'])) return false;
	   if (!$_modulesArray['GoMage_KlarnaPayment']->is('active')) return false;

       if(!isset($_modulesArray['Klarna_KlarnaPaymentModule'])) return false;	   
	   if (!$_modulesArray['Klarna_KlarnaPaymentModule']->is('active')) return false;

	   return true;	   
    }	
    
}

<?php
 /**
 * GoMage LightCheckout Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2010-2016 GoMage (https://www.gomage.com)
 * @author       GoMage
 * @license      https://www.gomage.com/license-agreement/  Single domain license
 * @terms of use https://www.gomage.com/terms-of-use
 * @version      Release: 5.9.2
 * @since        Class available since Release 2.2
 */

class GoMage_SagePay_Helper_Data extends Mage_Core_Helper_Abstract{

    public function isGoMage_SagePayEnabled()
    {
       $_modules = Mage::getConfig()->getNode('modules')->children();
       	   	   
	   $_modulesArray = (array)$_modules;
	   
	   if(!isset($_modulesArray['GoMage_SagePay'])) return false;
	   if (!$_modulesArray['GoMage_SagePay']->is('active')) return false;

       if(!isset($_modulesArray['Ebizmarts_SagePaySuite'])) return false;	   
	   if (!$_modulesArray['Ebizmarts_SagePaySuite']->is('active')) return false;

	   return true;	   
    }	
    
}

<?php
 /**
 * GoMage LightCheckout Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2010-2018 GoMage (https://www.gomage.com)
 * @author       GoMage
 * @license      https://www.gomage.com/license-agreement/  Single domain license
 * @terms of use https://www.gomage.com/terms-of-use
 * @version      Release: 5.9.4
 * @since        Class available since Release 3.1
 */

class GoMage_PayOne_Helper_Data extends Mage_Core_Helper_Abstract{

    public function isGoMage_PayOneEnabled()
    {
       $_modules = Mage::getConfig()->getNode('modules')->children();
       	   	   
	   $_modulesArray = (array)$_modules;
	   
	   if(!isset($_modulesArray['GoMage_PayOne'])) return false;
	   if (!$_modulesArray['GoMage_PayOne']->is('active')) return false;

       if(!isset($_modulesArray['Payone_Core'])) return false;	   
	   if (!$_modulesArray['Payone_Core']->is('active')) return false;

	   return true;	   
    }	
    
}

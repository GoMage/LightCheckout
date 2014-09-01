<?php
 /**
 * GoMage LightCheckout Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2010-2014 GoMage (http://www.gomage.com)
 * @author       GoMage
 * @license      http://www.gomage.com/license-agreement/  Single domain license
 * @terms of use http://www.gomage.com/terms-of-use
 * @version      Release: 5.7
 * @since        Class available since Release 5.0
 */
	
class GoMage_Checkout_Model_Adminhtml_System_Config_Source_Device{

   
	const ANDROID = 1;
    const BLACKBERRY = 2;
    const IOS = 3;
    const OTHER = 4;
	
    public function toOptionArray()
    {
        return array(
        	array('value' => '', 'label' => ''),
            array('value' => self::ANDROID, 'label' => Mage::helper('gomage_checkout')->__('Android')),
            array('value' => self::BLACKBERRY, 'label' => Mage::helper('gomage_checkout')->__('BlackBerry')),
            array('value' => self::IOS, 'label' => Mage::helper('gomage_checkout')->__('iOS')),
            array('value' => self::OTHER, 'label' => Mage::helper('gomage_checkout')->__('Other')),
        );
    }

}
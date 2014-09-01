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
 * @since        Class available since Release 3.1
 */
	
class GoMage_Checkout_Model_Adminhtml_System_Config_Source_Vatverification{

	CONST VIES = 0;
	CONST ISVAT = 1;
	
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => self::VIES, 'label'=>Mage::helper('gomage_checkout')->__('VIES')),
            array('value' => self::ISVAT, 'label'=>Mage::helper('gomage_checkout')->__('Isvat')),            
        );
    }

}
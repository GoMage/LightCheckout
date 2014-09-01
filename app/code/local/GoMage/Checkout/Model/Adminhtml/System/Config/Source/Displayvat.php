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

class GoMage_Checkout_Model_Adminhtml_System_Config_Source_Displayvat
{
    CONST BILLING          = 1;
    CONST SHIPPING         = 2;
    CONST BILLING_SHIPPING = 3;

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array('value' => self::BILLING, 'label' => Mage::helper('gomage_checkout')->__('Billing Address')),
            array('value' => self::SHIPPING, 'label' => Mage::helper('gomage_checkout')->__('Shipping Address')),
            array('value' => self::BILLING_SHIPPING, 'label' => Mage::helper('gomage_checkout')->__('Billing and Shipping Address')),
        );
    }

}
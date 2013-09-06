<?php
/**
 * GoMage.com
 *
 * GoMage LightCheckout Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2010 GoMage.com (http://www.gomage.com)
 * @author       GoMage.com
 * @license      http://www.gomage.com/licensing  Single domain license
 * @terms of use http://www.gomage.com/terms-of-use
 * @version      Release: 1.0
 * @since        Class available since Release 1.0
 */
class GoMage_Checkout_Model_Adminhtml_System_Config_Source_Shipping_Allowedmethods
    extends Mage_Adminhtml_Model_System_Config_Source_Shipping_Allmethods
{
    public function toOptionArray($isActiveOnlyFlag=false)
    {
        return parent::toOptionArray(true);
    }

}

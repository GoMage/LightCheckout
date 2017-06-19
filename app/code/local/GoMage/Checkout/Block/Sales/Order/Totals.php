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
 * @since        Class available since Release 2.4
 */
class GoMage_Checkout_Block_Sales_Order_Totals extends Mage_Sales_Block_Order_Totals
{
    protected function _initTotals()
    {
        parent::_initTotals();
        $source = $this->getSource();
        $totals = Mage::helper('gomage_checkout/giftwrap')->getTotals($source);
        foreach ($totals as $total) {
            $this->addTotalBefore(new Varien_Object($total), 'tax');
        }
        return $this;
    }
}

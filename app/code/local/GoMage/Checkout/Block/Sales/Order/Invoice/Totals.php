<?php
 /**
 * GoMage LightCheckout Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2010-2012 GoMage (https://www.gomage.com)
 * @author       GoMage
 * @license      https://www.gomage.com/license-agreement/  Single domain license
 * @terms of use https://www.gomage.com/terms-of-use
 * @version      Release: 4.1
 * @since        Class available since Release 3.1
 */

class GoMage_Checkout_Block_Sales_Order_Invoice_Totals extends Mage_Sales_Block_Order_Invoice_Totals
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

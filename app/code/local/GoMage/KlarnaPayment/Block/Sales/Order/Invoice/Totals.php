<?php
/**
 * GoMage LightCheckout Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2010-2015 GoMage (http://www.gomage.com)
 * @author       GoMage
 * @license      http://www.gomage.com/license-agreement/  Single domain license
 * @terms of use http://www.gomage.com/terms-of-use
 * @version      Release: 5.9
 * @since        Class available since Release 3.1
 */

if (Mage::helper('gomage_klarnapayment')->isGoMage_KlarnaPaymentEnabled()) {
    abstract class GoMage_KlarnaPayment_Block_Sales_Order_Invoice_TotalsAbstract extends Vaimo_Klarna_Block_Adminhtml_Sales_Invoice_Totals
    {
        public function _initTotals()
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
} else {
    abstract class GoMage_KlarnaPayment_Block_Sales_Order_Invoice_TotalsAbstract extends GoMage_Checkout_Block_Sales_Order_Invoice_Totals
    {

    }
}

class GoMage_KlarnaPayment_Block_Sales_Order_Invoice_Totals extends GoMage_KlarnaPayment_Block_Sales_Order_Invoice_TotalsAbstract
{

}
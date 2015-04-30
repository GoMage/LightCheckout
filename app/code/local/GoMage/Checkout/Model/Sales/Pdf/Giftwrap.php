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
 * @since        Class available since Release 5.9
 */
class GoMage_Checkout_Model_Sales_Pdf_Giftwrap extends Mage_Sales_Model_Order_Pdf_Total_Default
{

    public function getTotalsForDisplay()
    {
        $order         = $this->getOrder();
        $amount        = $order->formatPriceTxt($this->getAmount());
        $amountInclTax = $order->formatPriceTxt($this->getAmount() + $this->getSource()->getGomageTaxGiftWrapAmount());

        $helper   = Mage::helper('gomage_checkout/giftwrap');
        $fontSize = $this->getFontSize() ? $this->getFontSize() : 7;
        $title    = $helper->getTitle();

        if ($helper->displayBoth()) {
            $totals = array(
                array(
                    'amount'    => $this->getAmountPrefix() . $amount,
                    'label'     => $helper->__('%s (Excl. Tax)', $title) . ':',
                    'font_size' => $fontSize
                ),
                array(
                    'amount'    => $this->getAmountPrefix() . $amountInclTax,
                    'label'     => $helper->__('%s (Incl. Tax)', $title) . ':',
                    'font_size' => $fontSize
                )
            );
        } elseif ($helper->displayIncluding()) {
            $totals = array(array(
                'amount'    => $this->getAmountPrefix() . $amountInclTax,
                'label'     => $helper->__('%s (Incl. Tax)', $title) . ':',
                'font_size' => $fontSize
            ));
        } else {
            $totals = array(array(
                'amount'    => $this->getAmountPrefix() . $amount,
                'label'     => $helper->__($title) . ':',
                'font_size' => $fontSize
            ));
        }

        return $totals;
    }
}

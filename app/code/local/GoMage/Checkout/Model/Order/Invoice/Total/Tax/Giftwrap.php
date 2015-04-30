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
class GoMage_Checkout_Model_Order_Invoice_Total_Tax_Giftwrap extends Mage_Sales_Model_Order_Invoice_Total_Abstract
{
    public function collect(Mage_Sales_Model_Order_Invoice $invoice)
    {
        $order        = $invoice->getOrder();
        $baseInvoiced = 0;
        $invoiced     = 0;

        foreach ($invoice->getAllItems() as $invoiceItem) {
            if (!$invoiceItem->getQty() || $invoiceItem->getQty() == 0) {
                continue;
            }
            $orderItem = $invoiceItem->getOrderItem();

            if ($orderItem->getData('gomage_gift_wrap') && $orderItem->getBaseGomageTaxGiftWrapAmount()
                && $orderItem->getBaseGomageTaxGiftWrapAmount() != $orderItem->getBaseGomageTaxGiftWrapInvoiced()
            ) {
                $orderItem->setBaseGomageTaxGiftWrapInvoiced($orderItem->getBaseGomageTaxGiftWrapAmount());
                $orderItem->setGomageTaxGiftWrapInvoiced($orderItem->getGomageTaxGiftWrapAmount());
                $baseInvoiced += $orderItem->getBaseGomageTaxGiftWrapAmount() * $invoiceItem->getQty() / $orderItem->getQtyOrdered();
                $invoiced += $orderItem->getGomageTaxGiftWrapAmount() * $invoiceItem->getQty() / $orderItem->getQtyOrdered();
            }
        }

        if ($invoiced > 0 || $baseInvoiced > 0) {
            $order->setBaseGomageTaxGiftWrapInvoiced($order->getBaseGomageTaxGiftWrapInvoiced() + $baseInvoiced);
            $order->setGomageTaxGiftWrapInvoiced($order->getGomageTaxGiftWrapInvoiced() + $invoiced);
            $invoice->setBaseGomageTaxGiftWrapAmount($baseInvoiced);
            $invoice->setGomageTaxGiftWrapAmount($invoiced);
        }

        if (!$invoice->isLast()) {
            $baseTaxAmount = $invoice->getBaseGomageTaxGiftWrapAmount();
            $taxAmount     = $invoice->getGomageTaxGiftWrapAmount();
            $invoice->setBaseTaxAmount($invoice->getBaseTaxAmount() + $baseTaxAmount);
            $invoice->setTaxAmount($invoice->getTaxAmount() + $taxAmount);
            $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $baseTaxAmount);
            $invoice->setGrandTotal($invoice->getGrandTotal() + $taxAmount);
        }

        return $this;
    }
}
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
 * @since        Class available since Release 2.4
 */
class GoMage_Checkout_Model_Order_Invoice_Total_Giftwrap extends Mage_Sales_Model_Order_Invoice_Total_Abstract
{
    public function collect(Mage_Sales_Model_Order_Invoice $invoice)
    {
        $order = $invoice->getOrder();

        $invoiced     = 0;
        $baseInvoiced = 0;
        foreach ($invoice->getAllItems() as $invoiceItem) {
            if (!$invoiceItem->getQty() || $invoiceItem->getQty() == 0) {
                continue;
            }
            $orderItem = $invoiceItem->getOrderItem();
            if ($orderItem->getData('gomage_gift_wrap') && $orderItem->getBaseGomageGiftWrapAmount()
                && $orderItem->getBaseGomageGiftWrapAmount() != $orderItem->getBaseGomageGiftWrapInvoiced()
            ) {
                $orderItem->setBaseGomageGiftWrapInvoiced($orderItem->getBaseGomageGiftWrapAmount());
                $orderItem->setGomageGiftWrapInvoiced($orderItem->getGomageGiftWrapAmount());
                $baseInvoiced += $orderItem->getBaseGomageGiftWrapAmount() * $invoiceItem->getQty() / $orderItem->getQtyOrdered();
                $invoiced += $orderItem->getGomageGiftWrapAmount() * $invoiceItem->getQty() / $orderItem->getQtyOrdered();
            }
        }
        if ($invoiced > 0 || $baseInvoiced > 0) {
            $order->setBaseGomageGiftWrapInvoiced($order->getBaseGomageGiftWrapInvoiced() + $baseInvoiced);
            $order->setGomageGiftWrapInvoiced($order->getGomageGiftWrapInvoiced() + $invoiced);
            $invoice->setBaseGomageGiftWrapAmount($baseInvoiced);
            $invoice->setGomageGiftWrapAmount($invoiced);
        }

        $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $invoice->getBaseGomageGiftWrapAmount());
        $invoice->setGrandTotal($invoice->getGrandTotal() + $invoice->getGomageGiftWrapAmount());

        return $this;

    }
}
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
 * @since        Class available since Release 2.4
 */

class GoMage_Checkout_Model_Order_Invoice_Total_Giftwrap extends Mage_Sales_Model_Order_Invoice_Total_Abstract
{
    public function collect(Mage_Sales_Model_Order_Invoice $invoice)
    {
        $invoice->setGomageGiftWrapAmount(0);
        $invoice->setBaseGomageGiftWrapAmount(0);

        $totalGomageGiftWrapAmount     = 0;
        $baseTotalGomageGiftWrapAmount = 0;

        foreach ($invoice->getAllItems() as $item) {            
            $orderItem = $item->getOrderItem();
            $orderItemGomageGiftWrap      = (float) $orderItem->getGomageGiftWrapAmount();
            $baseOrderItemGomageGiftWrap  = (float) $orderItem->getBaseGomageGiftWrapAmount();
            $orderItemQty       = $orderItem->getQtyOrdered();

            if ($orderItemGomageGiftWrap && $orderItemQty) {
                
                $GomageGiftWrap = $orderItemGomageGiftWrap*$item->getQty()/$orderItemQty;
                $baseGomageGiftWrap = $baseOrderItemGomageGiftWrap*$item->getQty()/$orderItemQty;

                $GomageGiftWrap = $invoice->getStore()->roundPrice($GomageGiftWrap);
                $baseGomageGiftWrap = $invoice->getStore()->roundPrice($baseGomageGiftWrap);
                

                $item->setGomageGiftWrapAmount($GomageGiftWrap);
                $item->setBaseGomageGiftWrapAmount($baseGomageGiftWrap);

                $totalGomageGiftWrapAmount += $GomageGiftWrap;
                $baseTotalGomageGiftWrapAmount += $baseGomageGiftWrap;
            }
        }


        $invoice->setGomageGiftWrapAmount($totalGomageGiftWrapAmount);
        $invoice->setBaseGomageGiftWrapAmount($baseTotalGomageGiftWrapAmount);

        $invoice->setGrandTotal($invoice->getGrandTotal() + $totalGomageGiftWrapAmount);
        $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $baseTotalGomageGiftWrapAmount);
        return $this;
    }
}
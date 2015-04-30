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
class GoMage_Checkout_Model_Order_Creditmemo_Total_Giftwrap extends Mage_Sales_Model_Order_Creditmemo_Total_Abstract
{
    public function collect(Mage_Sales_Model_Order_Creditmemo $creditmemo)
    {
        $order = $creditmemo->getOrder();

        $refunded     = 0;
        $baseRefunded = 0;
        foreach ($creditmemo->getAllItems() as $creditmemoItem) {
            if (!$creditmemoItem->getQty() || $creditmemoItem->getQty() == 0) {
                continue;
            }
            $orderItem = $creditmemoItem->getOrderItem();
            if ($orderItem->getData('gomage_gift_wrap') && $orderItem->getBaseGomageGiftWrapInvoiced()
                && $orderItem->getBaseGomageGiftWrapInvoiced() != $orderItem->getBaseGomageGiftWrapRefunded()
            ) {
                $orderItem->setBaseGomageGiftWrapRefunded($orderItem->getBaseGomageGiftWrapInvoiced());
                $orderItem->setGomageGiftWrapRefunded($orderItem->getGomageGiftWrapInvoiced());
                $baseRefunded += $orderItem->getBaseGomageGiftWrapInvoiced() * $creditmemoItem->getQty() / $orderItem->getQtyOrdered();
                $refunded += $orderItem->getGomageGiftWrapInvoiced() * $creditmemoItem->getQty() / $orderItem->getQtyOrdered();
            }
        }
        if ($refunded > 0 || $baseRefunded > 0) {
            $order->setBaseGomageGiftWrapRefunded($order->getGwItemsBasePriceRefunded() + $baseRefunded);
            $order->setGomageGiftWrapRefunded($order->getGwItemsPriceRefunded() + $refunded);
            $creditmemo->setBaseGomageGiftWrapAmount($baseRefunded);
            $creditmemo->setGomageGiftWrapAmount($refunded);
        }

        $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() + $creditmemo->getBaseGomageGiftWrapAmount());
        $creditmemo->setGrandTotal($creditmemo->getGrandTotal() + $creditmemo->getGomageGiftWrapAmount());

        return $this;
    }
}

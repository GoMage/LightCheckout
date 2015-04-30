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
class GoMage_Checkout_Block_Checkout_Giftwrap extends Mage_Checkout_Block_Total_Default
{

    protected $_template = 'gomage/checkout/giftwrap/totals.phtml';

    public function displayBoth()
    {
        return Mage::helper('gomage_checkout/giftwrap')->displayBoth();
    }

    public function getValues()
    {
        $values = array();
        $total  = $this->getTotal();

        $totals = Mage::helper('gomage_checkout/giftwrap')->getTotals($total);
        foreach ($totals as $total) {
            $values[$total['label']] = $total['value'];
        }

        return $values;
    }

}
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
 * @since        Class available since Release 5.9
 */
class GoMage_Checkout_Helper_Giftwrap extends Mage_Core_Helper_Data
{

    /**
     * @return bool
     */
    public function displayBoth()
    {
        return Mage::helper('gomage_checkout')->getConfigData('gift_wrapping/display') == Mage_Tax_Model_Config::DISPLAY_TYPE_BOTH;
    }

    /**
     * @return bool
     */
    public function displayIncluding()
    {
        return Mage::helper('gomage_checkout')->getConfigData('gift_wrapping/display') == Mage_Tax_Model_Config::DISPLAY_TYPE_INCLUDING_TAX;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return Mage::helper('gomage_checkout')->getConfigData('gift_wrapping/title');
    }

    /**
     * @return float
     */
    public function getPrice()
    {
        return Mage::helper('gomage_checkout')->getConfigData('gift_wrapping/price');
    }

    /**
     * @return int
     */
    public function getTaxClass()
    {
        return Mage::helper('gomage_checkout')->getConfigData('gift_wrapping/tax_class');
    }

    /**
     * Return totals of data object
     *
     * @param  Varien_Object $dataObject
     * @return array
     */
    public function getTotals($dataObject)
    {
        $totals = array();

        $display_both      = $this->displayBoth();
        $display_including = $this->displayIncluding();
        $title             = $this->getTitle();

        if ($display_both || $display_including) {
            if ($display_both) {
                $this->_addTotalToTotals(
                    $totals,
                    'gomage_gift_wrap_excl',
                    $dataObject->getGomageGiftWrapAmount(),
                    $dataObject->getBaseGomageGiftWrapAmount(),
                    $this->__('%s (Excl. Tax)', $title)
                );
            }
            $this->_addTotalToTotals(
                $totals,
                'gomage_gift_wrap_incl',
                $dataObject->getGomageGiftWrapAmount() + $dataObject->getGomageTaxGiftWrapAmount(),
                $dataObject->getBaseGomageGiftWrapAmount() + $dataObject->getBaseGomageTaxGiftWrapAmount(),
                $this->__('%s (Incl. Tax)', $title)
            );
        } else {
            $this->_addTotalToTotals(
                $totals,
                'gomage_gift_wrap',
                $dataObject->getGomageGiftWrapAmount(),
                $dataObject->getBaseGomageGiftWrapAmount(),
                $this->__($title)
            );
        }

        return $totals;
    }

    /**
     * Add total into array totals
     *
     * @param  array $totals
     * @param  string $code
     * @param  decimal $value
     * @param  decimal $baseValue
     * @param  string $label
     */
    protected function _addTotalToTotals(&$totals, $code, $value, $baseValue, $label)
    {
        if ($value == 0 && $baseValue == 0) {
            return;
        }
        $total    = array(
            'code'       => $code,
            'value'      => $value,
            'base_value' => $baseValue,
            'label'      => $label
        );
        $totals[] = $total;
    }

}

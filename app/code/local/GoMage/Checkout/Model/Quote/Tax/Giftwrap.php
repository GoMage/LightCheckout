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
class GoMage_Checkout_Model_Quote_Tax_Giftwrap extends Mage_Sales_Model_Quote_Address_Total_Abstract
{

    /** @var Mage_Tax_Model_Calculation */
    protected $_tax_calculation;

    /** @var array */
    protected $_request;

    /** @var float */
    protected $_rate;

    /** @var  GoMage_Checkout_Helper_Giftwrap */
    protected $_helper;

    public function __construct()
    {
        $this->setCode('gomage_tax_gift_wrap');
        $this->_helper          = Mage::helper('gomage_checkout/giftwrap');
        $this->_tax_calculation = Mage::getSingleton('tax/calculation');
    }

    /**
     * Collect gift wrapping tax totals
     *
     * @param   Mage_Sales_Model_Quote_Address $address
     * @return  Enterprise_GiftWrapping_Model_Total_Quote_Tax_Giftwrapping
     */
    public function collect(Mage_Sales_Model_Quote_Address $address)
    {
        parent::collect($address);

        $items = $address->getAllNonNominalItems();
        if (!count($items)) {
            return $this;
        }
        if ($address->getAddressType() != Mage_Sales_Model_Quote_Address::TYPE_SHIPPING) {
            return $this;
        }

        /** @var Mage_Sales_Model_Quote $quote */
        $quote = $address->getQuote();

        $this->_initRate($address);

        $gomage_tax_gift_wrap_amount      = 0;
        $base_gomage_tax_gift_wrap_amount = 0;

        foreach ($items as $item) {
            if (!$item->getData('gomage_gift_wrap')) {
                $item->setGomageTaxGiftWrapAmount(0);
                $item->setBaseGomageTaxGiftWrapAmount(0);
            } else {
                $gw_tax_amount      = $this->_calcTaxAmount($item->getGomageGiftWrapAmount());
                $base_gw_tax_amount = $this->_calcTaxAmount($item->getBaseGomageGiftWrapAmount());

                $item->setGomageTaxGiftWrapAmount($gw_tax_amount);
                $item->setBaseGomageTaxGiftWrapAmount($base_gw_tax_amount);

                $gomage_tax_gift_wrap_amount += $gw_tax_amount;
                $base_gomage_tax_gift_wrap_amount += $base_gw_tax_amount;
            }
        }

        $address->setGomageTaxGiftWrapAmount($gomage_tax_gift_wrap_amount);
        $address->setBaseGomageTaxGiftWrapAmount($base_gomage_tax_gift_wrap_amount);

        $address->setTaxAmount($address->getTaxAmount() + $gomage_tax_gift_wrap_amount);
        $address->setBaseTaxAmount($address->getBaseTaxAmount() + $base_gomage_tax_gift_wrap_amount);
        $address->setGrandTotal($address->getGrandTotal() + $gomage_tax_gift_wrap_amount);
        $address->setBaseGrandTotal($address->getBaseGrandTotal() + $base_gomage_tax_gift_wrap_amount);

        if ($quote->getIsNewGomagTaxeGiftWrapCollecting()) {
            $quote->setGomageTaxGiftWrapAmount(0);
            $quote->setBaseGomageTaxGiftWrapAmount(0);
            $quote->setIsNewGomagTaxeGiftWrapCollecting(false);
        }

        $quote->setGomageTaxGiftWrapAmount($address->getGomageTaxGiftWrapAmount() + $quote->getGomageTaxGiftWrapAmount());
        $quote->setBaseGomageTaxGiftWrapAmount($address->getBaseGomageTaxGiftWrapAmount() + $quote->getBaseGomageTaxGiftWrapAmount());

        $applied = Mage::getSingleton('tax/calculation')->getAppliedRates($this->_request);
        $this->_saveAppliedTaxes($address, $applied, $gomage_tax_gift_wrap_amount, $base_gomage_tax_gift_wrap_amount, $this->_rate);

        return $this;
    }

    /**
     * Calculate tax for amount
     *
     * @param   float $price
     * @return  float
     */
    protected function _calcTaxAmount($price)
    {
        return $this->_tax_calculation->calcTaxAmount($price, $this->_rate);
    }

    protected function _initRate($address)
    {
        $store          = $address->getQuote()->getStore();
        $billingAddress = $address->getQuote()->getBillingAddress();
        $custTaxClassId = $address->getQuote()->getCustomerTaxClassId();
        $this->_request = $this->_tax_calculation->getRateRequest(
            $address,
            $billingAddress,
            $custTaxClassId,
            $store
        );
        $this->_request->setProductClassId($this->_helper->getTaxClass());
        $this->_rate = $this->_tax_calculation->getRate($this->_request);
        return $this;
    }

    /**
     * Collect applied tax rates information on address level
     *
     * @param Mage_Sales_Model_Quote_Address $address
     * @param array $applied
     * @param float $amount
     * @param float $baseAmount
     * @param float $rate
     * @return Enterprise_GiftWrapping_Model_Total_Quote_Tax_Giftwrapping
     */
    protected function _saveAppliedTaxes($address, $applied, $amount, $baseAmount, $rate)
    {
        $previouslyAppliedTaxes = $address->getAppliedTaxes();
        $process                = count($previouslyAppliedTaxes);

        foreach ($applied as $row) {
            if ($row['percent'] == 0) {
                continue;
            }
            if (!isset($previouslyAppliedTaxes[$row['id']])) {
                $row['process']                     = $process;
                $row['amount']                      = 0;
                $row['base_amount']                 = 0;
                $previouslyAppliedTaxes[$row['id']] = $row;
            }

            if (!is_null($row['percent'])) {
                $row['percent'] = $row['percent'] ? $row['percent'] : 1;
                $rate           = $rate ? $rate : 1;

                $appliedAmount     = $amount / $rate * $row['percent'];
                $baseAppliedAmount = $baseAmount / $rate * $row['percent'];
            } else {
                $appliedAmount     = 0;
                $baseAppliedAmount = 0;
                foreach ($row['rates'] as $rate) {
                    $appliedAmount += $rate['amount'];
                    $baseAppliedAmount += $rate['base_amount'];
                }
            }

            if ($appliedAmount || $previouslyAppliedTaxes[$row['id']]['amount']) {
                $previouslyAppliedTaxes[$row['id']]['amount'] += $appliedAmount;
                $previouslyAppliedTaxes[$row['id']]['base_amount'] += $baseAppliedAmount;
            } else {
                unset($previouslyAppliedTaxes[$row['id']]);
            }
        }
        $address->setAppliedTaxes($previouslyAppliedTaxes);
        return $this;
    }

    /**
     * @param Mage_Sales_Model_Quote_Address $address
     * @return GoMage_Checkout_Model_Quote_Tax_Giftwrap
     */
    public function fetch(Mage_Sales_Model_Quote_Address $address)
    {
        $address->addTotal(array(
                'code'                             => 'gomage_gift_wrap',
                'gomage_gift_wrap_amount'          => $address->getGomageGiftWrapAmount(),
                'base_gomage_gift_wrap_amount'     => $address->getBaseGomageGiftWrapAmount(),
                'gomage_tax_gift_wrap_amount'      => $address->getGomageTaxGiftWrapAmount(),
                'base_gomage_tax_gift_wrap_amount' => $address->getBaseGomageTaxGiftWrapAmount(),
            )
        );
        return $this;
    }

}

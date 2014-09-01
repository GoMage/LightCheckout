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
 * @since        Class available since Release 1.0
 */

class GoMage_Checkout_Block_Onepage_Billing extends GoMage_Checkout_Block_Onepage_Abstract
{

    protected $prefix = 'billing';

    public function customerHasAddresses()
    {
        if (intval($this->helper->getConfigData('address_fields/address_book'))) {
            return parent::customerHasAddresses();
        }
        return false;
    }

    protected function _prepareLayout()
    {
        return parent::_prepareLayout();
    }

    public function getCountries()
    {
        return Mage::getResourceModel('directory/country_collection')->loadByStore();
    }

    public function getMethod()
    {
        return $this->getQuote()->getCheckoutMethod();
    }

    function getAddress()
    {
        return $this->getQuote()->getBillingAddress();
    }

    public function getFirstname()
    {
        $firstname = $this->getAddress()->getFirstname();
        if (empty($firstname) && $this->getQuote()->getCustomer()) {
            return $this->getQuote()->getCustomer()->getFirstname();
        }
        return $firstname;
    }

    public function getLastname()
    {
        $lastname = $this->getAddress()->getLastname();
        if (empty($lastname) && $this->getQuote()->getCustomer()) {
            return $this->getQuote()->getCustomer()->getLastname();
        }
        return $lastname;
    }

    public function shippingAsBilling()
    {
        if (null === $this->getCheckout()->getShippingSameAsBilling()) {
            return true;
        }
        return (bool)$this->getCheckout()->getShippingSameAsBilling();
    }

    public function canShip()
    {
        if (!$this->getQuote()->isVirtual()) {
            if (intval($this->getConfigData('general/different_shipping_enabled'))) {
                return true;
            }

        }
        return false;
    }

    public function getCountryHtmlSelect($type)
    {
        $countryId = $this->getAddress()->getCountryId();

        if (is_null($countryId)) {
            $countryId = $this->getConfigData('general/default_country');
        }

        if (is_null($countryId)) {
            $countryId = Mage::getStoreConfig('general/country/default');
        }

        $options = $this->getCountryOptions();

        $options[0] = array('value' => '', 'label' => $this->__('--Please Select--'));

        $select = $this->getLayout()->createBlock('core/html_select')
            ->setName($type . '[country_id]')
            ->setId($type . '_country_id')
            ->setTitle(Mage::helper('checkout')->__('Country'))
            ->setClass('required-entry absolute-advice')
            ->setValue($countryId)
            ->setOptions($options);

        return $select->getHtml();
    }


    public function getAutoRegistration()
    {
        return intval($this->getConfigData('registration/auto'));
    }

    public function isReferralBlockShow()
    {
        return Mage::getStoreConfig('rewards/general/layoutsactive') &&
        Mage::getStoreConfig('rewards/referral/show_in_onepage_checkout') &&
        (Mage::getStoreConfig('rewards/referral/show_referral_email') || Mage::getStoreConfig('rewards/referral/show_referral_code'));
    }

    public function getReferralLabel()
    {
        if (Mage::getStoreConfig('rewards/referral/show_referral_email') && !Mage::getStoreConfig('rewards/referral/show_referral_code')) {
            return $this->__('Referral E-mail');
        } elseif (!Mage::getStoreConfig('rewards/referral/show_referral_email') && Mage::getStoreConfig('rewards/referral/show_referral_code')) {
            return $this->__('Referral Code');
        }

        return $this->__('Referral E-mail or Code');
    }

}
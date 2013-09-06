<?php
/**
 * GoMage LightCheckout Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2010-2013 GoMage (http://www.gomage.com)
 * @author       GoMage
 * @license      http://www.gomage.com/license-agreement/  Single domain license
 * @terms of use http://www.gomage.com/terms-of-use
 * @version      Release: 5.0
 * @since        Class available since Release 5.0
 */

class GoMage_Checkout_Model_Type_Onestep_Calculator extends Varien_Object
{

    const CACHE_TAG    = 'gomage_checkout_ajax_request';
    const CACHE_PREFIX = 'gomage_checkout_block_';

    public $result;
    private $request;

    public function __construct($request)
    {
        $this->request = $request;
        $this->result  = new stdClass();
        $this->initialize();
    }

    public function initialize()
    {
        $this->result->error = false;
    }

    public function setResultParam($key, $value)
    {
        $this->result->{$key} = $value;
        return $this;
    }

    public function getCacheBlocks()
    {
        return array('shippings', 'payments', 'review');
    }

    public function cleanCache()
    {
        Mage::app()->getCache()->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, array(self::CACHE_TAG));
        return $this;
    }

    public function generateResult(array $blocks)
    {
        foreach ($blocks as $block) {
            $block_html = null;
            switch ($block) {
                case 'shippings':
                    $block_html = $this->_getLayoutHrml('checkout_onepage_shippingmethod');
                    break;
                case 'payments':
                    $block_html = $this->_getLayoutHrml('gomage_checkout_onepage_paymentmethod');
                    break;
                case 'review':
                    $block_html = $this->_getLayoutHrml('gomage_checkout_onepage_review');
                    break;
                case 'centinel':
                    $block_html = $this->_getLayoutHrml('gomage_checkout_onepage_centinel');
                    break;
                case 'methods':
                    $block_html = $this->_getLayoutHrml('gomage_checkout_onepage_methods');
                    break;
                case 'toplinks':
                    $block_html = $this->_getTopLinksHtml();
                    break;
                case 'content_billing':
                    $block_html = $this->_getContentBilling();
                    break;
                case 'content_shipping':
                    $block_html = $this->_getContentShipping();
                    break;
            }

            if ($block_html) {
                $this->setResultParam($block, $block_html);
            }

        }
        return $this;
    }

    protected function _getLayoutHrml($handles)
    {
        $layout = Mage::getModel('core/layout');
        $layout->getUpdate()->load($handles);
        $layout->generateXml()->generateBlocks();
        return $layout->getOutput();
    }

    protected function _getTopLinksHtml()
    {
        $layout = Mage::getSingleton('core/layout');

        if (Mage::helper('gomage_checkout')->getIsAnymoreVersion(1, 8)) {
            $top_links = $layout->createBlock('checkout/cart_sidebar', 'glc.top.links');
            $top_links->setTemplate('checkout/cart/cartheader.phtml');
        } else {
            $top_links          = $layout->createBlock('page/template_links', 'glc.top.links');
            $checkout_cart_link = $layout->createBlock('checkout/links', 'checkout_cart_link');
            $top_links->setChild('checkout_cart_link', $checkout_cart_link);
            if (method_exists($top_links, 'addLinkBlock')) {
                $top_links->addLinkBlock('checkout_cart_link');
            }
            $checkout_cart_link->addCartLink();
        }

        return $top_links->renderView();
    }

    protected function _getContentBilling()
    {
        $layout = Mage::getModel('core/layout');
        $layout->getUpdate()->load('gomage_checkout_onepage_index');
        $layout->generateXml()->generateBlocks();

        $html = $layout->getBlock('checkout.onepage.address.billing')->toHtml();
        return trim($html);
    }

    protected function _getContentShipping()
    {
        $layout = Mage::getModel('core/layout');
        $layout->getUpdate()->load('gomage_checkout_onepage_index');
        $layout->generateXml()->generateBlocks();

        $html = $layout->getBlock('checkout.onepage.address.shipping')->toHtml();
        return trim($html);
    }

    public function calcShippings($quote)
    {

        $address = $quote->getShippingAddress();
        $address->setCollectShippingRates(true)->collectShippingRates()->save();

        $shipping_method         = $address->getShippingMethod();
        $default_shipping_method = '';

        $rates = ( array )$address->getGroupedAllShippingRates();

        if (count($rates) == 1) {
            foreach ($rates as $rate_code => $methods) {
                if (count($methods) == 1) {
                    foreach ($methods as $method) {
                        $default_shipping_method = $method->getCode();
                    }
                }
            }
        }

        if ($default_shipping_method && ($default_shipping_method != $shipping_method)) {
            $address->setShippingMethod($default_shipping_method);
        }

        $quote->setTotalsCollectedFlag(false)->collectTotals()->save();

    }

    public function prepareResult()
    {

        if (isset($this->result->methods)) {
            unset($this->result->shippings);
            unset($this->result->payments);
        }

        foreach ($this->getCacheBlocks() as $block) {
            $block_html = $this->result->{$block};
            if ($block_html) {
                $cache_block_html = Mage::app()->getCache()->load(self::CACHE_PREFIX . $block);
                if ($cache_block_html == $block_html) {
                    unset($this->result->{$block});
                } else {
                    Mage::app()->getCache()->save($block_html, self::CACHE_PREFIX . $block, array(self::CACHE_TAG));
                }
            }
        }

        if (isset($this->result->toplinks) && Mage::helper('gomage_checkout')->getIsAnymoreVersion(1, 8)) {
            if ($this->isProCartExists()) {
                $this->result->cart_sidebar = Mage::getModel('gomage_procart/observer')->getCartSidebar();
            }
        }

    }

    private function isProCartExists()
    {
        $modules = array_keys((array)Mage::getConfig()->getNode('modules')->children());
        return in_array('GoMage_Procart', $modules);

    }

}

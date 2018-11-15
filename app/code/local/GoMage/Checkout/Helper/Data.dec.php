<?php
/**
 * GoMage LightCheckout Extension
 *
 * @category     Extension
 * @copyright    Copyright (c) 2010-2018 GoMage (https://www.gomage.com)
 * @author       GoMage
 * @license      https://www.gomage.com/license-agreement/  Single domain license
 * @terms of use https://www.gomage.com/terms-of-use
 * @version      Release: 5.9.4
 * @since        Class available since Release 1.0
 */

require_once(Mage::getBaseDir('lib') . DS . 'GoMage' . DS . 'MobileDetect' . DS . 'Checkout_Mobile_Detect.php');

class GoMage_Checkout_Helper_Data extends Mage_Core_Helper_Abstract
{

    protected $mode;
    protected $country_id;

    public function getConfigData($node)
    {
        return Mage::getStoreConfig('gomage_checkout/' . $node);
    }

    public function getCheckoutMode()
    {
        if (is_null($this->mode)) {
            if (Mage::getSingleton('gomage_checkout/type_onestep')->getQuote()->isAllowedGuestCheckout()) {
                $this->mode = intval($this->getConfigData('registration/mode'));
            } else {
                $this->mode = 1;
            }
        }
        return $this->mode;
    }

    public function getAllStoreDomains()
    {

        $domains = array();

        foreach (Mage::app()->getWebsites() as $website) {

            $url = $website->getConfig('web/unsecure/base_url');
            if ($domain = trim(preg_replace('/^.*?\\/\\/(.*)?\\//', '$1', $url))) {
                $domains[] = $domain;
            }

            $url = $website->getConfig('web/secure/base_url');
            if ($domain = trim(preg_replace('/^.*?\\/\\/(.*)?\\//', '$1', $url))) {
                $domains[] = $domain;
            }

        }

        return array_unique($domains);

    }

    public function getAvailableWebsites()
    {
        return $this->_w();
    }

    public function getAvailavelWebsites()
    {
        return $this->getAvailableWebsites();
    }

    public function isAvailableWebsite()
    {
        return in_array(Mage::app()->getStore()->getWebsiteId(), $this->getAvailableWebsites());
    }

    protected function _w()
    {

        if (!Mage::getStoreConfig('gomage_activation/lightcheckout/installed') ||
            (intval(Mage::getStoreConfig('gomage_activation/lightcheckout/count')) > 10)
        ) {
            return array();
        }

        $time_to_update = 60 * 60 * 24 * 15;

        $r = Mage::getStoreConfig('gomage_activation/lightcheckout/ar');
        $t = Mage::getStoreConfig('gomage_activation/lightcheckout/time');
        $s = Mage::getStoreConfig('gomage_activation/lightcheckout/websites');

        $last_check = str_replace($r, '', Mage::helper('core')->decrypt($t));

        $allsites = explode(',', str_replace($r, '', Mage::helper('core')->decrypt($s)));
        $allsites = array_diff($allsites, array(""));

        if (($last_check + $time_to_update) < time()) {
            $this->a(Mage::getStoreConfig('gomage_activation/lightcheckout/key'),
                intval(Mage::getStoreConfig('gomage_activation/lightcheckout/count')),
                implode(',', $allsites)
            );
        }

        return $allsites;

    }

    public function a($k, $c = 0, $s = '')
    {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, sprintf('https://www.gomage.com/index.php/gomage_downloadable/key/check'));
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, 'key=' . urlencode($k) . '&sku=lightcheckout&domains=' . urlencode(implode(',', $this->getAllStoreDomains())) . '&ver=' . urlencode('5.9.4'));
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

        $content = curl_exec($ch);

        try {
            $r = Zend_Json::decode($content);
        } catch (\Exception $e) {
            $r = array();
        }

        $e = Mage::helper('core');

        if (empty($r)) {

            $value1 = Mage::getStoreConfig('gomage_activation/lightcheckout/ar');

            $groups = array(
                'lightcheckout' => array(
                    'fields' => array(
                        'ar'       => array(
                            'value' => $value1
                        ),
                        'websites' => array(
                            'value' => (string)Mage::getStoreConfig('gomage_activation/lightcheckout/websites')
                        ),
                        'time'     => array(
                            'value' => (string)$e->encrypt($value1 . (time() - (60 * 60 * 24 * 15 - 1800)) . $value1)
                        ),
                        'count'    => array(
                            'value' => $c + 1)
                    )
                )
            );

            Mage::getModel('adminhtml/config_data')
                ->setSection('gomage_activation')
                ->setGroups($groups)
                ->save();

            Mage::getConfig()->reinit();
            Mage::app()->reinitStores();

            return;
        }

        $value1 = '';
        $value2 = '';

        if (isset($r['d']) && isset($r['c'])) {
            $value1 = $e->encrypt(base64_encode(Zend_Json::encode($r)));

            if (!$s) {
                $s = Mage::getStoreConfig('gomage_activation/lightcheckout/websites');
            }

            $s = array_slice(explode(',', $s), 0, $r['c']);

            $value2 = $e->encrypt($value1 . implode(',', $s) . $value1);

        }
        $groups = array(
            'lightcheckout' => array(
                'fields' => array(
                    'ar'        => array(
                        'value' => $value1
                    ),
                    'websites'  => array(
                        'value' => (string)$value2
                    ),
                    'time'      => array(
                        'value' => (string)$e->encrypt($value1 . time() . $value1)
                    ),
                    'installed' => array(
                        'value' => 1
                    ),
                    'count'     => array(
                        'value' => 0)
                )
            )
        );

        Mage::getModel('adminhtml/config_data')
            ->setSection('gomage_activation')
            ->setGroups($groups)
            ->save();

        Mage::getConfig()->reinit();
        Mage::app()->reinitStores();

    }

    public function ga()
    {
        $ar = base64_decode(Mage::helper('core')->decrypt(Mage::getStoreConfig('gomage_activation/lightcheckout/ar')));
        return $ar ? Zend_Json::decode($ar) : array();
    }

    public function getGeoipRecord()
    {
        return GeoIP_Core::getInstance(Mage::getBaseDir('media') . "/geoip/GeoLiteCity.dat", GeoIP_Core::GEOIP_STANDARD)->geoip_record_by_addr(Mage::helper('core/http')->getRemoteAddr());
    }

    public function getDefaultCountryId()
    {
        if (is_null($this->country_id)) {
            if (Mage::getStoreConfig('gomage_checkout/geoip/geoip_enabled') && file_exists(Mage::getBaseDir('media') . "/geoip/GeoLiteCity.dat") && extension_loaded('mbstring')) {
                try {
                    $this->country_id = GeoIP_Core::getInstance(Mage::getBaseDir('media') . "/geoip/GeoLiteCity.dat", GeoIP_Core::GEOIP_STANDARD)->geoip_country_code_by_addr(Mage::helper('core/http')->getRemoteAddr());
                } catch (Exception $e) {
                    echo $e;
                }
            }

            if (!$this->country_id) {
                $this->country_id = Mage::getStoreConfig('gomage_checkout/general/default_country');
                if (!$this->country_id) {
                    $this->country_id = Mage::getStoreConfig('general/country/default');
                }
            }
        }
        return $this->country_id;
    }

    public function getDefaultShippingMethod()
    {
        $address = Mage::getSingleton('checkout/session')->getQuote()->getShippingAddress();
        $address->setCollectShippingRates(true)->collectShippingRates();
        $rates = $address->getGroupedAllShippingRates();

        if (count($rates) == 1) {
            foreach ($rates as $rate_code => $methods) {
                if (count($methods) == 1) {
                    foreach ($methods as $method) {

                        return $method->getCode();
                    }
                } else {
                    return $this->getConfigData('general/default_shipping_method');
                }
            }
        } else {
            return $this->getConfigData('general/default_shipping_method');
        }
    }

    public function getDefaultPaymentMethod()
    {
        return $this->getConfigData('general/default_payment_method');
    }

    public function getActivePaymentMethods($store = null)
    {
        $methods = array();
        $config  = Mage::getStoreConfig('payment', $store);

        foreach ($config as $code => $methodConfig) {

            if (isset($methodConfig['model']) && $methodConfig['model']) {
                if (isset($methodConfig['group']) && $methodConfig['group'] == 'mbookers' && Mage::getStoreConfigFlag('moneybookers/' . $code . '/active', $store)) {
                    $method          = $this->_getPaymentMethod($code, $methodConfig);
                    $method['group'] = 'mbookers';
                    $methods[$code]  = $method;
                } elseif ($methodConfig['model'] == 'googlecheckout/payment') {
                    if (Mage::getStoreConfigFlag('google/checkout/active', $store)) {
                        $method         = $this->_getPaymentMethod($code, $methodConfig);
                        $methods[$code] = $method;
                    }
                } elseif (isset($methodConfig['group']) && $methodConfig['group'] == 'payone') {
                    $method = $this->_getPaymentMethod($code, $methodConfig);
                    if ($method && $method->isAvailable()) {
                        $methods[$code] = $method;
                    }
                } elseif (Mage::getStoreConfigFlag('payment/' . $code . '/active', $store)) {
                    $method          = $this->_getPaymentMethod($code, $methodConfig);
                    $method['group'] = '';
                    $methods[$code]  = $method;
                }
            }
        }

        return $methods;
    }

    protected function _getPaymentMethod($code, $config, $store = null)
    {
        $modelName = $config['model'];
        $method    = Mage::getModel($modelName);

        if ($method) {
            $method->setId($code)->setStore($store);
        }
        return $method;
    }

    public function getVatBaseCountryMode()
    {
        return $this->getConfigData('vat/base_country');
    }

    public function getVatWithinCountryMode()
    {
        return $this->getConfigData('vat/if_not_base_country');
    }

    public function getTaxCountries()
    {
        $rule_ids = Mage::helper('gomage_checkout')->getConfigData('vat/rule');
        $rule_ids = array_filter(explode(',', $rule_ids));
        if (count($rule_ids)) {
            $resource   = Mage::getSingleton('core/resource');
            $connection = $resource->getConnection('read');
            $q          = sprintf('SELECT DISTINCT(`tax_country_id`) FROM `%s` WHERE `tax_calculation_rate_id` IN (SELECT `tax_calculation_rate_id` FROM `%s` WHERE `tax_calculation_rule_id` in (%s) )',
                $resource->getTableName('tax_calculation_rate'),
                $resource->getTableName('tax_calculation'),
                implode(',', $rule_ids)
            );
            return (array)$connection->fetchCol($q);
        }

        return array();
    }

    public function getIsAnymoreVersion($major, $minor, $revision = 0)
    {
        $version_info = Mage::getVersionInfo();

        if ($version_info['major'] > $major) {
            return true;
        } elseif ($version_info['major'] == $major) {
            if ($version_info['minor'] > $minor) {
                return true;
            } elseif ($version_info['minor'] == $minor) {
                if ($version_info['revision'] >= $revision) {
                    return true;
                }
            }
        }

        return false;
    }

    public function isEnterprise()
    {
        $modules = array_keys((array)Mage::getConfig()->getNode('modules')->children());
        return in_array('Enterprise_Enterprise', $modules);
    }

    public function isCompatibleDevice()
    {

        $detect = new Checkout_Mobile_Detect();
        if (!$detect->isMobile()) {
            return (bool)$this->getConfigData('device/desktop');
        }
        if ($detect->isTablet()) {
            $devices = explode(',', $this->getConfigData('device/tablet'));
        } else {
            $devices = explode(',', $this->getConfigData('device/smartphone'));
        }

        if ($detect->isAndroidOS()) {
            return in_array(GoMage_Checkout_Model_Adminhtml_System_Config_Source_Device::ANDROID, $devices);
        }
        if ($detect->isBlackBerryOS()) {
            return in_array(GoMage_Checkout_Model_Adminhtml_System_Config_Source_Device::BLACKBERRY, $devices);
        }
        if ($detect->isiOS()) {
            return in_array(GoMage_Checkout_Model_Adminhtml_System_Config_Source_Device::IOS, $devices);
        }

        return in_array(GoMage_Checkout_Model_Adminhtml_System_Config_Source_Device::OTHER, $devices);

    }

    public function isLefttoRightWrite()
    {
        return in_array(Mage::app()->getLocale()->getLocaleCode(), array('ar_DZ', 'ar_EG', 'ar_KW', 'ar_MA',
                'ar_SA', 'he_IL', 'fa_IR')
        );
    }

    public function getCountriesStatesRequired()
    {
        $result = array();

        if ($this->getConfigData('address_fields/region') == 'req') {
            $country_collection = Mage::helper('directory')->getCountryCollection();
            foreach ($country_collection as $country) {
                $result[] = $country->getCountryId();
            }
        }
        return Mage::helper('core')->jsonEncode($result);

    }

    public function formatColor($value)
    {
        if ($value = preg_replace('/[^a-zA-Z0-9\s]/', '', $value)) {
            $value = '#' . $value;
        }
        return $value;
    }

    public function notify()
    {

        $frequency = intval(Mage::app()->loadCache('gomage_notifications_frequency'));
        if (!$frequency) {
            $frequency = 24;
        }
        $last_update = intval(Mage::app()->loadCache('gomage_notifications_last_update'));

        if (($frequency * 60 * 60 + $last_update) > time()) {
            return false;
        }

        $timestamp = $last_update;
        if (!$timestamp) {
            $timestamp = time();
        }

        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, sprintf('https://www.gomage.com/index.php/gomage_notification/index/data'));
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, 'sku=lightcheckout&timestamp=' . $timestamp . '&ver=' . urlencode('5.9.2'));
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

            $content = curl_exec($ch);

            try {
                $result = Zend_Json::decode($content);
            } catch (\Exception $e) {
                $result = array();
            }

            if ($result && isset($result['frequency']) && ($result['frequency'] != $frequency)) {
                Mage::app()->saveCache($result['frequency'], 'gomage_notifications_frequency');
            }

            if ($result && isset($result['data'])) {
                if (!empty($result['data'])) {
                    Mage::getModel('adminnotification/inbox')->parse($result['data']);
                }
            }
        } catch (Exception $e) {
        }

        Mage::app()->saveCache(time(), 'gomage_notifications_last_update');

    }


}

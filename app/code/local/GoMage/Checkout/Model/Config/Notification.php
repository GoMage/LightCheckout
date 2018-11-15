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
class GoMage_Checkout_Model_Config_Notification extends Mage_Core_Model_Config_Data
{
    /**
     * Factory instance
     *
     * @var Mage_Core_Model_Factory
     */
    protected $_factory;

    /**
     * Initialize class instance
     *
     * @param array $args
     */
    public function __construct(array $args = array())
    {
        $this->_factory = !empty($args['factory']) ? $args['factory'] : Mage::getSingleton('core/factory');
        parent::__construct($args);
    }

    /**
     * Get config model
     *
     * @return Mage_Core_Model_Config_Data
     */
    protected function _getConfig()
    {
        return $this->_factory->getModel('core/config_data');
    }

    /**
     * Prepare and store cron settings after save
     *
     * @return Mage_Tax_Model_Config_Notification
     */
    protected function _afterSave()
    {
        if ($this->isValueChanged()) {
            $this->_resetNotificationFlag(Mage_Tax_Model_Config::XML_PATH_TAX_NOTIFICATION_DISCOUNT);
            $this->_resetNotificationFlag(Mage_Tax_Model_Config::XML_PATH_TAX_NOTIFICATION_PRICE_DISPLAY);
        }
        return parent::_afterSave();
    }

    /**
     * Reset flag for showing tax notifications
     *
     * @param string $path
     * @return GoMage_Checkout_Model_Config_Notification
     */
    protected function _resetNotificationFlag($path)
    {
        $this->_getConfig()
            ->load($path, 'path')
            ->setValue(0)
            ->setPath($path)
            ->save();
        return $this;
    }
}

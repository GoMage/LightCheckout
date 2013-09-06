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
 * @since        Class available since Release 4.0
 */

class GoMage_Checkout_Block_Config_Block extends Mage_Core_Block_Template {
	
	protected $config_block;
	protected $ifconfig;
	protected $call_methods = array();
	
	protected function _toHtml() {
		if ($this->config_block) {
			if ($this->ifconfig && ($configPath = ( string ) $this->ifconfig)) {
				if (! Mage::getStoreConfigFlag($configPath)) {
					return null;
				}
			}
			try {
				$block = $this->getLayout()->createBlock($this->config_block);
				foreach ($this->call_methods as $method) {
					call_user_func_array(array($block, $method['method']), $method['params']);
				}
			}
			catch (Exception $e) {
				$block = null;
			}
			if ($block) {
				return $block->toHtml();
			}
		}
		return null;
	}
	
	public function setConfigBlock($config_block) {
		$this->config_block = $config_block;
		return $this;
	}
	
	public function setIfConfig($ifconfig) {
		$this->ifconfig = $ifconfig;
		return $this;
	}
	
	public function setCallMethod($method, $parms = array()) {
		$this->call_methods[] = array('method' => $method, 'params' => $parms);
		return $this;
	}

}
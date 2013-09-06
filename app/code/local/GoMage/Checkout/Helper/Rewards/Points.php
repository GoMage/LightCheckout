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

class GoMage_Checkout_Helper_Rewards_Points extends Mage_Core_Helper_Data {
	
	public function getPoints($item) {
		$html = '';		
		if (Mage::getStoreConfig('rewards/general/layoutsactive')) {
			$layout = Mage::getSingleton('core/layout');
			$item_points = $layout->getBlock('gomage_checkout_item_points');
			if (! $item_points) {				
				try {
					$item_points = $layout->createBlock('rewards/checkout_cart_item_points', 'gomage_checkout_item_points')
										->setTemplate('rewards/checkout/cart/item/points.phtml');
					
					$layout->createBlock('rewards/checkout_cart_item_points_earning', 'gomage_checkout_item_points_earning')
										->setTemplate('rewards/checkout/cart/item/points/earning.phtml')
										->setPriority(20);
					$item_points->setChild('gomage_checkout_item_points_earning', 'gomage_checkout_item_points_earning');

					$layout->createBlock('rewards/checkout_cart_item_points_spending', 'gomage_checkout_item_points_spending')
										->setTemplate('rewardsspend/checkout/cart/item/points.phtml')
										->setPriority(10);
					$item_points->setChild('gomage_checkout_item_points_spending', 'gomage_checkout_item_points_spending');					
				}
				catch (Exception $e) {
					return $html;
				}
			}
			$item_points->setItem($item);
			$html = $item_points->toHtml();
		}
		
		return $html;
	}

}

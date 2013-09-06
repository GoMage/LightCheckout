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
 * @since        Class available since Release 1.0
 */
	
class GoMage_Checkout_Model_Adminhtml_System_Config_Source_Taxrules{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
    	
    	$tax_calculation_rule_table = Mage::getSingleton('core/resource')->getTableName('tax/tax_calculation_rule');
    	$q = "SELECT `tax_calculation_rule_id`, `code` FROM {$tax_calculation_rule_table}";
		$rules = Mage::getSingleton('core/resource')->getConnection('read')->fetchPairs($q);
		
		$options = array();
		$options[] = array(
                   'value' => '',
                   'label' => ''
                );             
		
        foreach ($rules as $code => $name) {            
                $options[] = array(
                   'value' => $code,
                   'label' => $name
                );            
        }

        return $options;
		
    }

}
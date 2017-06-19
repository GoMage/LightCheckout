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
 * @since        Class available since Release 1.0
 */
class GoMage_Checkout_Model_Mysql4_Tax_Calculation extends Mage_Tax_Model_Mysql4_Calculation
{

    protected function _getRates($request)
    {

        $storeId = Mage::app()->getStore($request->getStore())->getId();

        $select = $this->_getReadAdapter()->select();
        $select
            ->from(array('main_table' => $this->getMainTable()))
            ->where('customer_tax_class_id = ?', $request->getCustomerClassId());
        if ($request->getProductClassId()) {
            $select->where('product_tax_class_id IN (?)', $request->getProductClassId());
        }

        if ($ruleIds = $request->getDisableByRule()) {

            $select->join(
                array('rule' => $this->getTable('tax/tax_calculation_rule')),
                sprintf('rule.tax_calculation_rule_id = main_table.tax_calculation_rule_id AND rule.tax_calculation_rule_id not in (%s)', $ruleIds),
                array('rule.priority', 'rule.position')
            );

        } else {

            $select->join(
                array('rule' => $this->getTable('tax/tax_calculation_rule')),
                'rule.tax_calculation_rule_id = main_table.tax_calculation_rule_id',
                array('rule.priority', 'rule.position')
            );


        }

        $select->join(
            array('rate' => $this->getTable('tax/tax_calculation_rate')),
            'rate.tax_calculation_rate_id = main_table.tax_calculation_rate_id',
            array('value' => 'rate.rate', 'rate.tax_country_id', 'rate.tax_region_id', 'rate.tax_postcode', 'rate.tax_calculation_rate_id', 'rate.code')
        );

        $select->joinLeft(
            array('title_table' => $this->getTable('tax/tax_calculation_rate_title')),
            "rate.tax_calculation_rate_id = title_table.tax_calculation_rate_id AND title_table.store_id = '{$storeId}'",
            array('title' => 'IFNULL(title_table.value, rate.code)')
        );

        $select
            ->where("rate.tax_country_id = ?", $request->getCountryId())
            ->where("rate.tax_region_id in ('*', '', ?)", $request->getRegionId());

        $selectClone = clone $select;

        $select
            ->where("rate.zip_is_range IS NULL")
            ->where("rate.tax_postcode in ('*', '', ?)", $this->_createSearchPostCodeTemplates($request->getPostcode()));

        $selectClone
            ->where("rate.zip_is_range IS NOT NULL")
            ->where("? BETWEEN rate.zip_from AND rate.zip_to", $request->getPostcode());

        /**
         * @see ZF-7592 issue http://framework.zend.com/issues/browse/ZF-7592
         */
        $select = $this->_getReadAdapter()->select()->union(array('(' . $select . ')', '(' . $selectClone . ')'));
        $order  = array('priority ASC', 'tax_calculation_rule_id ASC', 'tax_country_id DESC', 'tax_region_id DESC', 'tax_postcode DESC', 'value DESC');
        $select->order($order);

        $rates = $this->_getReadAdapter()->fetchAll($select);

        if (!$rates && !$request->getDisableByRule()) {
            $rates = parent::_getRates($request);
        }

        return $rates;
    }
}

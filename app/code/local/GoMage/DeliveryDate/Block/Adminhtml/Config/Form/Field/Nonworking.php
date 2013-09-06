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
 * @since        Class available since Release 2.5
 */

class GoMage_DeliveryDate_Block_Adminhtml_Config_Form_Field_Nonworking extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
	
	protected $_renders = array();
	
    public function __construct(){
        $this->addColumn('sort', array(
            'label' => Mage::helper('gomage_checkout')->__('Sort'),
            'style' => 'width:40px',
        ));
        
        $layout = Mage::app()->getFrontController()->getAction()->getLayout();
        
        $renderer = $layout->createBlock('gomage_deliverydate/adminhtml_config_form_renderer_select', '',
                							array('is_render_to_js_template' => true));                							                
        $renderer->setOptions(Mage::getModel('gomage_deliverydate/adminhtml_system_config_source_day')->toOptionArray());
        $this->addColumn('day', array(
            'label' => Mage::helper('gomage_checkout')->__('Day'),
            'style' => 'width:60px',
        	'renderer' => $renderer
        ));
        $this->_renders['day'] = $renderer; 
                
        $renderer = $layout->createBlock('gomage_deliverydate/adminhtml_config_form_renderer_select', '',
                							array('is_render_to_js_template' => true));                
        $renderer->setOptions(Mage::getModel('gomage_deliverydate/adminhtml_system_config_source_month')->toOptionArray());
        $this->addColumn('month', array(
            'label' => Mage::helper('gomage_checkout')->__('Month'),
            'style' => 'width:200px',
        	'renderer' => $renderer
        ));
        $this->_renders['month'] = $renderer;
                                
        $this->_addAfter = false;
        $this->_addButtonLabel = Mage::helper('adminhtml')->__('Add Non-Working Day');
        parent::__construct();
    }
    
    protected function _prepareArrayRow(Varien_Object $row){
    	
    	foreach ($this->_renders as $key => $render){
	        $row->setData(
	            'option_extra_attr_' . $render->calcOptionHash($row->getData($key)),
	            'selected="selected"'
	        );
    	}
    } 
}

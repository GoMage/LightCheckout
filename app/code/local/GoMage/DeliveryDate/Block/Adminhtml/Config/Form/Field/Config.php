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

class GoMage_DeliveryDate_Block_Adminhtml_Config_Form_Field_Config extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
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
        $renderer->setOptions(Mage::getBlockSingleton('gomage_deliverydate/adminhtml_config_form_renderer_dates')->toOptionArray());
        $this->addColumn('day', array(
            'label' => Mage::helper('gomage_checkout')->__('Delivery Day'),
            'style' => 'width:120px',
        	'renderer' => $renderer
        ));
        $this->_renders['day'] = $renderer;

        $renderer = $layout->createBlock('gomage_deliverydate/adminhtml_config_form_renderer_select', '',
                							array('is_render_to_js_template' => true));
        $renderer->setOptions(Mage::getModel('gomage_deliverydate/adminhtml_system_config_source_hour')->toOptionArray());
        $this->addColumn('time_from', array(
            'label' => Mage::helper('gomage_checkout')->__('Time From'),
            'style' => 'width:80px',
        	'renderer' => $renderer
        ));
        $this->_renders['time_from'] = $renderer;
        
        $renderer = $layout->createBlock('gomage_deliverydate/adminhtml_config_form_renderer_select', '',
                							array('is_render_to_js_template' => true));                
        $renderer->setOptions(Mage::getModel('gomage_deliverydate/adminhtml_system_config_source_hour')->toOptionArray());
        $this->addColumn('time_to', array(
            'label' => Mage::helper('gomage_checkout')->__('Time To'),
            'style' => 'width:80px',
        	'renderer' => $renderer
        ));
        $this->_renders['time_to'] = $renderer;
        
        $renderer = $layout->createBlock('gomage_deliverydate/adminhtml_config_form_renderer_select', '',
                							array('is_render_to_js_template' => true));         
        $renderer->setOptions(Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray());        
        $this->addColumn('available', array(
            'label' => Mage::helper('gomage_checkout')->__('Available'),
            'style' => 'width:60px',
        	'renderer' => $renderer
        ));
        $this->_renders['available'] = $renderer;
                
        $this->_addAfter = false;
        $this->_addButtonLabel = Mage::helper('adminhtml')->__('Add Delivery Day');
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

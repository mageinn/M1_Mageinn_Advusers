<?php
/**
 * Admin Edit Mapping Block
 *
 * @category    Mageinn
 * @package     Mageinn_Advusers
 * @author      Rustem.Ziganshin
 */
class Mageinn_Advusers_Block_Adminhtml_Mapping_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
             
        $this->_objectId = 'mapping_id';
        $this->_blockGroup = 'mageinn_advusers';
        $this->_controller = 'adminhtml_mapping';
 
        $this->_updateButton('save', 'label', Mage::helper('mageinn_advusers')->__('Save Mapping'));
    }
    
    public function getHeaderText()
    {
        if( Mage::registry('mapping_data') && Mage::registry('mapping_data')->getId() ) {
            return Mage::helper('mageinn_advusers')->__("Edit Manager");
        } else {
            return Mage::helper('mageinn_advusers')->__("Add Manager");
        }
    }
}
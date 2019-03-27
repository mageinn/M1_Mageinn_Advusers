<?php
/**
 * Admin Mapping Edit Form Block
 *
 * @category    Mageinn
 * @package     Mageinn_Affiliate
 * @author      Mikhail.Dubrovskiy
 */
class Mageinn_Advusers_Block_Adminhtml_Mapping_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
            'id' => 'edit_form',
            'action' => $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'))),
            'method' => 'post'
            )
        );
        
        $fieldset = $form->addFieldset('mapping_form', array('legend'=>Mage::helper('mageinn_advusers')->__('Mapping Details')));
        
        $fieldset->addField('category_id', 'text', array(
            'label'     => Mage::helper('mageinn_advusers')->__('Category ID'),
            'name'      => 'category_id',
            'readonly'  => true
        ));
        
        $values = Mage::getModel('admin/user')->getCollection()
                ->addExpressionFieldToSelect('name', 'concat(main_table.lastname, " ", main_table.firstname)', '');
        $optionValues = array();
        foreach ($values as $value) {
            $optionValues[$value->getUserId()] = $value->getName();
        }

        $fieldset->addField('user_id', 'select', array(
            'label'     => Mage::helper('mageinn_advusers')->__('User'),
            'class'     => 'required-entry',
            'required'  => true,
            'name'      => 'user_id',
            'values'    => $optionValues
        ));
        
        $form->setUseContainer(true);
        $this->setForm($form);
        
        if (Mage::getSingleton('adminhtml/session')->getMappingData()) {
            $formData = Mage::getSingleton('adminhtml/session')->getMappingData();
            Mage::getSingleton('adminhtml/session')->setMappingData(null);
        } elseif ( Mage::registry('mapping_data') ) {
            $formData = Mage::registry('mapping_data')->getData();
        }
        
        $categoryId = (int) $this->getRequest()->getParam('category_id');
        if($categoryId) {
            $formData['category_id'] = $categoryId;
        }
        
        $form->setValues($formData);  
        
        return parent::_prepareForm();
    }
}
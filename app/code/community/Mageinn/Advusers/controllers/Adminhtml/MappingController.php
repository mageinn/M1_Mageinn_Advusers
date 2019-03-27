<?php
/**
 * Admin Advusers mapping controller
 *
 * @category   Mageinn
 * @package    Mageinn_Advusers
 * @author     Rustem.Ziganshin
 */
class Mageinn_Advusers_Adminhtml_MappingController 
    extends Mage_Adminhtml_Controller_Action
{
    
    protected function _initAction()
    {
        $this->loadLayout()
            ->_addBreadcrumb(Mage::helper('mageinn_advusers')->__('Category Managers'), 
                    Mage::helper('mageinn_advusers')->__('Category Managers'));
        return $this;
    }   
    
    
    /**
     * Index action
     */
    public function indexAction()
    {
        $this->_redirect('adminhtml/catalog_category/index/');
    }
    
    /**
     * Edit action
     */
    public function editAction()
    {
        $mId = (int) $this->getRequest()->getParam('id');
        //$cId = (int) $this->getRequest()->getParam('cat');
        $mapping  = Mage::getModel('mageinn_advusers/mapping')->load($mId);
 
        if ($mapping->getId() || $mId == 0) {
            Mage::register('mapping_data', $mapping);
            $this->_initAction();
            //$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
            $this->_addContent($this->getLayout()->createBlock('mageinn_advusers/adminhtml_mapping_edit'));
            $this->renderLayout();
        } else {
            $this->_redirect('*/*/');
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('mageinn_advusers')->__('Mapping does not exist'));
        }
    }
    
    /**
     * New action
     */
    public function newAction()
    {
        $this->_forward('edit');
    }
   
    /**
     * Save action
     */
    public function saveAction()
    {
        if ($this->getRequest()->getPost()) {
            try {
                $postData = $this->getRequest()->getPost();
                $mapping  = Mage::getModel('mageinn_advusers/mapping')
                        ->load($this->getRequest()->getParam('id'));
                
                $catId = $postData['category_id'];
                $userId = $postData['user_id'];
                
                $helper = Mage::helper('mageinn_advusers');
                if (!$helper->mappingExists($catId, $userId)) {
                    $mapping->setCategoryId($catId)
                        ->setUserId($userId)
                        ->save();

                    Mage::getSingleton('adminhtml/session')->addSuccess($helper->__('Manager was successfully saved'));
                    Mage::getSingleton('adminhtml/session')->setMappingData(false);

                    $this->_redirect('adminhtml/catalog_category/edit/', array('id' => $catId));
                    return;
                } else {
                    Mage::getSingleton('adminhtml/session')->addWarning($helper->__('Mapping already exists'));
                    Mage::getSingleton('adminhtml/session')->setMappingData($this->getRequest()->getPost());
                    $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                    return;
                }
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setMappingData($this->getRequest()->getPost());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        $this->_redirect('*/*/');
    }
    
    /**
     * Delete action
     */
    public function deleteAction()
    {
        if( $this->getRequest()->getParam('id') > 0 ) {
            try {
                $mapping= Mage::getModel('mageinn_advusers/mapping');
               
                $mapping->setId($this->getRequest()->getParam('id'))
                    ->delete();
                   
                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('mageinn_advusers')->__('Manager was successfully deleted'));
                $this->_redirect('*/*/');
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        $this->_redirect('*/*/');
    }
    
    /**
     * Product grid for AJAX request.
     * Sort and filter result
     */
    public function gridAction()
    {
        $this->_initCategory();
        $this->loadLayout();
        $this->getResponse()->setBody(
               $this->getLayout()
                ->createBlock('mageinn_advusers/adminhtml_catalog_category_tab_mapping')
                ->toHtml()
        );
    }
    
    /**
     * Initialize requested category and put it into registry.
     *
     * @return Mage_Catalog_Model_Category
     */
    protected function _initCategory()
    {
        $categoryId = (int) $this->getRequest()->getParam('id',false);
        $category = Mage::getModel('catalog/category');

        if ($categoryId) {
            $category->load($categoryId);
        }

        Mage::register('category', $category);
        return $category;
    }
}
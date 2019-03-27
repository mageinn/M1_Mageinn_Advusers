<?php
/**
 * Mageinn_Advusers extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category    Mageinn
 * @package     Mageinn_Advusers
 * @copyright   Copyright (c) 2016 Mageinn. (http://mageinn.com/)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Advusers Observer
 *
 * @category   Mageinn
 * @package    Mageinn_Advusers
 * @author     Mageinn
 */
class Mageinn_Advusers_Model_Observer
{
    private static $_handleOrderCounter = 1;
    private static $_isNewOrder = false;

    const DEBUG_FILE = 'mageinn_advusers.log';
    
    /**
     * Adds a custom tab to adminhtml category page
     * 
     * @param Varien_Event_Observer $observer
     */
    public function addCategoryTab(Varien_Event_Observer $observer)
    {
        $helper = Mage::helper('mageinn_advusers');
        if (!$helper->isEnabled()) {
            return $this;
        }
        
        $tabs = $observer->getEvent()->getTabs();
        $tabs->addTab('Managers', array(
            'label'     => $helper->__('Category Managers'),
            'content'   => Mage::app()->getLayout()
                ->createBlock('mageinn_advusers/adminhtml_catalog_category_tab_mapping')
                ->toHtml()
        ));
    }
    
    /**
     * Add "Add Manager" button
     * 
     * @param Varien_Event_Observer $observer
     */
    public function adminhtmlBlockHtmlBefore(Varien_Event_Observer $observer) 
    {
        $helper = Mage::helper('mageinn_advusers');
        if (!$helper->isEnabled()) {
            return $this;
        }
        
        $block = $observer->getBlock();

        if ($block instanceof Mage_Adminhtml_Block_Catalog_Category_Edit_Form) {
            $category = $block->getCategory();
            $categoryId = (int) $category->getId(); // 0 when we create category, otherwise some value for editing category
        
            if($categoryId) {
                $block->addAdditionalButton('add_manager_mapping', array(
                    'label'     => $helper->__('Add Manager'),
                    'onclick'   => "setLocation('{$block->getUrl('advusersadmin/adminhtml_mapping/new/', array('category_id' => $categoryId))}')",
                    'class'     => "go"
                ));
            }
        }
    }
    
    /**
     * Check if admin has manager rights to process order
     * 
     * @param Varien_Event_Observer $observer
     * @return type
     */
    public function orderLoadAfter(Varien_Event_Observer $observer)
    {
        $helper = Mage::helper('mageinn_advusers');
        if (!$helper->isEnabled() || !$helper->isOrderPermissionEnabled()) {
            return $this;
        }
        
        $user = Mage::getSingleton('admin/session'); 
        $userId = $user->getUser()->getUserId();
        
        // Super Admin
        if($userId == 1) {
            return;
        }
        
        // Check rights
        $managers = $helper->getOrderManagers($observer->getOrder());
        foreach($managers as $manager) {
            if ($manager->getId() == $userId) {
                return;
            }
        }
        
        // Forbidden
        Mage::getSingleton('adminhtml/session')
                ->addWarning($helper->__('You cannot process this order'));
        
        session_write_close();
        
        Mage::app()->getResponse()->setRedirect(Mage::getUrl('*/sales_order'))->sendResponse();
        exit;
    }
    
    /**
     * Set $_isNewOrder flag
     *
     * @param Varien_Event_Observer $observer
     * @return Mageinn_Advusers_Model_Observer
     */
    public function salesOrderBeforeSave(Varien_Event_Observer $observer) 
    {
        $helper = Mage::helper('mageinn_advusers');
        if (!$helper->isEnabled()) {
            return $this;
        }
        
        $order = $observer->getOrder();
        if(!$order->getId()) {
            $this->_debugEntry('salesOrderBeforeSave: Setting $_isNewOrder falg...');
            self::$_isNewOrder = true;
        }
        return $this;
    }
    
    /**
     * Send notifications to managers
     *
     * @param Varien_Event_Observer $observer
     * @return Mageinn_Advusers_Model_Observer
     */
    public function salesOrderSaveAfter(Varien_Event_Observer $observer) 
    {
        $helper = Mage::helper('mageinn_advusers');
        if (!$helper->isEnabled()) {
            return $this;
        }

        $_order = $observer->getOrder();
        $_payment = $_order->getPayment();
        $_code = $_payment->getMethodInstance()->getCode();
        $_whitelisted = $helper->getWhitelistedPayments();

        $history = Mage::app()->getRequest()->getPost('history');

        // Ignore for new orders
        if(self::$_isNewOrder && !in_array($_code, $_whitelisted)) {
            $this->_debugEntry('salesOrderSaveAfter: Skipped, ignoring new orders...');
            return $this;
        }

        // Prevent multiple observer executing
        if (self::$_handleOrderCounter > 1) {
            $this->_debugEntry('salesOrderSaveAfter: Skipped, function already triggered...');
            return $this;
        }
        self::$_handleOrderCounter++;

        $storeId = $_order->getStoreId();
        $store = Mage::app()->getStore($storeId);

        $translate = Mage::getSingleton('core/translate');
        $translate->setTranslateInline(false);
        
        $template = self::$_isNewOrder ?
                    Mage::getStoreConfig('mageinn_advusers/general/new_order_notification_template', $store):
                    Mage::getStoreConfig('mageinn_advusers/general/order_update_notification_template', $store);
        
        $managers = $helper->getOrderManagers($_order);
        $this->_debugEntry('salesOrderSaveAfter: Getting managers for order#' . $_order->getId());
        foreach($managers as $manager) {
            $this->_debugEntry('salesOrderSaveAfter: Sending email for order#' . $_order->getId() . ' to manager at ' . $manager->getEmail());

            Mage::getModel('core/email_template')
                    ->setDesignConfig(array(
                        'area' => 'frontend',
                        'store' => $storeId
                    ))
                    ->sendTransactional(
                        $template,
                        Mage::getStoreConfig('trans_email/ident_general', $store),
                        $manager->getEmail(),
                        '',
                        array(
                            'store' => $store,
                            'comment' => $history['comment'],
                            'recipient_name' => '',
                            'recipient_email' => $manager->getEmail(),
                            'order' => $_order
                        )
                    );
        }
        $translate->setTranslateInline(true);
        
        return $this;
    }

    /**
     * Send New Order Notification once it's paid
     *
     * @param Varien_Event_Observer $observer
     * @return $this
     */
    public function salesOrderInvoicePay(Varien_Event_Observer $observer)
    {
        $helper = Mage::helper('mageinn_advusers');
        if (!$helper->isEnabled()) {
            return $this;
        }

        self::$_handleOrderCounter++;

        $_order = $observer->getInvoice()->getOrder();
        $storeId = $_order->getStoreId();
        $store = Mage::app()->getStore($storeId);

        $translate = Mage::getSingleton('core/translate');
        $translate->setTranslateInline(false);

        $template = Mage::getStoreConfig('mageinn_advusers/general/new_order_notification_template', $store);

        $managers = $helper->getOrderManagers($_order);
        $this->_debugEntry('salesOrderInvoicePay: Getting managers for PAID order#' . $_order->getId());
        foreach($managers as $manager) {
            $this->_debugEntry('salesOrderInvoicePay: Sending email for PAID order#' . $_order->getId() . ' to manager at ' . $manager->getEmail());

            Mage::getModel('core/email_template')
                ->setDesignConfig(array(
                    'area' => 'frontend',
                    'store' => $storeId
                ))
                ->sendTransactional(
                    $template,
                    Mage::getStoreConfig('trans_email/ident_general', $store),
                    $manager->getEmail(),
                    '',
                    array(
                        'store' => $store,
                        'recipient_name' => '',
                        'recipient_email' => $manager->getEmail(),
                        'order' => $_order
                    )
                );
        }
        $translate->setTranslateInline(true);

        return $this;
    }

    /**
     * @param $msg
     * @return $this
     */
    protected function _debugEntry($msg)
    {
        $helper = Mage::helper('mageinn_advusers');
        if (!$helper->isDebugMode()) {
            return $this;
        }

        Mage::log($msg, null, self::DEBUG_FILE);
    }
}
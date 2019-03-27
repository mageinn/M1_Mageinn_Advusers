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
 * Advusers Helper
 * 
 * @category   Mageinn
 * @package    Mageinn_Advusers
 * @author     Mageinn
 */
class Mageinn_Advusers_Helper_Data extends Mage_Core_Helper_Abstract
{
    const XML_PATH_ENABLED                  = 'mageinn_advusers/general/enabled';
    const XML_PATH_ENABLED_PERM             = 'mageinn_advusers/general/enable_order_permission';
    const XML_PATH_WHITELISTED_PAY_CODES    = 'mageinn_advusers/general/whitelisted_payments';
    const XML_PATH_DEBUG_MODE               = 'mageinn_advusers/general/debug_mode';

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_ENABLED);
    }

    /**
     * @return bool
     */
    public function isDebugMode()
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_DEBUG_MODE);
    }

    /**
     * @return bool
     */
    public function isOrderPermissionEnabled()
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_ENABLED_PERM);
    }

    /**
     * @return array
     */
    public function getWhitelistedPayments()
    {
        $result = array();
        $methods = explode(",", Mage::getStoreConfig(self::XML_PATH_WHITELISTED_PAY_CODES));
        foreach($methods as $code) {
            $result[] = trim($code);
        }
        return $result;
    }
    
    /**
     * Get users associated to a highest value category
     * 
     * @param Mage_Sales_Model_Order $order
     * @return Mage_Admin_Model_Resource_User_Collection
     */
    public function getOrderManagers($order)
    {
        $users = array();
        
        $mainCategoryId = $this->getMainCategory($order);
        $mapped = Mage::getModel('mageinn_advusers/mapping')
                ->getCollection()
                ->addFieldToFilter('category_id', $mainCategoryId);
        $filter = array();
        foreach($mapped as $user) {
            $filter[] = $user->getUserId();
        }
        
        if(!empty($filter)) {
            $users = Mage::getModel('admin/user')->getCollection()
                    ->addFieldToFilter('user_id', array('in' => $filter));
        }
        
        return $users;
    }
    
    /**
     * Get main category that has highest purchased value and also mapped
     * 
     * @param Mage_Sales_Model_Order $order
     * @return int
     */
    public function getMainCategory($order)
    {
        // Get mapped categories
        $_userCats = Mage::getModel('mageinn_advusers/mapping')
                ->getCollection();
        $mapped = array();
        foreach ($_userCats as $_cat) {
            $mapped[] = $_cat->getCategoryId();
        }
        
        // Get purchased categories
        $purchased = array();
        $_items = $order->getAllItems();
        foreach ($_items as $_item) {
            $_cats = $_item->getProduct()->getCategoryIds();
            foreach ($_cats as $_cid) {
                if(!isset($purchased[$_cid])) {
                    $purchased[$_cid] = 0;
                }
                $purchased[$_cid] += $_item->getPrice() * $_item->getQtyOrdered();
            }
        }
        
        // Get highest value mapped category
        $mainCategory = 0;
        $lastHighest = 0;
        foreach($purchased as $cid => $value) {
            // If category is mapped, proceed
            if(in_array($cid, $mapped) && $value > $lastHighest) {
                $mainCategory = $cid;
            }
        }
        
        return $mainCategory;
    }
    
    /**
     * Check if mapping exists
     * 
     * @param int $catId
     * @param int $userId
     * @return bool
     */
    public function mappingExists($catId, $userId)
    {
        $m = Mage::getModel('mageinn_advusers/mapping')->getCollection()
                ->addFieldToFilter('user_id', $userId)
                ->addFieldToFilter('category_id', $catId);
        return (count($m) > 0);
    }
}
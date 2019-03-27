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
 * Advusers Notify
 * 
 * @category   Mageinn
 * @package    Mageinn_Advusers
 * @author     Mageinn
 */
class Mageinn_Advusers_Model_Notify extends AW_Rma_Model_Notify
{
    protected function _notify($rmaEntity, $comment, $template, $templateRecipient, $toEmail, $toName, $emailParams) 
    {
        parent::_notify($rmaEntity, $comment, $template, $templateRecipient, $toEmail, $toName, $emailParams);
        
        $store = Mage::app()->getStore();
        $translate = Mage::getSingleton('core/translate');
        $translate->setTranslateInline(false);
        $tpl = Mage::getStoreConfig('mageinn_advusers/general/rma_notification_template', $store);
        if(empty($tpl)) {
            return;
        }
        
        $helper = Mage::helper('mageinn_advusers');
        $order = $rmaEntity->getOrder();
        
        $managers = $helper->getOrderManagers($order);
        foreach($managers as $manager) {
            if ($tpl) {
                Mage::getModel('core/email_template')
                    ->setDesignConfig(array(
                        'area' => 'frontend',
                        'store' => $store->getId()
                    ))
                    ->sendTransactional(
                        $tpl,
                        Mage::getStoreConfig('trans_email/ident_general', $store),
                        $manager->getEmail(),
                        '',
                        array(
                            'store' => $store,
                            'recipient_name' => '',
                            'comment' => $comment,
                            'recipient_email' => $manager->getEmail(),
                            'order' => $order
                        )
                    );
            }
        }
        $translate->setTranslateInline(true);
    }
}
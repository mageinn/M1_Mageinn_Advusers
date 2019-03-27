<?php
/**
 * Install script
 *
 */

$installer = $this;
$installer->startSetup();

/**
 * Create table 'mageinn_advusers_mapping'
 */
$table7 = $installer->getConnection()
    ->newTable($installer->getTable('mageinn_advusers/mapping'))
    ->addColumn('mapping_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Mapping Id')
    ->addColumn('category_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'unsigned'  => true
        ), 'Category ID')
    ->addColumn('user_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        ), 'User ID')
    ->addForeignKey($installer->getFkName('mageinn_advusers/mapping', 'category_id', 'catalog/category', 
        'entity_id'),
        'category_id', $installer->getTable('catalog/category'), 'entity_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->addForeignKey($installer->getFkName('mageinn_advusers/mapping', 'user_id', 'admin/user', 'user_id'),
        'user_id', $installer->getTable('admin/user'), 'user_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE)
    ->setComment('User Category Mapping Table');
$installer->tableExists($table7->getName()) || $installer->getConnection()->createTable($table7);

$installer->endSetup();
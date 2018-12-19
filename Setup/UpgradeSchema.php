<?php
namespace Compropago\Magento2\Setup;
use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;
class UpgradeSchema implements UpgradeSchemaInterface
{
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        // Get compropago_orders table
        $tableName = $installer->getTable('compropago_orders');
        // Check if the table already exists
        if ($installer->getConnection()->isTableExists($tableName) != true)
        {
            // Create compropago_orders table
            $table = $installer->getConnection()
                ->newTable($tableName)
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    11,
                    [
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true
                    ],
                    'ID'
                )
                ->addColumn(
                    'date',
                    Table::TYPE_INTEGER,
                    11,
                    ['nullable' => false],
                    'Reg date'
                )
                ->addColumn(
                    'modified',
                    Table::TYPE_INTEGER,
                    11,
                    ['nullable' => false],
                    'Mod date'
                )
                ->addColumn(
                    'compropagoId',
                    Table::TYPE_TEXT,
                    50,
                    ['nullable' => false, 'default' => ''],
                    'Compropago Order Id'
                )
                ->addColumn(
                    'compropagoStatus',
                    Table::TYPE_TEXT,
                    50,
                    ['nullable' => false, 'default' => ''],
                    'Compropago status'
                )
                ->addColumn(
                    'storeCartId',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => false, 'default' => ''],
                    'no cart id repeat order id'
                )
                ->addColumn(
                    'storeOrderId',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => false, 'default' => ''],
                    'store order Id to save'
                )
                ->addColumn(
                    'storeExtra',
                    Table::TYPE_TEXT,
                    255,
                    ['nullable' => false, 'default' => ''],
                    'store extra or Compropago flags'
                )
                ->addColumn(
                    'ioIn',
                    Table::TYPE_TEXT,
                    '2M',
                    ['nullable' => false, 'default' => ''],
                    'store extra or Compropago flags'
                )
                ->addColumn(
                    'ioOut',
                    Table::TYPE_TEXT,
                    '2M',
                    ['nullable' => false, 'default' => ''],
                    'store extra or Compropago flags'
                )
                ->setComment('ComproPago Orders Table')
                ->setOption('charset', 'utf8');
            $installer->getConnection()->createTable($table);
        }
        // Get compropago_transactions table
        $tableName = $installer->getTable('compropago_transactions');
        // Check if the table already exists
        if ($installer->getConnection()->isTableExists($tableName) != true)
        {
            // Create compropago_transactions table
            $table = $installer->getConnection()
                ->newTable($tableName)
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    11,
                    [
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true
                    ],
                    'ID'
                )->addColumn(
                    'orderId',
                    Table::TYPE_INTEGER,
                    11,
                    ['nullable' => false],
                    'FK Id orders_compropago'
                )->addColumn(
                    'date',
                    Table::TYPE_INTEGER,
                    11,
                    ['nullable' => false],
                    'Reg date'
                )->addColumn(
                    'compropagoId',
                    Table::TYPE_TEXT,
                    50,
                    ['nullable' => false, 'default' => ''],
                    'Compropago Order Id'
                )->addColumn(
                    'compropagoStatus',
                    Table::TYPE_TEXT,
                    50,
                    ['nullable' => false, 'default' => ''],
                    'Compropago status'
                )->addColumn(
                    'compropagoStatusLast',
                    Table::TYPE_TEXT,
                    50,
                    ['nullable' => false, 'default' => ''],
                    'b4 transaction'
                )->addColumn(
                    'ioIn',
                    Table::TYPE_TEXT,
                    '2M',
                    ['nullable' => false, 'default' => ''],
                    'store extra or Compropago flags'
                )->addColumn(
                    'ioOut',
                    Table::TYPE_TEXT,
                    '2M',
                    ['nullable' => false, 'default' => ''],
                    'store extra or Compropago flags'
                )
                ->setComment('ComproPago Orders Table');
            $installer->getConnection()->createTable($table);
        }

        // end setup
        $installer->endSetup();
    }
}

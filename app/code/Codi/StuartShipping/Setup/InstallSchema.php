<?php
namespace Codi\StuartShipping\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        $table = $installer->getConnection()
            ->newTable($installer->getTable('stuart_job'))
            ->addColumn(
                'entity_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true],
                'Entity ID'
            )
            ->addColumn(
                'order_id',
                Table::TYPE_INTEGER,
                null,
                ['unsigned' => true, 'nullable' => false],
                'Order ID'
            )
            ->addColumn(
                'job_id',
                Table::TYPE_TEXT,
                255,
                ['nullable' => false],
                'Stuart Job ID'
            )
            ->addColumn(
                'status',
                Table::TYPE_TEXT,
                50,
                ['nullable' => false],
                'Job Status'
            )
            ->addColumn(
                'tracking_url',
                Table::TYPE_TEXT,
                255,
                ['nullable' => true],
                'Tracking URL'
            )
            ->addColumn(
                'pickup_at',
                Table::TYPE_DATETIME,
                null,
                ['nullable' => true],
                'Pickup Time'
            )
            ->addColumn(
                'created_at',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT],
                'Created At'
            )
            ->addColumn(
                'updated_at',
                Table::TYPE_TIMESTAMP,
                null,
                ['nullable' => false, 'default' => Table::TIMESTAMP_INIT_UPDATE],
                'Updated At'
            )
            ->addIndex(
                $installer->getIdxName('stuart_job', ['order_id']),
                ['order_id']
            )
            ->addIndex(
                $installer->getIdxName('stuart_job', ['job_id']),
                ['job_id']
            )
            ->addForeignKey(
                $installer->getFkName('stuart_job', 'order_id', 'sales_order', 'entity_id'),
                'order_id',
                $installer->getTable('sales_order'),
                'entity_id',
                Table::ACTION_CASCADE
            );

        $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }
}
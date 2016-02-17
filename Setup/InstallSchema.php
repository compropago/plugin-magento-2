<?php
/*
* Copyright 2016 Compropago. 
*
* Licensed under the Apache License, Version 2.0 (the "License");
* you may not use this file except in compliance with the License.
* You may obtain a copy of the License at
*
*     http://www.apache.org/licenses/LICENSE-2.0
*
* Unless required by applicable law or agreed to in writing, software
* distributed under the License is distributed on an "AS IS" BASIS,
* WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
* See the License for the specific language governing permissions and
* limitations under the License.
*/
/**
 * @author Rolando Lucio <rolando@compropago.com>
 */

namespace Compropago\Magento2\Setup;

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

		// Get tutorial_simplenews table
		$tableName = $installer->getTable('tutorial_simplenews');
		// Check if the table already exists
		if ($installer->getConnection()->isTableExists($tableName) != true) {
			// Create tutorial_simplenews table
			$table = $installer->getConnection()
			->newTable($tableName)
			->addColumn(
					'id',
					Table::TYPE_INTEGER,
					null,
					[
							'identity' => true,
							'unsigned' => true,
							'nullable' => false,
							'primary' => true
					],
					'ID'
					)
					->addColumn(
							'title',
							Table::TYPE_TEXT,
							null,
							['nullable' => false, 'default' => ''],
							'Title'
							)
							->addColumn(
									'summary',
									Table::TYPE_TEXT,
									null,
									['nullable' => false, 'default' => ''],
									'Summary'
									)
									->addColumn(
											'description',
											Table::TYPE_TEXT,
											null,
											['nullable' => false, 'default' => ''],
											'Description'
											)
											->addColumn(
													'created_at',
													Table::TYPE_DATETIME,
													null,
													['nullable' => false],
													'Created At'
													)
			->addColumn(
					'status',
					Table::TYPE_SMALLINT,
					null,
					['nullable' => false, 'default' => '0'],
					'Status'
					)
					->setComment('News Table')
					->setOption('type', 'InnoDB')
					->setOption('charset', 'utf8');
					$installer->getConnection()->createTable($table);
		}

		$installer->endSetup();
	}
}
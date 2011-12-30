<?php

/**
 * 
 *
 */
class _20120101000000_channel extends Migration {
	/**
	 * up
	 */
	public function up () {
		$this->_adapter
			->createTable('channel', TableOption::instance()->timestamp(false))
			->addColumn('channel', 'name', 'string', ColumnOption::instance()->_default(null))
			->addColumn('channel', 'view', 'boolean', ColumnOption::instance()->_default(1))
			->addColumn('channel', 'created_on', 'datetime')
			->addColumn('channel', 'updated_on', 'datetime')
			->addColumn('channel', 'readed_on', 'datetime');

		$this->_adapter->sql('ALTER TABLE `channel` ADD UNIQUE `name` (`name`);');
	}

	public function down() {
		$this->_adapter
			->dropTable('channel');
	}
}


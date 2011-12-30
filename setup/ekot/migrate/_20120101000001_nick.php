<?php

/**
 * 
 *
 */
class _20120101000001_nick extends Migration {

	public function up () {
		$this->_adapter
			->createTable('nick', TableOption::instance()->timestamp(false))
			->addColumn('nick', 'name', 'string', ColumnOption::instance()->_default(null))
			->addColumn('nick', 'created_on', 'datetime')
			->addColumn('nick', 'updated_on', 'datetime');

		$this->_adapter->sql('ALTER TABLE `nick` ADD UNIQUE `name` (`name`);');
	}

	public function down() {
		$this->_adapter
			->dropTable('nick');
	}
}


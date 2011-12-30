<?php

/**
 * 
 *
 */
class _20120101000002_priv extends Migration {
	/**
	 * up
	 */
	public function up () {
		$this->_adapter
			->createTable('priv', TableOption::instance()->timestamp(false))
			->addColumn('priv', 'nick_id', 'integer', ColumnOption::instance()->_default(null))
			->addColumn('priv', 'msg', 'text')
			->addColumn('priv', 'is_notice', 'boolean', ColumnOption::instance()->_default(null))
			->addColumn('priv', 'is_me', 'boolean', ColumnOption::instance()->_default(null))
			->addColumn('priv', 'created_on', 'datetime')
			->addColumn('priv', 'updated_on', 'datetime');
	}

	public function down() {
		$this->_adapter
			->dropTable('priv');
	}

}


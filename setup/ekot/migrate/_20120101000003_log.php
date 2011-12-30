<?php

/**
 * 
 *
 */
class _20120101000003_log extends Migration {
	/**
	 * up
	 */
	public function up () {
		$this->_adapter
			->createTable('log', TableOption::instance()->timestamp(false))
			->addColumn('log', 'channel_id', 'integer')
			->addColumn('log', 'nick_id', 'integer')
			->addColumn('log', 'log', 'text')
			->addColumn('log', 'is_notice', 'boolean', ColumnOption::instance()->_default(null))
			->addColumn('log', 'created_on', 'datetime')
			->addColumn('log', 'updated_on', 'datetime');

		$this->_adapter->sql('ALTER TABLE `log` ADD INDEX `nick_id` (`nick_id`);');
		$this->_adapter->sql('ALTER TABLE `log` ADD INDEX `channel_id_and_created_on` (`channel_id`, `created_on`);');
		$this->_adapter->sql('ALTER TABLE `log` ADD FOREIGN KEY (`channel_id`) REFERENCES `channel` (`id`);');
		$this->_adapter->sql('ALTER TABLE `log` ADD FOREIGN KEY (`nick_id`) REFERENCES `nick` (`id`);');
	}

	public function down() {
		$this->_adapter
			->dropTable('log');
	}

}


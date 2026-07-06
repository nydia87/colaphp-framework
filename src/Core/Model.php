<?php
/**
 * @author: nydia87 <349196713@qq.com>
 * @description:
 */

namespace ColaPHP\Framework\Core;

use ColaPHP\Db\DbManager;

class Model
{
	// 当前数据库操作对象
	protected $db;

	public function __construct()
	{
		$this->db(0, $this->getDefaultDbConfig());
		// 子类初始化
		if (method_exists($this, '_initialize')) {
			$this->_initialize();
		}
	}

	/**
	 * 切换数据库连接.
	 *
	 * @param mixed $index
	 * @param mixed $config
	 */
	public function db($index, $config = [])
	{
		static $_db = [];
		if (! isset($_db[$index])) { // 创建并切换
			$manager = new DbManager($config);
			$_db[$index] = $manager->make();
			$this->db = $_db[$index];

			return $this;
		}
		if (null === $config) {
			$_db[$index]->disconnect();
			unset($_db[$index]);
		}
	}

	/**
	 * 插入入口.
	 *
	 * @param mixed $query
	 * @param mixed $bindings
	 */
	public function insert($query, $bindings = [])
	{
		return $this->db->insert($query, $bindings);
	}

	/**
	 * 删除入口.
	 *
	 * @param mixed $query
	 * @param mixed $bindings
	 */
	public function delete($query, $bindings = [])
	{
		return $this->db->delete($query, $bindings);
	}

	/**
	 * 修改入口.
	 *
	 * @param mixed $query
	 * @param mixed $bindings
	 */
	public function update($query, $bindings = [])
	{
		return $this->db->update($query, $bindings);
	}

	/**
	 * 查找单条记录.
	 *
	 * @return mixed
	 */
	public function selectOne($query, $bindings = [], $slave = true)
	{
		return $this->db->selectOne($query, $bindings, $slave);
	}

	/**
	 * 查找多条记录.
	 *
	 * @return mixed
	 */
	public function select($query, $bindings = [], $slave = true)
	{
		return $this->db->select($query, $bindings, $slave);
	}

	/**
	 * @return mixed
	 */
	public function exec($query)
	{
		return $this->db->exec($query);
	}

	/**
	 * Closure 事务
	 */
	public function transaction(\Closure $callback)
	{
		return $this->db->transaction($callback);
	}

	/**
	 * 开启事务
	 */
	public function beginTransaction()
	{
		$this->db->beginTransaction();
	}

	/**
	 * 提交事务
	 */
	public function commit()
	{
		$this->db->commit();
	}

	/**
	 * 回滚事务
	 */
	public function rollBack()
	{
		$this->db->rollBack();
	}

	/**
	 * 返回插入SQL LastID.
	 *
	 * @param null|mixed $name
	 */
	public function lastId($name = null)
	{
		return $this->db->lastId($name);
	}

	/**
	 * 调试.
	 *
	 * @return array
	 */
	public function debug(\Closure $callback)
	{
		return $this->db->debug($callback);
	}

	/**
	 * 开启日志.
	 */
	public function enableLog()
	{
		return $this->db->enableLog();
	}

	/**
	 * 关闭日志.
	 */
	public function disableLog()
	{
		return $this->db->disableLog();
	}

	/**
	 * 获取日志.
	 */
	public function getLogs()
	{
		return $this->db->getLogs();
	}

	/**
	 * 默认数据库配置.
	 */
	protected function getDefaultDbConfig()
	{
		$config = [];
		$config['driver'] = env('db.driver') ?? 'mysql';
		$config['charset'] = env('db.charset') ?? 'utf8mb4';
		$config['collation'] = env('db.collation') ?? 'utf8mb4_unicode_ci';
		$config['prefix'] = env('db.prefix') ?? '';
		if (empty(env('db.master.host'))) {
			$config['host'] = env('db.host') ?? '127.0.0.1';
			$config['database'] = env('db.database') ?? '';
			$config['port'] = env('db.port') ?? 0;
			$config['timezone'] = env('db.timezone') ?? 'Asia/Shanghai';
			$config['username'] = env('db.username') ?? '';
			$config['password'] = env('db.password') ?? '';
		} else {
			// master
			$config['master']['host'] = env('db.master.host') ?? '127.0.0.1';
			$config['master']['database'] = env('db.master.database') ?? '';
			$config['master']['port'] = env('db.master.port') ?? 0;
			$config['master']['timezone'] = env('db.master.timezone') ?? 'Asia/Shanghai';
			$config['master']['username'] = env('db.master.username') ?? '';
			$config['master']['password'] = env('db.master.password') ?? '';
			// slave
			$config['slave']['host'] = env('db.slave.host') ?? '127.0.0.1';
			$config['slave']['database'] = env('db.slave.database') ?? '';
			$config['slave']['port'] = env('db.slave.port') ?? 0;
			$config['slave']['timezone'] = env('db.slave.timezone') ?? 'Asia/Shanghai';
			$config['slave']['username'] = env('db.slave.username') ?? '';
			$config['slave']['password'] = env('db.slave.password') ?? '';
		}

		return $config;
	}
}

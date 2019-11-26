<?php
/**
 * Created by PhpStorm.
 * User: mu
 * Date: 2019-11-26
 * Time: 10:20
 */
namespace Mu\BloomFilter;
/**
 * 使用redis实现的布隆过滤器
 */
abstract class BloomFilterRedis
{
	/**
	 * 需要使用一个方法来定义bucket的名字
	 */
	protected $bucket;
	
	protected $hashFunction;
	
	protected $Redis;
	
	protected $Hash;
	/**
	 * BloomFilterRedis constructor.
	 *
	 * @param array $config
	 *
	 * @throws \Exception
	 */
	public function __construct(array $config)
	{
		if (!$this->bucket || !$this->hashFunction) {
			throw new \Exception("需要定义bucket和hashFunction", 1);
		}
		$this->Hash = new BloomFilterHash;
		$this->Redis = new \Redis(); //假设这里你已经连接好了
		try{
			$this->Redis->connect($config['host'],$config['port'],$config['timeout']);
			$this->Redis->auth($config['auth']);
			$this->Redis->select($config['database']);
		}catch (\RedisException $e){
			throw new \Exception('redis链接超时');
		}catch (\Exception $e){
			throw new \Exception('redis 链接超时');
		}
	}
	
	/**
	 * 添加到集合中
	 */
	public function add($string)
	{
		$pipe = $this->Redis->multi();
		foreach ($this->hashFunction as $function) {
			$hash = $this->Hash->$function($string);
			$pipe->setBit($this->bucket, $hash, 1);
		}
		return $pipe->exec();
	}
	
	/**
	 * 批量添加内容到集合中
	 *
	 * @param array $keys
	 *
	 * @return array
	 */
	public function multi_add(array $keys)
	{
		$result = [];
		if (count($keys) < 1){
			return $result;
		}
		foreach ($keys as $key)
		{
			$result[] = $this->add($key);
		}
		return $result;
	}
	
	/**
	 * 查询是否存在, 存在的一定会存在, 不存在有一定几率会误判
	 *
	 * @param string $string
	 *
	 * @return bool
	 */
	public function exists(string $string)
	{
		$pipe = $this->Redis->multi();
		$len = strlen($string);
		foreach ($this->hashFunction as $function) {
			$hash = $this->Hash->$function($string, $len);
			$pipe = $pipe->getBit($this->bucket, $hash);
		}
		$res = $pipe->exec();
		foreach ($res as $bit) {
			if ($bit == 0) {
				return false;
			}
		}
		return true;
	}
	
	/**
	 * 批量检查是否存在
	 *
	 * @param array $keys
	 *
	 * @return array
	 */
	public function multi_exists(array $keys)
	{
		$result = [];
		if (count($keys)<1){
			return $result;
		}
		foreach ($keys as $key)
		{
			$result[] = $this->exists($key);
		}
		return $result;
	}
	
	public function __destruct()
	{
		// TODO: Implement __destruct() method.
		$this->Redis->close();
	}
	
}
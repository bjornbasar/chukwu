<?
/**
 * Data Caching Class
 *
 * @author Sheldon Senseng
 * @copyright Sheldon Senseng <sheldonsenseng@gmail.com>
 * @version 0.1
 *         
 */
define('APP_DATA_CACHE', APP_DATA . 'cache/');

class Core_Cache
{

	private $cache;

	/**
	 * Initializes the Cache object
	 */
	public function __construct ()
	{

		$frontendOptions = array('lifetime' => 3600, 'automatic_serialization' => true);
		
		$backendOptions = array('cache_dir' => APP_DATA_CACHE);
		
		$this->cache = Zend_Cache::factory('Core', 'File', $frontendOptions, $backendOptions);
	
	}

	/**
	 * Push data to the cache
	 *
	 * @param $id string        	
	 * @param $value mixed        	
	 * @param $tags array        	
	 */
	public function set ($id, $value, $tags = array())
	{

		$this->cache->save($value, $id, $tags);
	
	}

	/**
	 * Retrieve data from the cache
	 *
	 * @param $id string        	
	 */
	public function get ($id)
	{

		$this->cache->load($id);
	
	}

	/**
	 * Remove data from the cache
	 *
	 * @param $id string        	
	 */
	public function remove ($id)
	
	{

		$this->cache->remove($id);
	
	}

	/**
	 * Purge the entire cache
	 */
	public function reset_all ()
	{

		$this->cache->clean(Zend_Cache::CLEANING_MODE_ALL);
	
	}

	/**
	 * Remove all old objects in the cache
	 */
	public function reset_old ()
	{

		$this->cache->clean(Zend_Cache::CLEANING_MODE_OLD);
	
	}

	/**
	 * Remove all cache objects with the given tag
	 *
	 * @param $tags array        	
	 */
	public function reset_tags (array $tags = array())
	{

		if (count($tags) > 0)
		{
			$this->cache->clean(Zend_Cache::CLEANING_MODE_MATCHING_TAG, $tags);
		}
	
	}

	public function __destruct ()
	{

	}

}
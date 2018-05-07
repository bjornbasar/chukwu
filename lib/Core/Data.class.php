<?
if (! defined('APP_DATA_DB'))
{
	define('APP_DATA_DB', APP_DATA . 'db/');
}

if (! file_exists(APP_DATA_DB))
{
	mkdir(APP_DATA_DB);
}

class Core_Data
{

	protected $_db;

	public function __construct ($name = 'berkeley')
	{

		$this->_db = dba_popen(APP_DATA_DB . $name, "c", "db4");
		
		if (! $this->_db)
		{
			trigger_error('Cannot open/create database', E_USER_ERROR);
			die();
		}
	
	}

	public function __destruct ()
	{

		if ($this->_db)
		{
			dba_optimize($this->_db);
			
			dba_close($this->_db);
		}
	
	}

	protected function generateID ()
	{

		return md5(uniqid('', true));
	
	}

	public function add ($data)
	{

		$isUnique = false;
		
		while (! $isUnique)
		{
			// create a unique key
			$key = $this->generateID();
			
			// check if key exists
			if (! dba_exists($key, $this->_db))
			{
				$isUnique = true;
			}
		}
		
		dba_replace($key, json_encode($data), $this->_db);
		
		dba_sync($$this->_db);
		
		return $key;
	
	}

	public function update ($key, $data)
	{

		dba_replace($key, json_encode($data), $this->_db);
		
		dba_sync($this->_db);
		
		return $key;
	
	}

	public function autoUpdate ($data, $key = null)
	{
		if (is_null($key))
		{
			return $this->add($data);
		}
		else
		{
			return $this->update($key, $data);
		}
	}

	public function remove ($key)
	{

		dba_delete($key);
		
		dba_sync($this->_db);
	
	}

	public function get ($key)
	{

		return dba_fetch($key, $this->_db);
	
	}

}

<?

class Core_Sqlite
{

	protected $_dbName;

	protected $_dbFullPath;

	protected $_conn;

	public function __construct ($dbName, $path = null)
	{

		$this->_dbName = $dbName;
		
		if (is_null($path))
		{
			$this->_dbFullPath = APP_SQLITE . $dbName;
		}
		else
		{
			$this->_dbFullPath = $path . $dbName;
		}
		
		$this->_conn = sqlite_open($this->_dbFullPath, '0666', $error);
		
		if ($error)
		{
			throw new Exception($error);
		}
	
	}

	public function __destruct ()
	{

		sqlite_close($this->_conn);
	
	}

	public function getRow ($query, $data = array())
	{

		$query = $this->_createSql($query, $data);
		$rs = $this->_execute($query);
		
		if (count($rs) > 0)
		{
			return $rs[0];
		}
		
		return $rs;
	
	}

	public function getArray ($query, $data = array())
	{

		$query = $this->_createSql($query, $data);
		$rs = $this->_execute($query);
		
		return $rs;
	
	}

	public function execute ($query, $data = array())
	{

		$query = $this->_createSql($query, $data);
		$rs = $this->_execute($query, false);
	
	}

	protected function _createSql ($query, $data)
	{
		// replace ? in $query with $data values
		for ($i = 0; $i < count($data); ++ $i)
		{
			$replaceString = Core_Helper::sanitize($data[$i]);
			if (is_string($data[$i]))
			{
				$replaceString = "'" . $data[$i] . "'";
			}
			
			$y = strpos($query, '?');
			if ($y !== false)
				$query = substr($query, 0, $y) . $replaceString . substr($query, $y + 1);
		}
		
		return $query;
	
	}

	protected function _execute ($query, $returndata = true)
	{

		if ($returndata)
		{
			$result = sqlite_query($this->_conn, $query, SQLITE_ASSOC, $error);
			
			if ($error)
			{
				throw new Exception($error);
			}
			
			return $this->_convertResult($result);
		}
		else
		{
			$result = sqlite_exec($this->_conn, $query, $error);
			
			if ($error)
			{
				throw new Exception($error . " <br/>\n" . $query);
			}
		}
	
	}

	private function _convertResult ($result)
	{

		if (sqlite_num_rows($result) > 0)
		{
			$rs = array();
			$data = sqlite_fetch_array($result);
			while ($data)
			{
				$rs[] = Core_Helper::desanitize($data);
				$data = sqlite_fetch_array($result);
			}
			
			return $rs;
		}
		
		return array();
	
	}

}
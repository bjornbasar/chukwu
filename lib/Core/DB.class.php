<?

/**
 * DB Abstraction Class for MySQL
 *
 * @author Sheldon Senseng
 * @copyright Sheldon Senseng <sheldonsenseng@gmail.com>
 * @version 0.1
 *         
 */

// include Database Configuration
require_once APP_CONF . 'DB.conf.php';

// define the path for the DB structure cache
define('APP_DATA_DB', APP_DATA . 'db/');

// define columns type not to be sanitized
define('UNSANITIZEDDATATYPES', serialize(array('timestamp', 'date', 'time', 'enum', 'datetime')));

class Core_DB
{

	/**
	 * Database Connection holder
	 *
	 * @var MySQL Handler
	 */
	private $_db;

	/**
	 * Database and table structure
	 *
	 * @var array
	 */
	public $dataset;

	/**
	 * Database Configuration
	 *
	 * @var array
	 */
	private $_conf;

	/**
	 * Index to be used in Database Configuration
	 *
	 * @var int
	 */
	private $_dbUsed;

	/**
	 * List of string data types for auto cleanup
	 *
	 * @var array
	 */
	public $STRINGTYPES = array('char', 'varchar', 'binary', 'varbinary', 'blob', 'text', 'enum', 'set');

	/**
	 * Number of affected rows
	 *
	 * @var int
	 */
	public $affected_rows;

	/**
	 * DB constructor
	 *
	 * @param $db int
	 *        	- defined in DB.conf.php
	 * @return DB
	 */
	public function __construct ($db = DB_MAIN)
	{

		require APP_CONF . 'DB.conf.php';
		
		$this->_conf = $DATABASES[$db];
		$this->_dbUsed = $db;
		
		$this->_db = mysqli_connect($DATABASES[$db]['host'], $DATABASES[$db]['username'], $DATABASES[$db]['password'], $DATABASES[$db]['db']) or die(mysqli_error($this->_db));
		
		if (! file_exists(APP_DATA_DB . 'DB'))
		{
			$this->dataRefresh();
		}
		else
		{
			$this->dataGet();
		}
	
	}

	/**
	 * Converts mysql result resource into associative array
	 *
	 * @param
	 *        	mysql resource $result
	 * @return array - associative array of resource
	 */
	private function _convertResult ($result)
	{

		if ($this->_db->affected_rows > 0)
		{
			$rs = array();
			$data = $result->fetch_assoc();
			while ($data)
			{
				$rs[] = Core_Helper::desanitize($data);
				$data = $result->fetch_assoc();
			}
			
			return $rs;
		}
		
		return array();
	
	}

	/**
	 * Executes mysql query and optionally converts return data
	 *
	 * @param $query string
	 *        	- mysql query
	 * @param $returndata boolean
	 *        	- if there is a result set to be returned
	 * @return array or none
	 */
	private function _execute ($query, $returndata = true)
	{

		$result = $this->_db->query($query) or die(mysqli_error($this->_db) . "<br/>\nyour query: <b>" . $query . '</b>');
		
		$this->affected_rows = $this->_db->affected_rows;
		
		if ($returndata)
		{
			return $this->_convertResult($result);
		}
	
	}

	/**
	 * Creates and sql query based on $query and $data array by replacing ? with
	 * corresponding $data values
	 *
	 * @param $query string        	
	 * @param $data array        	
	 * @return string - query
	 */
	private function _createSql ($query, $data = array())
	{
		// replace ? in $query with $data values
		for ($i = 0; $i < count($data); ++ $i)
		{
			$replaceString = Core_Helper::sanitize($data[$i]);
			if (is_string($data[$i]))
			{
				$replaceString = "'" . $replaceString . "'";
			}
			
			$y = strpos($query, '?');
			if ($y !== false)
				$query = substr($query, 0, $y) . $replaceString . substr($query, $y + 1);
		}
		
		return $query;
	
	}

	/**
	 * Returns first row of result of $query with replaced $data values
	 *
	 * @param $query string        	
	 * @param $data array        	
	 * @return array
	 */
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

	/**
	 * Returns array of result of $query with replaced $data values
	 *
	 * @param $query string        	
	 * @param $data array        	
	 * @return array
	 */
	public function getArray ($query, $data = array())
	{

		$query = $this->_createSql($query, $data);
		$rs = $this->_execute($query);
		return $rs;
	
	}

	/**
	 * Executes query from $query and $data, does not return anything
	 *
	 * @param $query string        	
	 * @param $array $data        	
	 */
	public function execute ($query, $data = array())
	{

		$query = $this->_createSql($query, $data);
		$rs = $this->_execute($query, false);
	
	}

	/**
	 * Automatically Creates an INSERT statement
	 *
	 * @param $table string        	
	 * @param $data array        	
	 * @return string
	 */
	private function _createSqlInsert ($table, $data)
	{
		// create query based on $data
		$query = "INSERT INTO `$table` (";
		$values = ' VALUES (';
		foreach ($data as $key => $value)
		{
			$query .= "`$key`, ";
			$values .= "'" . (! in_array($this->dataset[$table]['columns'][$key]['type'], unserialize(UNSANITIZEDDATATYPES)) ? Core_Helper::sanitize($value) : $value) . "', ";
		}
		$query = substr($query, 0, strlen($query) - 2) . ')';
		$values = substr($values, 0, strlen($values) - 2) . ')';
		
		return $query . $values;
	
	}

	/**
	 * Automatically Creates an Update statement
	 *
	 * @param $table string        	
	 * @param $data array        	
	 * @param $index array        	
	 * @return string
	 */
	private function _createSqlUpdate ($table, $data, $index)
	{

		$query = "UPDATE `$table` SET ";
		foreach ($data as $key => $value)
		{
			is_string($value) ? $query .= "`$key` = '" . (! in_array($this->dataset[$table]['columns'][$key]['type'], unserialize(UNSANITIZEDDATATYPES)) ? Core_Helper::sanitize($value) : $value) . "', " : $query .= "`$key` = $value, ";
		}
		$query = substr($query, 0, strlen($query) - 2);
		
		// add where clause
		$where = ' WHERE ';
		$and = '';
		foreach ($index as $key => $value)
		{
			$where .= $and . "`$key` = '$value'";
			$and = ' AND ';
		}
		
		return $query . $where;
	
	}

	/**
	 * Automatically inserts/updates the $table with values from $data based on
	 * $index
	 *
	 * @param $table string        	
	 * @param $data array        	
	 * @param $index array        	
	 */
	public function autoexecute ($table, $data, $index = null)
	{

		if (is_null($index))
		{
			// insert
			$query = $this->_createSqlInsert($table, $data);
		}
		else
		{
			// update
			$query = $this->_createSqlUpdate($table, $data, $index);
		}
		
		$this->_execute($query, false);
		
		if (is_null($index))
		{
			return $this->_db->insert_id;
		}
	
	}

	/**
	 * Lists all tables in the database
	 *
	 * @return array
	 */
	public function listTables ()
	{

		require APP_CONF . 'DB.conf.php';
		
		$query = "show tables from " . $DATABASES[$this->_dbUsed]['db'];
		$result = $this->getArray($query);
		$tables = array();
		
		foreach ($result as $data)
		{
			$tables[] = $data['Tables_in_' . $DATABASES[$this->_dbUsed]['db']];
		}
		
		return $tables;
	
	}

	/**
	 * List all fields in the given table
	 *
	 * @param $table string        	
	 * @return array
	 */
	public function listFields ($table)
	{

		require APP_CONF . 'DB.conf.php';
		
		$query = "show columns from $table";
		$result = $this->getArray($query);
		
		return $result;
	
	}

	/**
	 * Retrieves the database structure and stores it in a flat file
	 *
	 * @param $db integer        	
	 */
	public function dataPrepare ($db = DB_MAIN)
	{

		require APP_CONF . 'DB.conf.php';
		$database = $DATABASES[$db]['db'];
		
		$query = "show tables from `$database`";
		$tables = $this->getArray($query);
		
		$dataset = array();
		foreach ($tables as $table)
		{
			$data = array();
			$tablename = $table["Tables_in_$database"];
			
			// determine field of primary key
			$query = "show keys from `" . $tablename . "` where key_name = 'PRIMARY'";
			$keys = $this->getRow($query, array());
			
			$data['primary'] = $keys['Column_name'];
			unset($keys);
			
			// determine field of primary key
			$query = "show columns from `" . $tablename . "`";
			$columns = $this->getArray($query, array());
			
			foreach ($columns as $column)
			{
				$data['columns'][$column['Field']] = array('type' => $column['Type'], 'null_allowed' => $column['Null'], 'index_type' => $column['Key'], 'default_value' => $column['Default'], 'extra' => (isset($column['extra']) ? $column['extra'] : ''));
			}
			unset($columns);
			
			$dataset[$tablename] = $data;
		}
		
		$this->dataset = $dataset;
	
	}

	/**
	 * Refreshes the Database flat file with changes in the Database Structure
	 */
	public function dataRefresh ()
	{
		// prepare DB Structure and write to data file
		$this->dataPrepare();
		
		$fh = fopen(APP_DATA_DB . 'DB', 'w+');
		
		fwrite($fh, serialize($this->dataset));
		
		fclose($fh);
	
	}

	/**
	 * Retrieves the DB Structure from the flat file
	 */
	private function dataGet ()
	{
		// get DB Structure from data file
		$fh = fopen(APP_DATA_DB . 'DB', 'r');
		$this->dataset = unserialize(fread($fh, filesize(APP_DATA_DB . 'DB')));
		fclose($fh);
	
	}

	/**
	 * Class Destructor that unsets the db and dataset properties
	 */
	public function __destruct ()
	{

		unset($this->_db);
		unset($this->dataset);
	
	}

}

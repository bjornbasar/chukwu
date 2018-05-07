<?

/**
 * Unit Testing Class
 *
 * @author Sheldon Senseng
 * @copyright Sheldon Senseng <sheldonsenseng@gmail.com>
 * @version 0.1
 *         
 */
class Core_UnitTest
{

	protected $db;

	public function __construct ($replicate = false)
	{

		$this->_compareDBs($replicate);
		
		$this->_compareTables($replicate);
		
		$this->db = new Core_DB(DB_TEST);
	
	}

	private function _compareDBs ($replicate = false)
	{

		require APP_CONF . 'DB.conf.php';
		
		// verify if UnitTest DB is available
		$this->db = new Core_DB(DB_MAIN);
		
		if (! in_array($DATABASES[DB_TEST]['db'], $this->db->listDBs()))
		{
			// UnitTest DB does not exist
			if ($replicate)
			{
				$query = "create database " . $DATABASES[DB_TEST]['db'];
				$this->db->execute($query);
				
				if (! in_array($DATABASES[DB_TEST]['db'], $this->db->listDBs()))
				{
					throw new Exception("Unit Test Database does not exist (" . $DATABASES[DB_TEST]['db'] . ") and cannot be created.");
				}
			}
			else
			{
				throw new Exception("Unit Test Database does not exist (" . $DATABASES[DB_TEST]['db'] . ").");
			}
		}
	
	}

	private function _getTablesData ($db)
	{

		$this->db = new Core_DB($db);
		$tables = $this->db->listTables();
		$fields = array();
		foreach ($tables as $data)
		{
			$fields[$data] = $this->db->listFields($data);
		}
		
		return array($tables, $fields);
	
	}

	private function _compareFields ($tablesMainFields, $tablesTestFields)
	{

		$recreateTables = array();
		foreach ($tablesMainFields as $mainTable => $mainFields)
		{
			$recreateThis = false;
			if (isset($tablesTestFields[$mainTable]))
			{
				// check actual fields
				$testFields = $tablesTestFields[$mainTable];
				foreach ($mainFields as $key => $value)
				{
					if (count(array_diff($value, $testFields[$key])) > 0)
					{
						$recreateThis = true;
					}
				}
			}
			else
			{
				$recreateThis = true;
			}
			
			if ($recreateThis)
			{
				$recreateTables[] = $mainTable;
			}
		}
		return $recreateTables;
	
	}

	private function _compareTables ($replicate = false)
	{

		list ($tablesMain, $tablesMainFields) = $this->_getTablesData(DB_MAIN);
		
		list ($tablesTest, $tablesTestFields) = $this->_getTablesData(DB_TEST);
		
		if ($replicate)
		{
			$this->_createTables(array_diff($tablesMain, $tablesTest));
			
			$this->_createTables($this->_compareFields($tablesMainFields, $tablesTestFields));
		}
		else
		{
			if (count(array_diff($tablesMain, $tablesTest)) > 0 || count($this->_compareFields($tablesMainFields, $tablesTestFields)) > 0)
			{
				throw new Exception("Tables in the Main and Unit Test Databases do not match.");
			}
		}
	
	}

	private function _createTables ($tables)
	{

		require APP_CONF . 'DB.conf.php';
		foreach ($tables as $data)
		{
			$query = "show create table " . $DATABASES[DB_MAIN]['db'] . "." . $data;
			$result = $this->db->getRow($query);
			
			$this->db->execute("drop table if exists `" . $data . "`");
			$this->db->execute($result['Create Table']);
			$this->db->execute("truncate table `" . $data . "`");
		}
	
	}

	public function createSnapshot ($label)
	{

		require APP_CONF . 'DB.conf.php';
		
		$database = $DATABASES[DB_TEST];
		
		$sqlfile = UNITTEST_SNAPSHOT_PATH . UNITTEST_SNAPSHOT_PREFIX . $label . '.sql';
		$command = "mysqldump --opt -h $database[host] -u$database[username] -p$database[password] $database[db] > $sqlfile";
		
		exec($command);
	
	}

	public function loadSnapshot ($label)
	{

		$sqlfile = UNITTEST_SNAPSHOT_PATH . UNITTEST_SNAPSHOT_PREFIX . $label . '.sql';
		$command = "mysql -h $database[host] -u$database[username] -p$database[password] $database[db] < $sqlfile";
		
		exec($command);
	
	}

	public function listSnapshots ()
	{

		$files = Core_Helper::listFiles(UNITTEST_SNAPSHOT_PATH);
		
		foreach ($files as $key => $file)
		{
			if (strpos(basename($file), UNITTEST_SNAPSHOT_PREFIX) === 0)
			{
				$files[$key] = str_replace('.sql', '', str_replace(UNITTEST_SNAPSHOT_PREFIX, '', basename($file)));
			}
			else
			{
				unset($files[$key]);
			}
		}
		
		sort($files);
		
		return $files;
	
	}

	public function dumpToCSV ()
	{
		// verify the path is valid and accessible
		if (! is_dir(UNITTEST_CSV_PATH))
		{
			mkdir(UNITTEST_CSV_PATH, 0777, true);
		}
		
		$tables = $this->db->listTables();
		
		foreach ($tables as $table)
		{
			$query = "select * from `$table`";
			$result = $this->db->getArray($query);
			
			$keys = $this->db->listFields($table);
			foreach ($keys as $key => $value)
			{
				$keys[$key] = $value['Field'];
			}
			
			$csv = new Core_CSV(UNITTEST_CSV_PATH . UNITTEST_CSV_PREFIX . $table . '.csv', true);
			$csv->writeRow($keys);
			$csv->writeData($result);
		}
	
	}

	public function importFromCSV ()
	{

		$files = Core_Helper::listFiles(UNITTEST_CSV_PATH);
		
		$data = array();
		foreach ($files as $file)
		{
			$fh = fopen($file, 'r');
			
			$table = str_replace('.csv', '', str_replace(UNITTEST_CSV_PREFIX, '', basename($file)));
			
			$data[$table] = array();
			$keys = fgetcsv($fh);
			$row = fgetcsv($fh);
			while ($row)
			{
				$data[$table][] = array_combine($keys, $row);
				$row = fgetcsv($fh);
			}
			
			fclose($fh);
		}
		return $data;
	
	}

}
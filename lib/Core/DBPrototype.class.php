<?

/**
 * Class for Automatic Class Generation for Database Tables
 *
 * @author Sheldon Senseng
 * @copyright Sheldon Senseng <sheldonsenseng@gmail.com>
 * @version 0.1
 *         
 */
class Core_DBPrototype
{

	/**
	 * Return information about the database object
	 *
	 * @throws Exception
	 * @return array
	 */
	public static function _data ()
	{

		require APP_CONF . 'DB.conf.php';
		
		$db = new Core_DB();
		$table = substr(get_class(), 3);
		
		// check if table exists
		$dataset = $db->dataset;
		$exists = false;
		foreach ($dataset as $tableName => $data)
		{
			if ($table == $tableName)
			{
				$exists = true;
				if (! $data['primary'])
				{
					throw new Exception("Primary Key for the table does not exist.<br/>\nDatabase: " . $DATABASES[DB_MAIN]['db'] . "<br/>\nTable: " . $table);
				}
				$primary = $data['primary'];
			}
		}
		if (! $exists)
		{
			throw new Exception("Table does not exist.<br/>\nDatabase: " . $DATABASES[DB_MAIN]['db'] . "<br/>\nTable: " . $table);
		}
		
		return array($db, $table, $primary);
	
	}

	/**
	 * Retrieve data by SQL
	 *
	 * @param $where string        	
	 * @param $columns string        	
	 * @return array
	 */
	public static function bySql ($where = '', $columns = '*')
	{

		list ($db, $table, $primary) = Core_DBPrototype::_data();
		
		if ($where != '')
		{
			$where = ' where ' . $where;
		}
		
		return $db->getArray("select $columns from " . $table . " " . $where, array());
	
	}

	/**
	 * Retrieve data by primary key value
	 *
	 * @param $id string        	
	 * @return array
	 */
	public static function byPrimary ($id)
	{

		list ($db, $table, $primary) = Core_DBPrototype::_data();
		
		$query = "select * from `" . $table . "` where `" . $primary . "` = '$id'";
		return $db->getRow($query, array());
	
	}

	/**
	 * Retrieve data by field matching
	 *
	 * @param $data array        	
	 * @return array
	 */
	public static function byField (array $data = array())
	{

		list ($db, $table, $primary) = Core_DBPrototype::_data();
		
		if (count($data) < 1)
		{
			Core_DBPrototype::bySql();
		}
		
		$query = "select * from `" . $table . "` ";
		
		$where = "where ";
		foreach ($data as $field => $value)
		{
			if (is_array($value))
			{
				// value should be checked as 'in'
				$where .= "`$field` in (";
				foreach ($value as $in_value)
				{
					$where .= "'$in_value', ";
				}
				$where = substr($where, 0, strlen($where) - 2) . ") and ";
			}
			elseif (strpos($value, '%') !== false)
			{
				// value should be checked as 'like'
				$where .= "`$field` like '$value' and ";
			}
			else
			{
				// value should be checked as '='
				$where .= "`$field` = '$value' and ";
			}
		}
		$where = substr($where, 0, strlen($where) - 4);
		
		$query .= $where;
		
		return $db->getArray($query);
	
	}

	/**
	 * Update table data
	 *
	 * @param $data array        	
	 * @return mixed
	 */
	public static function update (array $data)
	{

		list ($db, $table, $primary) = Core_DBPrototype::_data();
		
		if (! isset($data[$primary]))
		{
			$db->autoexecute($table, $data);
		}
		else
		{
			$db->autoexecute($table, $data, array($primary => $data[$primary]));
		}
	
	}

	/**
	 * Remove table data
	 *
	 * @param $data array        	
	 * @return boolean
	 */
	public static function delete (array $data = array())
	{

		list ($db, $table, $primary) = Core_DBPrototype::_data();
		
		if (count($data) < 1)
		{
			return false;
		}
		
		$query = "delete from `" . $table . "` ";
		
		$where = "where ";
		foreach ($data as $field => $value)
		{
			if (is_array($value))
			{
				// value should be checked as 'in'
				$where .= "`$field` in (";
				foreach ($value as $in_value)
				{
					$where .= "'$in_value', ";
				}
				$where = substr($where, 0, strlen($where) - 2) . ") and ";
			}
			elseif (strpos($value, '%') !== false)
			{
				// value should be checked as 'like'
				$where .= "`$field` like '$value' and ";
			}
			else
			{
				// value should be checked as '='
				$where .= "`$field` = '$value' and ";
			}
		}
		$where = substr($where, 0, strlen($where) - 4);
		
		$query .= $where;
		
		$db->execute($query);
		
		return true;
	
	}

}
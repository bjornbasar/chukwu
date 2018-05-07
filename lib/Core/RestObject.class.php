<?

/**
 * Rest Object Class
 *
 * @author Sheldon Senseng
 * @copyright Sheldon Senseng <sheldonsenseng@gmail.com>
 * @version 0.1
 *         
 */
abstract class Core_RestObject
{

	protected $_ids = array();

	protected $_method;

	protected $_restServer;

	protected $_responseData = '';

	protected $_arguments;

	protected $_restArguments;

	protected $_restFilters;

	protected $_primaryKey;

	protected $_table;

	protected $_columns;

	protected $_conditions;

	protected $_lastCalled = '';

	protected $db;

	public function __construct ($ids = array(), $arguments = array(), $restArguments = array(), $restFilters = array())
	{

		$this->_ids = $ids;
		$this->_arguments = $arguments;
		$this->_restArguments = $restArguments;
		$this->_restFilters = $restFilters;
		$this->_parseColumns();
		$this->_parseConditions();
	
	}

	public final function setIDs ($ids)
	{

		$this->_ids = $ids;
	
	}

	public final function setMethod ($method)
	{

		$this->_method = $method;
	
	}

	public final function setRestServer ($restServer)
	{

		$this->_restServer = $restServer;
	
	}

	public final function setResponseData ($responseData)
	{
		// cleanup responseData of duplicates
		if (is_array($responseData) && isset($responseData[0]))
		{
			$this->_responseData = Core_Helper::arrayUnique($responseData);
		}
		else
		{
			$this->_responseData = Core_Helper::arrayUnique($responseData, true);
		}
		
		// convert mongoIDs to strings
		$this->_responseData = $this->_cleanMongoIds($this->_responseData);
	
	}

	protected final function _cleanMongoIds ($data)
	{

		if (is_array($data))
		{
			foreach ($data as $key => $value)
			{
				if (is_array($value))
				{
					$data[$key] = $this->_cleanMongoIds($value);
				}
				elseif ($key === '_id')
				{
					$data[$key] = $value . '';
				}
			}
		}
		
		return $data;
	
	}

	public final function getResponseData ()
	{

		return $this->_responseData;
	
	}

	public final function setLastCalled ($objectName)
	{

		$this->_lastCalled = $objectName;
	
	}

	private function _preProcess ()
	{

	}

	public final function process ()
	{

		$this->_preProcess();
		
		switch ($this->_method)
		{
			case 'GET':
				$this->_doGet();
				break;
			
			case 'HEAD':
				$this->_doHead();
				exit();
				break;
			
			case 'POST':
				$this->_validateArguments();
				$this->_doPost();
				break;
			
			case 'PUT':
				$this->_validateArguments();
				$this->_doPut();
				break;
			
			case 'DELETE':
				$this->_doDelete();
				break;
			
			default:
				$this->_restServer->setStatusCode(405);
				break;
		}
	
	}

	protected final function _parseColumns ()
	{

		if (count($this->_restFilters) < 1)
		{
			$this->_columns = '*';
		}
		else
		{
			$this->_columns = '`' . $this->_primaryKey . '`, ';
			foreach ($this->_restFilters as $filter)
			{
				$this->_columns .= '`' . $filter . '`, ';
			}
			$this->_columns = trim($this->_columns, ', ');
		}
	
	}

	protected function _parseConditions ()
	{

		if (count($this->_restArguments) < 1)
		{
			$this->_conditions = ' 1 ';
		}
		else
		{
			$like = false;
			if (isset($this->_arguments['like']) && $this->_arguments['like'])
			{
				$like = true;
			}
			
			$this->_conditions = '';
			foreach ($this->_restArguments as $key => $value)
			{
				// check for range
				if (stripos($value, '|to|') !== false)
				{
					$range = explode('|to|', $value);
					
					foreach ($range as $value2)
					{
						$value2 = trim($value2);
					}
					
					$from = ($range[0] < $range[1]) ? $range[0] : $range[1];
					$to = ($range[0] < $range[1]) ? $range[1] : $range[0];
					
					$this->_conditions .= "(`$key` between $from and $to) and ";
				}
				elseif (stripos($value, '|in|') !== false)
				{
					$this->_conditions .= "(`$key` in (" . implode(',', explode(';', str_replace('|in|', '', $value))) . ")) and ";
				}
				else
				{
					$value = Core_Helper::sanitize($value);
					if ($like)
					{
						$this->_conditions .= "`$key` like '%$value%' and ";
					}
					else
					{
						$this->_conditions .= "`$key` = '$value' and ";
					}
				}
			}
			$this->_conditions = '(' . trim($this->_conditions, ' and ') . ')';
		}
	
	}

	protected final function _validateArguments ()
	{

		$restvalidation = parse_ini_file(APP_CONF . 'restvalidation.ini', true);
		
		$passed = true;
		
		if (isset($restvalidation[$this->_table]))
		{
			$validation = $restvalidation[$this->_table];
			
			$validationRules = array();
			
			foreach ($validation as $key => $rulestring)
			{
				$validationRules[$key] = explode(' ', $rulestring);
			}
			
			switch ($this->_method)
			{
				case 'POST':
					
					// additional check for required fields
					foreach ($validation as $key => $rulestring)
					{
						$validationRules[$key] = explode(' ', $rulestring);
						if (in_array('required', $validationRules[$key]) && ! isset($this->_arguments[$key]))
						{
							$passed = false;
						}
					}
				
				case 'PUT':
					
					// validate all
					foreach ($this->_arguments as $key => $value)
					{
						foreach ($validationRules[$key] as $rule)
						{
							switch ($rule)
							{
								case 'required':
									$validator = new Zend_Validate_NotEmpty();
									break;
								
								case 'alnum':
									$validator = new Zend_Validate_Alnum();
									break;
								
								case 'alpha':
									$validator = new Zend_Validate_Alpha();
									break;
								
								case 'date':
									$validator = new Zend_Validate_Date(array('format' => VALIDATION_DATEFORMAT));
									break;
								
								case 'digits':
									$validator = new Zend_Validate_Digits();
									break;
								
								case 'email':
									$validator = new Zend_Validate_EmailAddress();
									break;
								
								case 'float':
									$validator = new Zend_Validate_Float();
									break;
								
								case 'int':
									$validator = new Zend_Validate_Int();
									break;
								
								case 'ip':
									$validator = new Zend_Validate_Ip();
									break;
							}
							
							if (! $validator->isValid($value))
							{
								$passed = false;
							}
						}
					}
					
					break;
			}
		}
		
		if (! $passed)
		{
			$this->_restServer->setStatusCode(400);
			exit();
		}
	
	}

	protected function _doGet ()
	{
		// check limit and orderby in the arguments
		$limit = '';
		$orderby = '';
		$count = false;
		
		if (isset($_GET['limit']))
		{
			$data = explode(',', $_GET['limit']);
			
			if (count($data) > 2 || count($data) == 0)
			{
				$this->_restServer->setStatusCode(400);
				exit();
			}
			
			foreach ($data as $value)
			{
				if (! is_numeric($value))
				{
					$this->_restServer->setStatusCode(404);
					exit();
				}
			}
			unset($data);
			
			$limit = ' LIMIT ' . $_GET['limit'];
		}
		
		if (isset($_GET['orderby']))
		{
			$data = explode(',', $_GET['orderby']);
			
			foreach ($data as $key => $column)
			{
				if (! isset($this->db->dataset[$this->_table]['columns'][$column]))
				{
					$this->_restServer->setStatusCode(400);
					exit();
				}
				$data[$key] = '`' . $column . '`';
			}
			
			$orderby = ' ORDER BY ' . implode(',', $data);
		}
		
		if (isset($_GET['count']) && $_GET['count'])
		{
			$count = true;
		}
		
		if ($this->_lastCalled)
		{
			$searchstring = '';
			$searchid = $this->_lastCalled . '_id';
			
			// check if column exists in table
			if (isset($this->db->dataset[$this->_table]) && isset($this->db->dataset[$this->_table]['columns'][$searchid]))
			{
				foreach ($this->_responseData as $data)
				{
					$searchstring .= $data[$searchid] . ', ';
				}
				$searchstring = trim($searchstring, ', ');
				
				if (count($this->_ids) > 0)
				{
					$query = "select " . $this->_columns . " from " . $this->_table . " where " . $this->_primaryKey . " in (" . implode(',', $this->_ids) . ") and ($searchid in ($searchstring)) and" . $this->_conditions . $orderby . $limit;
					$result = $this->db->getArray($query);
				}
				else
				{
					$query = "select " . $this->_columns . " from " . $this->_table . " where ($searchid in ($searchstring)) and " . $this->_conditions . $orderby . $limit;
					$result = $this->db->getArray($query);
				}
			}
			elseif (isset($this->_responseData[0]) && $this->_responseData[0][$this->_primaryKey])
			{
				foreach ($this->_responseData as $data)
				{
					$this->_ids[] = $data[$this->_primaryKey];
				}
				
				$this->_ids = Core_Helper::arrayUnique($this->_ids);
				
				$query = "select " . $this->_columns . " from " . $this->_table . " where " . $this->_primaryKey . " in (" . implode(',', $this->_ids) . ") and " . $this->_conditions . $orderby . $limit;
				$result = $this->db->getArray($query);
			}
			else
			{
				$this->_restServer->setStatusCode(404);
				exit();
			}
		}
		else
		{
			if (count($this->_ids) > 0)
			{
				$query = "select " . $this->_columns . " from " . $this->_table . " where " . $this->_primaryKey . " in (" . implode(',', $this->_ids) . ") and " . $this->_conditions . $orderby . $limit;
				$result = $this->db->getArray($query);
			}
			else
			{
				$query = "select " . $this->_columns . " from " . $this->_table . " where " . $this->_conditions . $orderby . $limit;
				$result = $this->db->getArray($query);
			}
		}
		
		if (count($result) < 1)
		{
			$this->_restServer->setStatusCode(404);
			exit();
		}
		
		if ($count)
		{
			$this->setResponseData(array('count' => count($result)));
		}
		else
		{
			$this->setResponseData($result);
		}
	
	}

	protected function _doPost ()
	{

		$postData = $this->_arguments;
		
		if (isset($postData[$this->_primaryKey]) && $postData[$this->_primaryKey])
		{
			$query = "select count(*) as `count` from " . $this->_table . " where `" . $this->_primaryKey . "` = '" . $postData[$this->_primaryKey] . "'";
			$result = $this->db->getRow($query);
			
			if ($result['count'] > 0)
			{
				// cannot post with value in the primarykey
				$this->_restServer->setStatusCode(400);
				exit();
			}
		}
		
		// verify that all columns passed exist in the table
		$exists = true;
		foreach ($postData as $column => $value)
		{
			if (! isset($this->db->dataset[$this->_table]['columns'][$column]))
			{
				$exists = false;
			}
		}
		
		if (! $exists)
		{
			$this->_restServer->setStatusCode(400);
			exit();
		}
		
		$id = $this->db->autoexecute($this->_table, $postData);
		
		$query = "select * from " . $this->_table . " where `" . $this->_primaryKey . "` = '$id'";
		$result = $this->db->getArray($query);
		
		$this->setResponseData($result);
	
	}

	protected function _doPut ()
	{

		$putData = $this->_arguments;
		
		if (isset($putData[$this->_primaryKey]) && $putData[$this->_primaryKey])
		{
			$query = "select count(*) as `count` from " . $this->_table . " where `" . $this->_primaryKey . "` = '" . $putData[$this->_primaryKey] . "'";
			$result = $this->db->getRow($query);
			
			if ($result['count'] < 1)
			{
				$this->_restServer->setStatusCode(404);
				exit();
			}
			
			// verify that all columns passed exist in the table
			$exists = true;
			foreach ($putData as $column => $value)
			{
				if (! isset($this->db->dataset[$this->_table]['columns'][$column]))
				{
					$exists = false;
				}
			}
			
			if (! $exists)
			{
				$this->_restServer->setStatusCode(400);
				exit();
			}
			
			$primary = $putData[$this->_primaryKey];
			unset($putData[$this->_primaryKey]);
			$this->db->autoexecute($this->_table, $putData, array($this->_primaryKey => $primary));
			
			$query = "select * from " . $this->_table . " where `" . $this->_primaryKey . "` = '$primary'";
			$result = $this->db->getArray($query);
			
			$this->setResponseData($result);
		}
		else
		{
			$this->_restServer->setStatusCode(400);
			exit();
		}
	
	}

	protected function _doDelete ()
	{

		$deleteData = $this->_arguments;
		
		if (isset($deleteData[$this->_primaryKey]) && $deleteData[$this->_primaryKey])
		{
			$query = "select count(*) as `count` from " . $this->_table . " where `" . $this->_primaryKey . "` = '" . $deleteData[$this->_primaryKey] . "'";
			$result = $this->db->getRow($query);
			
			if ($result['count'] < 1)
			{
				// cannot post with value in the primarykey
				$this->_restServer->setStatusCode(404);
				exit();
			}
			
			$query = "delete from " . $this->_table . " where `" . $this->_primaryKey . "` = '" . $deleteData[$this->_primaryKey] . "'";
			$this->db->execute($query);
		}
		else
		{
			$this->_restServer->setStatusCode(400);
			exit();
		}
	
	}

	protected function _doHead ()
	{

	}

}
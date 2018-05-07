<?

/**
 * Default REST Object Implementation that uses the Database for the objects
 *
 * @author Sheldon Senseng
 * @copyright Sheldon Senseng <sheldonsenseng@gmail.com>
 * @version 0.1
 *         
 */
class MysqlRestObject extends Core_RestObject
{

	protected $db;

	protected $_auth;

	public function __construct ($restServer, $table, $ids = array(), $arguments = array(), $restArguments = array(), $restFilters = array())
	{

		$this->_restServer = $restServer;
		$this->_table = $table;
		$this->_primaryKey = $table . '_id';
		$this->db = new Core_DB();
		
		if (isset($this->db->dataset[$this->_table]) && $this->db->dataset[$this->_table]['primary'] == $this->_primaryKey)
		{
			parent::__construct($ids, $arguments, $restArguments, $restFilters);
		}
		else
		{
			$this->_restServer->setStatusCode(404);
			exit();
		}
	
	}

	private function _preProcess ()
	{
		// user mongo id
		$requestToken = @$_SERVER['HTTP_TOKEN'];
		$moduleid = @$_SERVER['HTTP_ACL_MODULE'];
		
		// get resource to module mapping
		$RESOURCEMAPPING = unserialize(RESOURCEMAPPING);
		
		if (in_array($moduleid, $RESOURCEMAPPING[$this->_table]))
		{
			// decode requesttoken to get the user mongo id
			if ($requestToken && moduleid)
			{
				if ($this->_verifyToken($requestToken))
				{
					$data = unserialize(Core_Helper::decrypt($token));
					$mongoid = $data['id'];
					// get rolesid
					$query = "select distinct t2.permission from Users as t1 join Permissions as t2 on t1.Roles_id = t2.Roles_id and t2.Modules_id = ? where t1.cloudyway_id = ?";
					$result = $this->db->getRow($query, array($moduleid, $mongoid));
					if (count($result) > 0)
					{
						switch ($this->_method)
						{
							case 'GET':
								if (count($this->_ids) > 0)
								{
									$neededPermission = 1;
								}
								else
								{
									$neededPermission = 0;
								}
								break;
							
							case 'POST':
								$neededPermission = 3;
								break;
							
							case 'PUT':
								$neededPermission = 2;
								break;
							
							case 'DELETE':
								$neededPermission = 4;
								break;
							
							default:
								$this->_restServer->setStatusCode(401);
								exit();
								break;
						}
						
						if ($result['permission'] >= $neededPermission)
						{
							return true;
						}
						else
						{
							$this->_restServer->setStatusCode(401);
							exit();
						}
					}
					else
					{
						$this->_restServer->setStatusCode(401);
						exit();
					}
				
				}
				else
				{
					$this->_restServer->setStatusCode(401);
					exit();
				}
			
			}
			else
			{
				$this->_restServer->setStatusCode(401);
				exit();
			}
		}
		else
		{
			$this->_restServer->setStatusCode(401);
			exit();
		}
	
	}

	private function _verifyToken ($token)
	{

		$data = unserialize(Core_Helper::decrypt($token));
		
		// verify hash
		if (! isset($data['RESTAUTH_HASH']))
		{
			return false;
		}
		
		$hash = $data['RESTAUTH_HASH'];
		
		unset($data['RESTAUTH_HASH']);
		
		if ($hash != Core_Helper::generateHash(serialize($data), ENCRYPTION_SECRET))
		{
			return false;
		}
		
		// verify nonce
		if (! isset($data['RESTAUTH_NONCE']))
		{
			return false;
		}
		
		$nonce = $data['RESTAUTH_NONCE'];
		
		if ((time() - $nonce) > RESTAUTH_NONCE_LIMIT)
		{
			return false;
		}
		
		// verify type
		if (! isset($data['RESTAUTH_TYPE']))
		{
			return false;
		}
		
		if ($data['RESTAUTH_TYPE'] != $type)
		{
			return false;
		}
		
		return true;
	
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
					// $this->_restServer->setStatusCode( 400 );
					// exit();
					unset($data[$key]);
				}
				else
				{
					$data[$key] = '`' . $column . '`';
				}
			}
			
			if (count($data) > 0)
			{
				$orderby = ' ORDER BY ' . implode(',', $data);
			}
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
				echo 1;
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

		if (count($this->_ids) > 0)
		{
			foreach ($this->_ids as $id)
			{
				$query = "select count(*) as `count` from " . $this->_table . " where `" . $this->_primaryKey . "` = '" . $id . "'";
				$result = $this->db->getRow($query);
				
				if ($result['count'] < 1)
				{
					// cannot post with value in the primarykey
					$this->_restServer->setStatusCode(404);
					exit();
				}
				
				$query = "delete from " . $this->_table . " where `" . $this->_primaryKey . "` = '" . $id . "'";
				$this->db->execute($query);
			}
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
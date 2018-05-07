<?

/**
 * Default REST Object Implementation that uses the MongoDB Database for the objects
 *
 * @author Sheldon Senseng
 * @copyright Sheldon Senseng <sheldonsenseng@gmail.com>
 * @version 0.1
 *         
 */
class MongoRestObject extends Core_RestObject
{

	protected $db;

	public function __construct ($restServer, $table, $ids = array(), $arguments = array(), $restArguments = array(), $restFilters = array(), $lastCalled = null)
	{

		$this->_restServer = $restServer;
		$this->_table = $table;
		
		if (Core_Mongo::hasCollection($table) && ! $lastCalled)
		{
			$this->db = new Core_Mongo($table);
		}
		else
		{
			$this->db = new Core_Mongo($lastCalled);
		}
		
		parent::__construct($ids, $arguments, $restArguments, $restFilters);
	
	}

	protected function _parseConditions ()
	{

		if (count($this->_restArguments) < 1)
		{
			$this->_conditions = array();
		}
		else
		{
			$like = false;
			if (isset($this->_arguments['like']) && $this->_arguments['like'])
			{
				$like = true;
			}
			
			$likestart = false;
			if (isset($this->_arguments['likestart']) && $this->_arguments['likestart'])
			{
				$likestart = true;
			}
			
			$this->_conditions = array();
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
					
					$this->_conditions[][$key] = array('$gte' => $from);
					$this->_conditions[][$key] = array('$lte' => $to);
				}
				else
				{
					if ($like)
					{
						$this->_conditions[][$key] = array('$regex' => new MongoRegex('/.*' . addslashes($value) . '.*/i'));
					}
					elseif ($likestart)
					{
						$this->_conditions[][$key] = array('$regex' => new MongoRegex('/^' . addslashes($value) . '.*/i'));
					}
					else
					{
						$this->_conditions[][$key] = $value;
					}
				}
			}
			if (count($this->_conditions) > 1)
			{
				$this->_conditions = array('$and' => $this->_conditions);
			}
			elseif (count($this->_conditions) == 1)
			{
				$this->_conditions = array_shift($this->_conditions);
			}
		}
	
	}

	protected function _doGet ()
	{
		// check limit and orderby in the arguments
		$limit = null;
		$skip = null;
		$orderby = array();
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
					$this->_restServer->setStatusCode(400);
					exit();
				}
			}
			
			if (count($data) == 2)
			{
				$skip = array_shift($data);
			
			}
			$limit = array_shift($data);
			
			unset($data);
		}
		
		if (isset($_GET['start']))
		{
			$skip = $_GET['start'];
		}
		
		if (isset($_GET['orderby']))
		{
			$data = explode(',', $_GET['orderby']);
			
			foreach ($data as $row)
			{
				list ($key, $value) = explode(' ', $row);
				if (! $value)
				{
					$value = '1';
				}
				$orderby[$key] = (int) $value;
			}
			
			unset($data);
		}
		
		if (isset($_GET['count']) && $_GET['count'])
		{
			$count = true;
		}
		
		if ($this->_lastCalled)
		{
			$structure = Core_Mongo::getCurrentStructure($this->_lastCalled);
			$objectsCalled = $this->_restServer->_objectsCalled;
			array_pop($objectsCalled);
			foreach ($objectsCalled as $lastObject)
			{
				$temp = Core_Mongo::_getStructureSubset($lastObject, $structure);
				$subset = array();
				foreach ($temp as $k2 => $v2)
				{
					$first = explode('.', $k2);
					$first = array_shift($first);
					$subset[str_replace($first . '.', '', $k2)] = $v2;
				}
				
				$structure = $subset;
			}
			
			if (isset($structure[$this->_table]) && in_array('DocumentSet', $structure[$this->_table]['type']))
			{
				$temp = $this->_responseData;
				
				$data = array();
				if (isset($temp[$this->_table]))
				{
					$data = $temp[$this->_table];
				}
				else
				{
					foreach ($temp as $key => $value)
					{
						if (is_array($value) && isset($value[$this->_table]))
						{
							$data = $value[$this->_table];
						}
					}
				}
				
				if (count($this->_ids) < 1)
				{
					$result = $data;
				}
				else
				{
					$result = array();
					foreach ($this->_ids as $id)
					{
						foreach ($data as $row)
						{
							if ($row['_id'] == $id)
							{
								$result[] = $row;
							}
						}
					}
				}
				
				// process restarguments
				if (count($this->_restArguments) > 0)
				{
					$data = $result;
					$result = array();
					foreach ($data as $row)
					{
						foreach ($this->_restArguments as $key => $value)
						{
							if (isset($row[$key]) && $row[$key] == $value)
							{
								$result[] = $row;
							}
						}
					}
				}
			}
			elseif (isset($structure[$this->_table]) && in_array('Document', $structure[$this->_table]['type']))
			{
				
				$temp = $this->_responseData;
				$data = array();
				foreach ($temp as $key => $value)
				{
					if (isset($value[$this->_table]))
					{
						$data = $value[$this->_table];
					}
				}
				$result = $data;
				
				// process restarguments
				if (count($this->_restArguments) > 0)
				{
					$data = $result;
					$result = array();
					foreach ($data as $row)
					{
						foreach ($this->_restArguments as $key => $value)
						{
							if (isset($row[$key]) && $row[$key] == $value)
							{
								$result[] = $row;
							}
						}
					}
				}
			}
			else
			{
				$this->_restServer->setStatusCode(404);
			}
		}
		else
		{
			if (count($this->_ids) > 0)
			{
				$doc = $this->db->getNewDocument();
				$ids = array();
				foreach ($this->_ids as $id)
				{
					$ids['_id']['$in'][] = new MongoID($id);
				}
				
				if (count($this->_conditions) > 0)
				{
					$this->_conditions['$and'][] = $ids;
				}
				else
				{
					$this->_conditions = $ids;
				}
				$result = $this->db->query($this->_conditions, $limit, $skip, $orderby);
			}
			else
			{
				$result = $this->db->query($this->_conditions, $limit, $skip, $orderby);
			}
		}
		
		if (count($result) < 1)
		{
			if (! $this->_restServer->_hasParameter())
			{
				echo Zend_Json::encode(array());
				exit();
			}
		}
		
		if (count($orderby) > 0 && count($result) > 0)
		{
			foreach ($orderby as $sortkey => $sortdirection)
			{
				$temp = array();
				foreach ($result as $key => $value)
				{
					if (isset($value[$sortkey]))
					{
						$temp[$key] = $value[$sortkey];
					}
				}
				
				if (count($temp) > 0)
				{
					array_multisort($temp, ($sortdirection == 1) ? SORT_ASC : SORT_DESC, $result);
				}
			}
		}
		
		if ($count)
		{
			$this->setResponseData(array('count' => count($result)));
		}
		else
		{
			// removal of non-standard rest parameter in response
			// $result = array('data' => $result, 'count' => $this->db->count());
			$this->setResponseData($result);
		}
	
	}

	protected function _doPost ()
	{

		if ($this->_lastCalled)
		{
			$structure = Core_Mongo::getCurrentStructure($this->_lastCalled);
			$objectsCalled = $this->_restServer->_objectsCalled;
			array_pop($objectsCalled);
			foreach ($objectsCalled as $lastObject)
			{
				$temp = Core_Mongo::_getStructureSubset($lastObject, $structure);
				$subset = array();
				foreach ($temp as $k2 => $v2)
				{
					$first = explode('.', $k2);
					$first = array_shift($first);
					$subset[str_replace($first . '.', '', $k2)] = $v2;
				}
				
				$structure = $subset;
			}
			
			if (isset($structure[$this->_table]) && (in_array('DocumentSet', $structure[$this->_table]['type']) || in_array('Document', $structure[$this->_table]['type'])))
			{
				$data[$this->_table] = array(array_merge($this->_arguments));
				$responseData = $data;
				$temp = '$result';
				$result = array();
				foreach ($objectsCalled as $object)
				{
					$temp .= "['$object']";
				}
				$temp .= ' = $data;';
				eval($temp);
				$data = $result;
				
				$id = new MongoID($this->_restServer->mainIDs[0]);
				$result = $this->db->update($id, $data, false);
				
				$responseData[$this->_table][0]['_id'] = $result['_id'];
				
				$this->_restServer->setStatusCode($result['status']);
				$this->setResponseData($responseData[$this->_table][0]);
			}
		}
		else
		{
			$result = $this->db->create($this->_arguments);
			
			$this->_restServer->setStatusCode($result['status']);
			$this->setResponseData($result['data']);
		}
	
	}

	protected function _doPut ()
	{

		if ($this->_lastCalled)
		{
			$structure = Core_Mongo::getCurrentStructure($this->_lastCalled);
			$objectsCalled = $this->_restServer->_objectsCalled;
			array_pop($objectsCalled);
			foreach ($objectsCalled as $lastObject)
			{
				$temp = Core_Mongo::_getStructureSubset($lastObject, $structure);
				$subset = array();
				foreach ($temp as $k2 => $v2)
				{
					$first = explode('.', $k2);
					$first = array_shift($first);
					$subset[str_replace($first . '.', '', $k2)] = $v2;
				}
				
				$structure = $subset;
			}
			
			if (count($this->_ids) == 1)
			{
				if (isset($structure[$this->_table]) && in_array('DocumentSet', $structure[$this->_table]['type']))
				{
					$data[$this->_table] = array(array_merge($this->_arguments, array('_id' => new MongoID($this->_ids[0]))));
					$responseData = $data;
					$temp = '$result';
					$result = array();
					foreach ($objectsCalled as $object)
					{
						$temp .= "['$object']";
					}
					$temp .= ' = $data;';
					eval($temp);
					$data = $result;
					
					$id = new MongoID($this->_restServer->mainIDs[0]);
					
					$result = $this->db->update($id, $data, false);
					
					$this->_restServer->setStatusCode($result['status']);
					$this->setResponseData($responseData[$this->_table][0]);
				}
			}
			else
			{
				if (isset($structure[$this->_table]) && in_array('Document', $structure[$this->_table]['type']))
				{
					$data[$this->_table] = $this->_arguments;
					$data['_id'] = new MongoID($this->_restServer->mainIDs[0]);
					
					$id = new MongoID($this->_restServer->mainIDs[0]);
					
					$result = $this->db->update($id, $data, false);
					
					$this->_restServer->setStatusCode($result['status']);
					$this->setResponseData($data[$this->_table]);
				}
			}
		}
		else
		{
			if (count($this->_ids) == 1)
			{
				$postData = $this->_ids;
				$id = new MongoID($this->_ids[0]);
				
				$result = $this->db->update($id, $this->_arguments, false);
				
				$this->_restServer->setStatusCode($result['status']);
				$this->setResponseData($result['data']);
			}
		}
	
	}

	protected function _doDelete ()
	{

		if (count($this->_ids) == 1)
		{
			if ($this->_lastCalled)
			{
				$structure = Core_Mongo::getCurrentStructure($this->_lastCalled);
				$objectsCalled = $this->_restServer->_objectsCalled;
				array_pop($objectsCalled);
				foreach ($objectsCalled as $lastObject)
				{
					$temp = Core_Mongo::_getStructureSubset($lastObject, $structure);
					$subset = array();
					foreach ($temp as $k2 => $v2)
					{
						$first = explode('.', $k2);
						$first = array_shift($first);
						$subset[str_replace($first . '.', '', $k2)] = $v2;
					}
					
					$structure = $subset;
				}
				
				$data = array();
				
				$id = new MongoID($this->_restServer->mainIDs[0]);
				
				$result = $this->db->update($id, $data, array('_id' => new MongoID($this->_ids[0])));
				
				$this->_restServer->setStatusCode($result['status']);
				$this->setResponseData(array());
			}
			else
			{
				$id = new MongoID($this->_ids[0]);
				$result = $this->db->delete($id, $this->_arguments);
				
				$this->_restServer->setStatusCode($result['status']);
				$this->setResponseData($result['data']);
			}
		}
		else
		{
			$this->_restServer->setStatusCode(404);
		}
	
	}

	protected function _doHead ()
	{

	}

}
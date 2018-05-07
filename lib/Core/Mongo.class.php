<?php

/**
 *
 * @author Sheldon
 *        
 *        
 */
class Core_Mongo
{

	protected $_connection;

	protected $_config;

	public $_structure;

	protected $_database;

	protected $_collection;

	protected $_server;

	protected $_document;

	public function __construct ($collection)
	{

		if (! $collection)
		{
			throw new Exception('No collection provided');
		}
		else
		{
			$this->_collection = $collection;
			
			$this->_config = parse_ini_file(APP_CONF . 'mongodb.ini', true);
			
			$this->_server = @$this->_config['config']['server'];
			$this->_database = @$this->_config['config']['database'];
			
			if (! $this->_server)
			{
				throw new Exception('No Server configured');
			}
			elseif (! $this->_database)
			{
				throw new Exception('No Database configured');
			}
			else
			{
				$this->_connection = new Shanty_Mongo_Connection('mongodb://' . $this->_server . '/' . $this->_database);
			}
			
			$this->_parseStructures();
			
			$this->_document = $this->_createDocument();
		}
	
	}

	public function getNewDocument ()
	{

		return $this->_createDocument();
	
	}

	protected function _parseStructures ()
	{

		$file = APP_CONF . 'mongodb.json';
		$fh = fopen($file, 'r');
		$data = fread($fh, filesize($file));
		$this->_structure = Zend_Json::decode($data);
		fclose($fh);
	
	}

	public static function getStructure ()
	{

		$file = APP_CONF . 'mongodb.json';
		$fh = fopen($file, 'r');
		$data = fread($fh, filesize($file));
		fclose($fh);
		return Zend_Json::decode($data);
	
	}

	public static function hasCollection ($collection)
	{

		$structure = self::getStructure();
		$hasCollection = false;
		foreach ($structure as $data)
		{
			if ($data['collection'] == $collection)
			{
				$hasCollection = true;
			}
		}
		return $hasCollection;
	
	}

	protected function _createDocument ($collection = null)
	{

		if (is_null($collection))
		{
			$classname = 'MongoDoc_' . $this->_database . '_' . $this->_collection;
		}
		else
		{
			$classname = 'MongoDoc_' . $this->_database . '_' . $collection;
		}
		
		$document = new $classname();
		return $document;
	
	}

	public function _loadReferences ($document, $data)
	{
		// This function converts mongoID input in str to a $dbref for proper validation
		foreach ($data as $key => $value)
		{
			$prop = $document->getProperty($key);
			if ($prop instanceof Shanty_Mongo_Document && $document->isReference($prop))
			{
				$dbref = $this->_createDocument($key);
				$ref = $dbref::find($value);
				if (! $ref)
				{
					$data[$key] = null;
				}
				else
				{
					$data[$key] = $ref;
				}
			}
		}
		return $data;
	
	}

	public function count ()
	{

		$module = $this->_createDocument();
		return $module::all()->count();
	
	}

	public function query ($condition = array(), $limit = null, $skip = null, $sort = null)
	{

		$returnvalue = array();
		$module = $this->_createDocument();
		
		$data = $module::all($condition);
		
		if (! is_null($skip))
		{
			$data->skip($skip);
		}
		
		if (! is_null($limit))
		{
			$data->limit($limit);
		}
		
		if (! is_null($sort) && count($sort) > 0)
		{
			$data->sort($sort);
		}
		
		while ($row = $data->getNext())
		{
			$returnvalue[] = $row->export();
		}
		
		if (! is_null($sort) && count($sort) > 0)
		{
			foreach ($sort as $sortkey => $direction)
			{
				$temp = array();
				foreach ($returnvalue as $key => $value)
				{
					if (isset($value[$sortkey]))
					{
						$temp[$key] = $value[$sortkey];
					}
				}
				
				if (count($temp) > 0)
				{
					array_multisort($temp, ($direction == 1) ? SORT_ASC : SORT_DESC, $returnvalue);
				}
			}
		}
		
		return $returnvalue;
	
	}

	protected function _validationError ($error)
	{

		echo Zend_Json::encode($error);
		exit();
	
	}

	public static function getCurrentStructure ($collection)
	{

		$structure = false;
		foreach (self::getStructure() as $row)
		{
			if ($row['collection'] == $collection)
			{
				$structure = $row['properties'];
			}
		}
		
		return $structure;
	
	}

	public static function _getStructureSubset ($prefix, $structure)
	{

		$returnvalue = array();
		foreach ($structure as $field => $value)
		{
			if (strpos($field, $prefix . '.') === 0)
			{
				$returnvalue[$field] = $value;
			}
		}
		
		return $returnvalue;
	
	}

	protected function _processDefaults ($data, $type, $structure = null, $isDocumentSet = false)
	{

		if (is_null($structure))
		{
			$structure = self::getCurrentStructure($this->_collection);
		}
		
		foreach ($structure as $field => $value)
		{
			if (isset($value['type']) && is_array($value['type']) && in_array('DocumentSet', $value['type']) && isset($data[ltrim(substr($field, strrpos($field, '.')), '.')]))
			{
				$subset = $this->_getStructureSubset($field, $structure);
				
				$data[ltrim(substr($field, strrpos($field, '.')), '.')] = $this->_processDefaults($data[ltrim(substr($field, strrpos($field, '.')), '.')], $type, $subset, true);
			}
			
			if (((isset($value['type']) && isset($value['type']['default']) && isset($value['type']['default'][$type])) || (isset($value['default']) && isset($value['default'][$type]))) && ! isset($data[ltrim(substr($field, strrpos($field, '.')), '.')]))
			{
				if (isset($value['default']))
				{
					$default = $value['default'][$type];
				}
				else
				{
					$default = $value['type']['default'][$type];
				}
				
				switch ($default["type"])
				{
					case "datetime":
						$defaultvalue = date("Y-m-d H:i:s");
						break;
					
					case "boolean":
						if ($default["value"] == "true")
						{
							$defaultvalue = true;
						}
						else
						{
							$defaultvalue = false;
						}
						break;
					
					case "integer":
						$defaultvalue = (int) $default["value"];
						break;
					
					default:
						$defaultvalue = $default["value"];
				}
				
				if ($isDocumentSet)
				{
					foreach ($data as $key2 => $value)
					{
						$data[$key2][ltrim(substr($field, strrpos($field, '.')), '.')] = $defaultvalue;
					}
				}
				else
				{
					$data[ltrim(substr($field, strrpos($field, '.')), '.')] = $defaultvalue;
				}
			}
		}
		
		return $data;
	
	}

	protected function _getCurrentStructureArray ()
	{

		$structure = self::getCurrentStructure($this->_collection);
		
		$result = array();
		
		foreach ($structure as $key => $value)
		{
			$newkey = str_replace('.$', '', $key);
			
			$temp = explode('.', $newkey);
			
			$cmd = '$result';
			foreach ($temp as $tempkey)
			{
				$cmd .= '["' . $tempkey . '"]';
			}
			$cmd .= '="";';
			eval($cmd);
		}
		
		return $result;
	
	}

	protected function _validateData ($data, $structure = null)
	{

		if (is_null($structure))
		{
			$structure = self::_getCurrentStructureArray();
		}
		
		$valid = true;
		foreach ($data as $key => $value)
		{
			if (! isset($structure[$key]))
			{
				if (is_int($key))
				{
					$check = self::_validateData($value, $structure);
					
					if ($check == false)
					{
						$valid = false;
					}
				}
				else
				{
					$valid = false;
				}
			}
			else
			{
				if (is_array($value))
				{
					foreach ($data[$key] as $item)
					{
						$check = self::_validateData($data[$key], $structure[$key]);
						
						if ($check == false)
						{
							$valid = false;
						}
					}
				}
			}
		}
		
		return $valid;
	
	}

	public function create ($data)
	{
		// verify that data conforms to structure
		if (self::_validateData($data))
		{
			$document = $this->_createDocument();
			
			$data = $this->_loadReferences($document, $data);
			
			$validatorKeys = $this->_parseValidatorKeys($data);
			
			// default value processing
			$data = $this->_processDefaults($data, 'create');
			
			list ($valid, $errors) = $this->_processValidation($data, $validatorKeys);
			
			if ($valid && count($errors) < 1)
			{
				$connection = 'mongodb://' . $this->_server . ':27017';
				$mongo = new Mongo($connection);
				$db = $mongo->selectDB($this->_database);
				$collection = $db->selectCollection($this->_collection);
				
				$result = $collection->insert($data);
				
				return array('status' => 200, 'data' => $data);
			}
			else
			{
				return array('status' => 400, 'data' => $this->_validationError($errors));
			}
		}
		else
		{
			return array('status' => 400);
		}
	
	}

	public function _processValidation ($data, $validatorKeys, $valid = true, $errors = array())
	{

		foreach ($data as $key => $value)
		{
			if (is_array($value))
			{
				list ($valid, $errors, $cmd) = $this->_processValidation($value, $validatorKeys[$key]['validator'], $valid, $errors);
			}
			else
			{
				$path = $validatorKeys[$key]['name'];
				$validators = $validatorKeys[$key]['validator'];
				if (! $validators || $validators->isValid($value))
				{
				}
				else
				{
					$valid = false;
					$errors[] = array('key' => $path, $validators->getMessages());
				}
			}
		}
		
		return array($valid, $errors);
	
	}

	public function _parseValidatorKeys ($data, $prefix = '', $type = 'insert')
	{

		$document = $this->_createDocument();
		
		$validatorKeys = array();
		
		foreach ($data as $key => $value)
		{
			if (is_array($value))
			{
				$validatorKeys[$key] = array('name' => ltrim($prefix . '.' . $key, '.'), 'validator' => $this->_parseValidatorKeys($value, ltrim($prefix . '.' . (is_int($key) ? '$' : $key), '.'), $type));
			}
			else
			{
				$validatorKeys[$key] = array('name' => ltrim($prefix . '.' . $key, '.'), 'validator' => $document->getValidators(ltrim($prefix . '.' . $key, '.')));
			}
		}
		
		return $validatorKeys;
	
	}

	public function read ($id)
	{

		$module = $this->_createDocument();
		
		$document = $module::find($id);
		
		if (! $document)
		{
			return array('status' => 404, 'data' => '');
		}
		
		return array('status' => 200, 'data' => $document->export());
	
	}

	public function update ($id, $data, $withDelete = false)
	{

		$module = $this->_createDocument();
		
		$document = $module::find($id);
		
		if (! $document)
		{
			return array('status' => 404, 'data' => '');
		}
		
		$valid = true;
		$errors = array();
		$data = $this->_loadReferences($document, $data);
		
		$validatorKeys = $this->_parseValidatorKeys($data, '', 'update');
		
		list ($valid, $errors) = $this->_processValidation($data, $validatorKeys);
		
		if ($valid && count($errors) < 1)
		{
			$data['_id'] = new MongoID($id);
			
			$connection = 'mongodb://' . $this->_server . ':27017';
			$mongo = new Mongo($connection);
			$db = $mongo->selectDB($this->_database);
			$collection = $db->selectCollection($this->_collection);
			
			$doc = $collection->findOne(array('_id' => $data['_id']));
			
			$insertedID = null;
			if ($withDelete)
			{
				// remove the delete flag
				if (isset($data['mongoRestDelete']))
				{
					unset($data['mongoRestDelete']);
				}
				
				// remove the mongoID
				unset($data['_id']);
				
				foreach ($withDelete as $k => $v)
				{
					$doc = Core_Helper::removeFromArrayByKeyValue($doc, $k, $v);
				}
				$data = $doc;
			}
			else
			{
				$structure = self::getCurrentStructure($this->_collection);
				
				list ($data, $insertedID) = self::_addMongoIDs($doc, $data, $structure);
				
				$data = Core_Helper::array_overlay($doc, $data, '_id');
			}
			
			$result = $collection->save($data, array('safe' => true));
			
			return array('status' => 200, 'data' => $data, '_id' => $insertedID);
		}
		else
		{
			return array('status' => 400, 'data' => $this->_validationError($errors));
		}
	
	}

	protected function _addMongoIDs ($doc, $data, $structure)
	{

		foreach ($data as $key => $value)
		{
			if (in_array('DocumentSet', $structure[$key]['type']))
			{
				
				foreach ($value as $idx => $row)
				{
					if (! isset($row['_id']))
					{
						$data[$key][$idx]['_id'] = new MongoID();
						$insertedID = $data[$key][$idx]['_id'];
					}
					else
					{
						foreach ($doc[$key] as $k => $v)
						{
							if ($v['_id'] . '' == $row['_id'])
							{
								foreach ($row as $k2 => $v2)
								{
									if ($k2 != '_id')
									{
										$doc[$key][$k][$k2] = $v2;
									}
								}
							}
						}
					}
				}
			}
			elseif (is_array($value))
			{
				$temp = self::_getStructureSubset($key, $structure);
				$subset = array();
				foreach ($temp as $k2 => $v2)
				{
					$first = explode('.', $k2);
					$first = array_shift($first);
					$subset[str_replace($first . '.', '', $k2)] = $v2;
				}
				
				list ($data[$key], $insertedID) = self::_addMongoIDs($doc[$key], $value, $subset);
			}
		}
		
		return array($data, $insertedID);
	
	}

	public function delete ($id, $data = array())
	{

		$module = $this->_createDocument();
		
		$document = $module::find($id);
		
		if (! $document)
		{
			return array('status' => 404, 'data' => '');
		}
		
		$document->delete();
		return array('status' => 200, 'data' => '');
	
	}

}


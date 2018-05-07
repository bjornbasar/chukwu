<?

/**
 * Rest Server Class
 *
 * @author Sheldon Senseng
 * @copyright Sheldon Senseng <sheldonsenseng@gmail.com>
 * @version 0.1
 *         
 */
abstract class Core_RestServer
{

	protected $_supportedMethods = 'GET,HEAD,POST,PUT,DELETE';

	protected $_module;

	protected $_url;

	protected $_method;

	protected $_arguments = array();

	protected $_restParameters = array();

	protected $_restArguments = array();

	protected $_restFilters = array();

	protected $_accept;

	protected $_restParametersUrl;

	protected $_responseStatus = 200;

	protected $_restAuthHeaders = array('username' => '', 'password' => '');

	protected $_data = array();

	protected $_responseData = false;

	protected $_lastCalled = '';

	protected $_type = 'mysql';

	public $_objectsCalled = array();

	protected $_authKey;

	/**
	 * Instantiate the Rest Server endpoint
	 */
	public function __construct ()
	{
		// check if ALLOWEDHOSTS_LIMIT is set to true and verify request host if so
		if (ALLOWEDHOSTS_LIMIT)
		{
			$client = parse_url($_SERVER['HTTP_REFERER']);
			
			if (isset($client['host']))
			{
				if (! in_array($client['host'], unserialize(ALLOWEDHOSTS)))
				{
					// check if request has an auth token
					if (! $this->_checkRestAuth())
					{
						$this->setStatusCode(403);
						echo "Web page encountered some problem. Check again later.";
						exit();
					}
				}
			}
			else
			{
				// check if request has an auth token
				if (! $this->_checkRestAuth())
				{
					$this->setStatusCode(403);
					echo "Web page encountered some problem. Check again later.";
					exit();
				}
			}
		}
		
		$this->_module = str_replace('Rest_', '', get_class($this));
		$this->_url = $this->getFullUrl($_SERVER);
		$this->_method = $_SERVER['REQUEST_METHOD'];
		$this->_accept = @$_SERVER['HTTP_ACCEPT'];
		$this->getArguments();
		$this->parseRestParameters();
		
		if (LOGRESTCALLS)
		{
			
			Core_Helper::messageLog("URL:\n" . $this->_url);
			Core_Helper::messageLog("Method:\n" . $this->_method);
			Core_Helper::messageLog("Arguments:\n" . serialize($this->_arguments));
			Core_Helper::messageLog("REST Parameters:\n" . serialize($this->_restParameters));
			Core_Helper::messageLog("REST Arguments:\n" . serialize($this->_restArguments));
		}
		
		$this->_processNext();
	
	}

	protected final function _checkRestAuth ()
	{

		if (! RESTAUTHHASH_LIMIT)
		{
			return true;
		}
		else
		{
			$restAuthConf = parse_ini_file(APP_CONF . 'REST/auth.ini');
			$restAuthHeader = @$_SERVER['HTTP_REDRESTAUTH'];
			$restAuthHash = @$_SERVER['HTTP_REDRESTHASH'];
			
			if (! isset($restAuthConf[$restAuthHeader]) || ! $restAuthHash || ! $restAuthHeader)
			{
				return false;
			}
			else
			{
				$this->_authKey = $restAuthConf[$restAuthHeader];
				$querystring = $_SERVER['QUERY_STRING'];
				
				$hash = hash_hmac('sha1', $querystring, $this->_authKey);
				
				if ($hash != $restAuthHash)
				{
					return false;
				}
				else
				{
					return true;
				}
			}
		}
	
	}

	/**
	 * Retrieve data from the Rest Server
	 *
	 * @param $var string        	
	 */
	public final function __get ($var)
	{

		if (isset($this->_data[$var]))
		{
			return $this->_data[$var];
		}
		return null;
	
	}

	/**
	 * Set data for the REST Server
	 *
	 * @param $var string        	
	 * @param $value string        	
	 */
	public final function __set ($var, $value)
	{

		$this->_data[$var] = $value;
	
	}

	/**
	 * Class destructor
	 */
	public function __destruct ()
	{

	}

	/**
	 * Retrieve all REST Arguments
	 */
	protected final function getArguments ()
	{

		switch ($this->_method)
		{
			case 'GET':
			case 'HEAD':
				$this->_arguments = $_GET;
				break;
			
			case 'POST':
				$this->_arguments = $_POST;
				break;
			
			case 'PUT':
			case 'DELETE':
				parse_str(file_get_contents('php://input'), $this->_arguments);
				$this->_arguments = array_merge($this->_arguments, $_GET);
				break;
			
			default:
				header('Allow: ' . $this->_supportedMethods, true, 501);
				break;
		}
	
	}

	/**
	 * Retrieve the full REST Call
	 *
	 * @param $_SERVER string        	
	 * @return string
	 */
	protected final function getFullUrl ($currentServer = null)
	{

		if (is_null($currentServer))
		{
			$currentServer = $_SERVER;
		}
		$protocol = @$currentServer['HTTPS'] == 'on' ? 'https' : 'http';
		$location = $currentServer['REQUEST_URI'];
		
		if ($currentServer['QUERY_STRING'])
		{
			$location = substr($location, 0, strrpos($location, $currentServer['QUERY_STRING']) - 1);
		}
		
		return $protocol . '://' . $currentServer['HTTP_HOST'] . $location;
	
	}

	/**
	 * Header response for unallowed methods
	 */
	protected final function _methodNotAllowedResponse ()
	{

		header('Allow: ' . $this->_supportedMethods, true, 405);
	
	}

	/**
	 * Process the REST call parameters
	 */
	protected final function parseRestParameters ()
	{

		$this->_restParametersUrl = trim(trim(str_replace(APP_URI . MODULE_FULLNAME . '/', '', $this->_url)), '/');
		$this->_restParameters = ! ! $this->_restParametersUrl ? explode('/', $this->_restParametersUrl) : array();
		
		foreach ($this->_restParameters as $key => $parameter)
		{
			$parameter = urldecode($parameter);
			
			$restFilters = array();
			$arguments = array();
			
			$matches = array();
			if (preg_match('/\{.*\}/', $parameter, $matches))
			{
				$this->_restParameters[$key] = str_replace($matches[0], '', $parameter);
				$restFilters = explode(',', substr($matches[0], 1, strlen($matches[0]) - 2));
			}
			
			$matches = array();
			if (preg_match('/\[.*\]/', $parameter, $matches))
			{
				$this->_restParameters[$key] = str_replace($matches[0], '', $parameter);
				$restArguments = explode(',', substr($matches[0], 1, strlen($matches[0]) - 2));
				
				foreach ($restArguments as $restArgument)
				{
					if ($restArgument)
					{
						list ($restArgumentKey, $restArgumentValue) = explode('=', $restArgument);
						$arguments[$restArgumentKey] = $restArgumentValue;
					}
				}
			}
			
			$matches = array();
			if (preg_match('/\{.*\}/', $this->_restParameters[$key], $matches))
			{
				$this->_restParameters[$key] = str_replace($matches[0], '', $this->_restParameters[$key]);
				$restFilters = explode(',', substr($matches[0], 1, strlen($matches[0]) - 2));
			}
			
			$matches = array();
			if (preg_match('/\[.*\]/', $this->_restParameters[$key], $matches))
			{
				$this->_restParameters[$key] = str_replace($matches[0], '', $this->_restParameters[$key]);
				$restArguments = explode(',', substr($matches[0], 1, strlen($matches[0]) - 2));
				
				foreach ($restArguments as $restArgument)
				{
					if ($restArgument)
					{
						list ($restArgumentKey, $restArgumentValue) = explode('=', $restArgument);
						$arguments[$restArgumentKey] = $restArgumentValue;
					}
				}
			}
			
			$this->_restFilters[$this->_restParameters[$key]] = $restFilters;
			
			if (! $this->_restParameters[$key])
			{
				unset($this->_restParameters[$key]);
			}
			
			if ($this->_restParameters[$key])
			{
				$lastKey = $key;
			}
			else
			{
				$key = $lastKey;
			}
			$this->_restArguments[$this->_restParameters[$key ? $key : $lastKey]] = $arguments;
		}
	
	}

	/**
	 * Process the REST Call authentication headers
	 */
	public final function getRequestAuth ()
	{

		return array('username' => '' . @$_SERVER['PHP_AUTH_USER'], 'password' => '' . @$_SERVER['PHP_AUTH_PW']);
	
	}

	/**
	 * Retrieve data from the Rest Server
	 *
	 * @param $var string        	
	 * @return mixed
	 */
	public final function get ($var)
	{

		$var = '_' . $var;
		if (isset($this->$var))
		{
			return $this->$var;
		}
		else
		{
			return null;
		}
	
	}

	/**
	 * Set HTTP Header status code for response
	 *
	 * @param $code string        	
	 */
	public final function setStatusCode ($code)
	{

		$this->_responseStatus = $code;
		header("HTTP/1.1 $code", true, $code);
	
	}

	/**
	 * Returns the response data in JSON format when the object is called as a string
	 *
	 * @return string
	 */
	public final function __toString ()
	{

		return Zend_Json::encode($this->_responseData);
	
	}

	/**
	 * Checks and returns the next REST Parameter
	 *
	 * @return string
	 */
	protected final function _getNextParameter ()
	{

		if ($this->_hasParameter())
		{
			return array_shift($this->_restParameters);
		}
		else
		{
			return false;
		}
	
	}

	/**
	 * Checks if there is a succeeding REST Parameter
	 *
	 * @return boolean
	 */
	public final function _hasParameter ()
	{

		if (count($this->_restParameters) > 0)
		{
			return true;
		}
		else
		{
			return false;
		}
	
	}

	/**
	 * Returns a parameter onto the object list
	 *
	 * @param $parameter string        	
	 */
	protected final function _returnParameter ($parameter)
	{

		array_unshift($this->_restParameters, $parameter);
	
	}

	/**
	 * Checks if the next parameter is an ID
	 *
	 * @return boolean
	 */
	protected final function _isNextParameterId ()
	{

		if ($this->_hasParameter())
		{
			$parameter = $this->_getNextParameter();
			
			$data = explode(',', $parameter);
			$isId = true;
			
			foreach ($data as $value)
			{
				if (! is_numeric($value) && ! Core_Helper::isMongoID($value))
				{
					$isId = false;
				}
			}
			
			$this->_returnParameter($parameter);
			
			return $isId;
		}
		else
		{
			return false;
		}
	
	}

	/**
	 * Automatically parses the url parameters and calls the appropriate objects
	 */
	protected final function _processNext ()
	{
		// check if there is a parameter
		if ($this->_hasParameter())
		{
			$objectName = $this->_getNextParameter();
			
			switch ($this->_type)
			{
				case 'oauth':
					if (file_exists(APP_CLASSES . 'Oauth/' . ucfirst(strtolower($objectName)) . '.class.php'))
					{
						$restObject = 'Oauth_' . ucfirst(strtolower($objectName));
					}
					else
					{
						$this->setStatusCode(404);
						exit();
					}
					break;
				
				case 'mysql':
					$lastCalled = $objectName;
					// check if there is a table with the name $objectName
					$db = new Core_DB();
					
					if (file_exists(APP_CLASSES . 'Rest/' . ucfirst(strtolower($objectName)) . '.class.php'))
					{
						$restObject = 'Rest_' . ucfirst(strtolower($objectName));
					}
					elseif (isset($db->dataset[strtolower($objectName)]) || isset($db->dataset[$objectName]))
					{
						$restObject = "MysqlRestObject";
					}
					elseif (file_exists(APP_CLASSES . 'Rest/' . $objectName . '.class.php'))
					{
						$restObject = 'Rest_' . $objectName;
					}
					else
					{
						$this->setStatusCode(404);
						exit();
					}
					break;
				
				case 'mongodb':
					$lastCalled = $objectName;
					if (Core_Mongo::hasCollection($objectName) && ! ($this->_responseData || is_array($this->_responseData)))
					{
						$restObject = 'MongoRestObject';
					}
					elseif (file_exists(APP_CLASSES . 'MongoRest/' . ucfirst(strtolower($objectName)) . '.class.php'))
					{
						$restObject = 'MongoRest_' . ucfirst(strtolower($objectName));
					}
					elseif ($this->_responseData || is_array($this->_responseData))
					{
						// check if the object is a documentset of the current data
						$structure = Core_Mongo::getCurrentStructure($this->_lastCalled);
						foreach ($this->_objectsCalled as $lastObject)
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
						
						if (isset($structure[$objectName]) && (in_array('DocumentSet', $structure[$objectName]['type']) || in_array('Document', $structure[$objectName]['type'])))
						{
							$this->_objectsCalled[] = $objectName;
							$restObject = 'MongoRestObject';
							$lastCalled = $this->_lastCalled;
						}
						else
						{
							$this->setStatusCode(404);
							exit();
						}
					}
					else
					{
						$this->setStatusCode(404);
						exit();
					}
					break;
				
				default:
					$this->setStatusCode(501);
					exit();
			}
			
			$objectIDs = array();
			$restArgumentObject = $objectName;
			// check if there is an id
			if ($this->_isNextParameterId())
			{
				$nextParameter = $this->_getNextParameter();
				$restArgumentObject = $nextParameter;
				$objectIDs = explode(',', $nextParameter);
			}
			$objectInstance = new $restObject($this, $objectName, $objectIDs, $this->_arguments, $this->_restArguments[$restArgumentObject], $this->_restFilters[$objectName], $this->_lastCalled);
			
			if ($this->_hasParameter())
			{
				$objectInstance->setMethod('GET');
			}
			else
			{
				$objectInstance->setMethod($this->_method);
			}
			
			$objectInstance->setResponseData($this->_responseData);
			
			$objectInstance->setLastCalled($this->_lastCalled);
			
			$objectInstance->process();
			
			$this->_responseData = $objectInstance->getResponseData();
			
			if ($this->_type == 'mongodb' && ! $this->_lastCalled)
			{
				$temp = array();
				foreach ($this->_responseData as $row)
				{
					if (is_array($row) && isset($row['_id']))
					{
						$temp[] = $row['_id'];
					}
				}
				$this->mainIDs = $temp;
			}
			
			$this->_lastCalled = $lastCalled;
			
			if ($this->_hasParameter())
			{
				$this->_processNext();
			}
		}
		else
		{
			return false;
		}
	
	}

	/**
	 * Returns the method used in the REST Request
	 *
	 * @return string
	 */
	public final function getMethod ()
	{

		return $this->_method;
	
	}

}
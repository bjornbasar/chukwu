<?

class Oauth_Requesttoken extends Core_RestObject
{

	protected $_mongodb;

	protected $_auth;

	public function __construct ($restServer, $table, $ids = array(), $arguments = array(), $restArguments = array(), $restFilters = array())
	{

		$this->_restServer = $restServer;
		$this->db = new Core_Sqlite('oauth');
		$this->_auth = new Core_RestAuth();
		$this->_mongodb = new Core_Mongo("users");
		
		parent::__construct($ids, $arguments, $restArguments, $restFilters);
	
	}

	protected function _doGet ()
	{

		$consumer = $this->_auth->getHeaders(array('key', 'secret', 'access', 'callbackurl', 'redirecturl', 'type', 'id'));
		
		if ($this->_auth->verifyConsumerAuth($consumer['key'], $consumer['secret']))
		{
			$query = "select * from restauthconsumers where key = ? and secret = ?";
			$result = $this->db->getRow($query, array($consumer['key'], $consumer['secret']));
			
			if (isset($result))
			{
				
				if ($this->_auth->verifyToken('access', $consumer['access']))
				{
					if ($this->_verifyMongoId($consumer['id']))
					{
						$requestToken = $this->_auth->generateToken('request', $consumer);
						$query = "insert into restauthtokens (restauthconsumers_id, id, token, type, status) values (?, ?, ?, ?, ?)";
						$this->db->execute($query, array($result['restauthconsumers_id'], $consumer['id'], $requestToken, 'request', 0));
						if ("login" == strtolower($consumer['type']))
						{
							$tempToken = array('request' => $requestToken, 'callbackurl' => $consumer['callbackurl'], 'redirecturl' => $consumer['redirecturl']);
							$tempToken = Core_Helper::encrypt(serialize($tempToken));
							
							$this->_restServer->setStatusCode(200);
							$this->setResponseData(RESTAUTH_LOGIN . $tempToken);
						}
						else
						{
							$tempToken = array('request' => $requestToken, 'callbackurl' => $consumer['callbackurl']);
							$tempToken = Core_Helper::encrypt(serialize($tempToken));
							
							$this->_restServer->setStatusCode(201);
						}
					
					}
					else
					{
						$this->_restServer->setStatusCode(400);
					}
				}
				else
				{
					$this->_restServer->setStatusCode(401);
				}
			
			}
		}
		
		else
		{
			$this->_restServer->setStatusCode(401);
		}
	
	}

	protected function _doPost ()
	{

		$data = $this->_arguments;
		$tempToken = $data['token'];
		$tempToken = Core_Helper::decrypt($tempToken);
		
		$tempToken = unserialize($tempToken);
		if (is_array($tempToken))
		{
			if (isset($tempToken['request']))
			{
				$requestToken = $tempToken['request'];
				
				if ($this->_auth->verifyToken('request', $requestToken))
				{
					$data = unserialize(Core_Helper::decrypt($requestToken));
					
					if ($this->_verifyMongoId($data['id']))
					{
						$query = "select * from restauthtokens where token = ? and type = ? and status = ?";
						$result = $this->db->getArray($query, array($requestToken, 'request', 0));
						if (isset($result))
						{
							$query = "update restauthtokens set status = 1 where token = ? and type = ? and status = ?";
							$result = $this->db->execute($query, array($requestToken, 'request', 0));
							
							$requestTokenData = unserialize(Core_Helper::decrypt($requestToken));
							
							// httpclient call here for the callback url
							$httpclient = new Core_RestClient($tempToken['callbackurl']);
							
							$httpclient->call('post', array('id' => $requestTokenData['id'], 'requestToken' => $requestToken));
							
							$this->_restServer->setStatusCode(200);
							$this->setResponseData($tempToken['redirecturl']);
						}
						else
						{
							$this->_restServer->setStatusCode(400);
						}
					}
					else
					{
						$this->_restServer->setStatusCode(400);
					}
				
				}
				else
				{
					$this->_restServer->setStatusCode(400);
				}
			}
			else
			{
				$this->_restServer->setStatusCode(400);
			}
		}
		else
		{
			$this->_restServer->setStatusCode(400);
		}
	
	}

	protected function _verifyMongoId ($id)
	{

		$mongodb = new Core_Mongo('users');
		$data = $mongodb->query(array('_id' => new MongoID($id)));
		
		if (count($data) > 0)
		{
			return true;
		}
		else
		{
			return false;
		}
	
	}

}
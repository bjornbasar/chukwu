<?

class Oauth_List extends Core_RestObject
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

		if (count($this->_ids))
		{
			$id = $this->_ids[0];
			
			$consumer = $this->_auth->getHeaders(array('key', 'secret', 'access'));
			
			if ($this->_auth->verifyConsumerAuth($result['key'], $result['secret']))
			{
				if ($this->_auth->verifyToken('access', $consumer['access']))
				{
					if ($this->_verifyMongoId($id))
					{
						$query = "select * from restauthtokens where id = ?";
						$tokens = $this->db->getArray($query, array($id));
						
						if (count($token))
						{
							$this->_restServer->setStatusCode(200);
							$this->setResponseData($tokens);
						}
						else
						{
							$this->_restServer->setStatusCode(404);
						}
					}
					else
					{
						$this->_restServer->setStatusCode(404);
					}
				}
				else
				{
					$this->_restServer->setStatusCode(403);
				}
			}
		}
		else
		{
			$this->_restServer->setStatusCode(403);
		}
	
	}

	protected function _doPost ()
	{

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
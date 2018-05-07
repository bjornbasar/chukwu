<?

class Oauth_Accesstoken extends Core_RestObject
{

	protected $_auth;

	public function __construct ($restServer, $table, $ids = array(), $arguments = array(), $restArguments = array(), $restFilters = array())
	{

		$this->_restServer = $restServer;
		$this->db = new Core_Sqlite('oauth');
		
		$this->_auth = new Core_RestAuth();
		
		parent::__construct($ids, $arguments, $restArguments, $restFilters);
	
	}

	protected function _doGet ()
	{

		if ($this->_auth->verifyConsumerAuth())
		{
			$accessToken = $this->_auth->generateToken('access', $consumer);
			$this->_restServer->setStatusCode(200);
			$this->setResponseData($accessToken);
		}
		else
		{
			$this->_restServer->setStatusCode(401);
		}
	
	}

}
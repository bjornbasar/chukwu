<?

class Oauth_Token extends Core_RestObject
{

	public function __construct ($restServer, $table, $ids = array(), $arguments = array(), $restArguments = array(), $restFilters = array())
	{

		$this->_restServer = $restServer;
		$this->db = new Core_Sqlite('oauth');
		
		parent::__construct($ids, $arguments, $restArguments, $restFilters);
	
	}

	protected function _doGet ()
	{

		$this->_restServer->setStatusCode(200);
		$this->setResponseData(array('baklasiniel'));
	
	}

}
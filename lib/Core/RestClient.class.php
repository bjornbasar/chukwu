<?

/**
 * Rest Client Class
 *
 * @author Sheldon Senseng
 * @copyright Sheldon Senseng <sheldonsenseng@gmail.com>
 * @version 0.1
 *         
 */
class Core_RestClient
{

	public $_client;

	private $_uri;

	private $_path;

	private $_scheme;

	private $_response;

	/**
	 * Instantiate an HTTP Client
	 *
	 * @param $url unknown_type        	
	 */
	public function __construct ($url)
	{

		$this->_client = new Zend_Http_Client($url, $this->_config);
		$this->_client->setConfig(array('adapter' => 'Zend_Http_Client_Adapter_Curl', 'curloptions' => array(CURLOPT_FOLLOWLOCATION => false), 'maxredirects' => 0));
	
	}

	public function __destruct ()
	{

		unset($this->_client);
		unset($this->_response);
	
	}

	/**
	 * Send an HTTP Request
	 *
	 * @param $requestType string        	
	 * @param $arguments array        	
	 * @param $headers array        	
	 * @throws Exception
	 * @return array
	 */
	public function call ($requestType, $arguments = array(), $headers = array())
	{

		foreach ($headers as $headerKey => $headerValue)
		{
			$this->_client->setHeaders($headerKey, $headerValue);
		}
		
		switch (strtolower($requestType))
		{
			case 'get':
				$this->_client->setParameterGet($arguments);
				$this->_client->setMethod(Zend_Http_Client::GET);
				break;
			
			case 'post':
				$this->_client->setParameterPost($arguments);
				$this->_client->setMethod(Zend_Http_Client::POST);
				break;
			
			case 'put':
				$this->_client->setParameterPost($arguments);
				$this->_client->setMethod(Zend_Http_Client::PUT);
				break;
			
			case 'delete':
				$this->_client->setParameterPost($arguments);
				$this->_client->setMethod(Zend_Http_Client::DELETE);
				break;
			
			default:
				throw new Exception("Request Type: $requestType is not supported");
		}
		
		$this->_client->request();
		
		return array('status' => $this->_client->getLastResponse()->getStatus(), 'body' => $this->_client->getLastResponse()->getBody(), 'request' => $this->_client->getLastRequest());
	
	}

}

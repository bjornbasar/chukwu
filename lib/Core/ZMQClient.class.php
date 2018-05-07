<?

class Core_ZMQClient
{

	protected $_dsn;

	protected $_socket;

	protected $_application;

	public function __construct ($dsn, $application)
	{

		$this->_dsn = $dsn;
		$this->_application = $application;
		
		/* Create a socket */
		$this->_socket = new ZMQSocket(new ZMQContext(), ZMQ::SOCKET_REQ);
		
		/* Get list of connected endpoints */
		$endpoints = $this->_socket->getEndpoints();
		
		/* Check if the socket is connected */
		if (! in_array($this->_dsn, $endpoints['connect']))
		{
			$this->_socket->connect($dsn);
		}
		
		unset($endpoints);
	
	}

	public function __destruct ()
	{

		$this->_socket->disconnect($this->_dsn);
	
	}

	private function _getLocalIP ()
	{

		$command = "/sbin/ifconfig eth0 | grep 'inet addr:' | cut -d: -f2 | awk '{ print $1}'";
		$localIP = exec($command);
		
		return $localIP;
	
	}

	public function send ($message, $type = 'log')
	{

		$data = serialize(array('type' => $type, 'source' => $this->_getLocalIP(), 'application' => $this->_application, 'message' => $message));
		
		$this->_socket->send($data);
		$message = $this->_socket->recv();
		
		return $message;
	
	}

}
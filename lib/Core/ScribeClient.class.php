<?
/**
 * Scribe Client Class
 *
 * @author Sheldon Senseng
 * @copyright Sheldon Senseng <sheldonsenseng@gmail.com>
 * @version 0.1
 *         
 */
require_once SCRIBE_ROOT . '/scribe.php';
require_once THRIFT_ROOT . '/protocol/TBinaryProtocol.php';
require_once THRIFT_ROOT . '/transport/TFramedTransport.php';
require_once THRIFT_ROOT . '/transport/TSocketPool.php';
require_once SCRIBE_ROOT . '/bucketupdater/BucketStoreMapping.php';

class Core_ScribeClient
{

	protected $_client;

	protected $_trans;

	protected $_sock;

	protected $_prot;

	/**
	 * Initialize Scribe server parameters and connections
	 *
	 * @throws Exception
	 */
	public function __construct ()
	{

		try
		{
			// Set up the socket connections
			$scribe_servers = array(SCRIBE_SERVER);
			$scribe_ports = array(SCRIBE_PORT);
			$this->_sock = new TSocketPool($scribe_servers, $scribe_ports);
			$this->_sock->setDebug(0);
			$this->_sock->setSendTimeout(1000);
			$this->_sock->setRecvTimeout(2500);
			$this->_sock->setNumRetries(1);
			$this->_sock->setRandomize(false);
			$this->_sock->setAlwaysTryLast(true);
			$this->_trans = new TFramedTransport($this->_sock);
			$this->_prot = new TBinaryProtocol($this->_trans);
			
			$this->_client = new scribeClient($this->_prot);
			$this->_trans->open();
		}
		catch (Exception $x)
		{
			throw new Exception("Unable to create global scribe client, received exception: $x \n");
		}
	
	}

	/**
	 * Sends a message to the Scribe server
	 *
	 * @param $message string        	
	 * @param $category string        	
	 */
	public function log ($message, $category)
	{

		$msg = new LogEntry();
		$msg->category = $category;
		$msg->message = $message;
		
		$this->_client->Log(array($msg));
	
	}

	public function __destruct ()
	{

	}

}
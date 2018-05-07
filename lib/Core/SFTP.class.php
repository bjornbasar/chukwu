<?

class Core_SFTP
{

	private $_conn;

	private $_sftp;

	private $_path;

	public function __construct ($server, $username, $password)
	{

		$this->_conn = ssh2_connect($server) or die('Cannot connect to SFTP Server: ' . $server);
		
		ssh2_auth_password($this->_conn, $username, $password) or die('Cannot login to SFTP Server');
		
		$this->_sftp = ssh2_sftp($this->_conn);
	
	}

	public function __destruct ()
	{

	}

	public function push ($localfile, $remotefile)
	{

		ssh2_scp_send($this->_conn, $localfile, $remotefile, 0644);
	
	}

	public function pull ($localfile, $remotefile)
	{

		ssh2_scp_recv($this->_conn, $remotefile, $localfile);
	
	}

}
<?

class Core_FTP
{

	private $_conn;

	public function __construct ($server, $path, $username, $password)
	{

		$this->_conn = ftp_connect($server) or die('Cannot connect to FTP Server: ' . $server);
		
		if (! @ftp_login($this->_conn, $username, $password))
		{
			throw new Exception('Cannot login to FTP Server');
			exit();
		}
		
		$filename = basename($path);
		
		$path = trim($path, '/');
		$dirs = explode('/', $path);
		
		foreach ($dirs as $dir)
		{
			if (! in_array($dir, ftp_nlist($this->_conn, '')))
			{
				ftp_mkdir($this->_conn, $dir);
			}
			ftp_chdir($this->_conn, $dir);
		}
	
	}

	public function __destruct ()
	{

		ftp_close($this->_conn);
	
	}

	public function push ($localfile, $remotefile = null)
	{

		if (is_null($remotefile))
		{
			$remotefile = $localfile;
		}
		ftp_put($this->_conn, basename($remotefile), $localfile, FTP_BINARY);
	
	}

	public function pull ($localfile, $remotefile)
	{

		ftp_get($this->_conn, $localfile, basename($remotefile), FTP_BINARY);
	
	}

}
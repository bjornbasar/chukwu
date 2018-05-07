<?

class Core_HTTP
{

	private $_client;

	private $_content;

	private $_url;

	private $_name;

	private $_file;

	public function __construct ($server, $path, $username = null, $password = null)
	{

		$protocol = 'http://';
		$auth = ($username && $password) ? "$username:$password@" : '';
		
		$url = Zend_Uri::factory($protocol . $auth . $server . '/' . $path);
		
		if (! $url->valid())
		{
			throw new Exception("Invalid URL");
			exit();
		}
		
		$this->_url = $url->__toString();
		
		$this->_client = fopen($url->__toString(), 'r');
	
	}

	public function pull ($localfile)
	{

		$this->_content = stream_get_contents($this->_client);
		
		$fh = fopen($localfile, 'w+');
		
		fwrite($fh, $this->_content);
		
		fclose($fh);
	
	}

}
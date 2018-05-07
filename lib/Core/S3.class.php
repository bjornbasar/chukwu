<?

/**
 * Amazon S3 Helper Class
 *
 * @author Sheldon Senseng
 * @copyright Sheldon Senseng <sheldonsenseng@gmail.com>
 * @version 0.1
 *         
 */
class Core_S3
{

	private $accesskey;

	private $secretkey;

	private $connection;

	private $bucket;

	public $stream;

	public $objects;

	/**
	 * Create a connection to the Amazon S3 Server
	 *
	 * @param $accesskey string        	
	 * @param $secretkey string        	
	 * @param $bucket string        	
	 */
	public function __construct ($accesskey, $secretkey, $bucket)
	{

		$this->accesskey = $accesskey;
		$this->secretkey = $secretkey;
		$this->bucket = $bucket;
		
		$this->connection = new Zend_Service_Amazon_S3($this->accesskey, $this->secretkey);
		$this->connection->registerStreamWrapper('s3');
		
		$this->stream = 's3://' . $this->bucket . '/';
		
		$this->getList();
	
	}

	/**
	 * Retrieve list of files available in the S3 bucket
	 */
	public function getList ()
	{

		$this->objects = $this->connection->getObjectsByBucket($this->bucket);
		
		return $this->objects;
	
	}

	/**
	 * Retrieve file object from S3
	 *
	 * @param $file string        	
	 */
	public function getFile ($file)
	{

		$data = $this->connection->getObject($this->bucket . '/' . $file);
		return $data;
	
	}

	/**
	 * Write contents to a file in S3
	 *
	 * @param $filename string        	
	 * @param $contents string        	
	 */
	public function writeFile ($filename, $contents)
	{

		$s3fh = fopen($this->stream . $filename, 'w+');
		fwrite($s3fh, $contents);
		return true;
	
	}

	/**
	 * Copy a file from the local filesystem to S3
	 *
	 * @param $pathToFile string        	
	 * @return boolean
	 */
	public function copyFile ($pathToFile)
	{

		if (file_exists($pathToFile))
		{
			$filename = basename($pathToFile);
			$fh = fopen($pathToFile, 'r');
			$contents = fread($fh, filesize($pathToFile));
			
			$s3fh = fopen($this->stream . $filename, 'w+');
			fwrite($s3fh, $contents);
			return true;
		}
		return false;
	
	}

}
<?

/**
 * Class for file uploads thru web forms
 *
 * @author Sheldon Senseng
 * @copyright Sheldon Senseng <sheldonsenseng@gmail.com>
 * @version 0.1
 *         
 */
class Core_Upload
{

	/**
	 * Property where paths of all uploaded files are placed
	 *
	 * @var array
	 */
	private $_pathToFiles = array();

	/**
	 * Class Constructor
	 */
	public function __construct ()
	{

	}

	/**
	 * Uploads the files from the standard $_FILES variable to the specified
	 * path
	 *
	 * @param $uploadedFile array        	
	 * @param $pathToUpload string        	
	 */
	public function process ($uploadedFile, $pathToUpload = APP_UPLOAD)
	{

		foreach ($uploadedFile as $field => $parameters)
		{
			if ($parameters['error'] === 0)
			{
				if (! move_uploaded_file($parameters['tmp_name'], $pathToUpload . $parameters['name']))
				{
					throw new Exception('Problem uploading file, please check directory permissions');
				}
				else
				{
					$this->_pathToFiles[] = $pathToUpload . $parameters['name'];
				}
			}
		}
	
	}

	/**
	 * Retrieves the path to a specified index of a uploaded file or all paths
	 *
	 * @param $i integer        	
	 * @return array
	 */
	public function getPaths ($i = null)
	{

		if (is_null($i))
		{
			return $this->_pathToFiles;
		}
		return $this->_pathToFiles[$i];
	
	}

	/**
	 * Class Destructor
	 */
	public function __destruct ()
	{

	}

}
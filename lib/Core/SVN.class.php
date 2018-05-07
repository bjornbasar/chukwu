<?

/**
 * Subversion Class for Framework Tasks
 *
 * @author Sheldon Senseng
 * @copyright Sheldon Senseng <sheldonsenseng@gmail.com>
 * @version 0.1
 *         
 */
class Core_SVN
{

	private $_workingCopy;

	public $_files = array();

	private $_forCommit = array();

	private $_statuses = array('', 'NONE', 'UNVERSIONED', 'NORMAL', 'ADDED', 'MISSING', 'DELETED', 'REPLACED', 'MODIFIED', 'MERGED', 'CONFLICTED', 'IGNORED', 'OBSTRUCTED', 'EXTERNAL', 'INCOMPLETE');

	private $_ignoreList = array(APP_TEMPLATES_CACHE, APP_TEMPLATES_COMPILED, APP_LOG, APP_UPLOAD, APP_DATA);

	/**
	 * Initialize the authentication and url parameters of the Subversion
	 * Repository to use
	 *
	 * @param $workingCopy string        	
	 * @param $username string        	
	 * @param $password string        	
	 */
	public function __construct ($workingCopy = null, $username = null, $password = null)
	{

		if (is_null($workingCopy))
		{
			$this->_workingCopy = APP_ROOT;
		}
		else
		{
			$this->_workingCopy = $workingCopy;
		}
		if (! is_null($username))
		{
			svn_auth_set_parameter(SVN_AUTH_PARAM_DEFAULT_USERNAME, $username);
		}
		if (! is_null($password))
		{
			svn_auth_set_parameter(SVN_AUTH_PARAM_DEFAULT_PASSWORD, $password);
		}
	
	}

	/**
	 * Generate list of files to ignore from versioning
	 *
	 * @param $path string        	
	 * @return array
	 */
	private function _isIgnored ($path)
	{

		$isIgnored = false;
		
		if (! is_file($path))
		{
			$path = $path . '/';
		}
		
		foreach ($this->_ignoreList as $ignored)
		{
			if (strpos($path, $ignored) !== false)
			{
				$isIgnored = true;
			}
		}
		
		return $isIgnored;
	
	}

	/**
	 * Check the status of all files that need to be or are versioned
	 */
	public function updateStatus ()
	{

		$this->_files = array();
		$this->_forCommit = array();
		
		$files = svn_status($this->_workingCopy);
		
		// categorize files
		foreach ($files as $file)
		{
			// check against ignore list
			if (! $this->_isIgnored($file['path']))
			{
				$data = array();
				$data['path'] = $file['path'];
				$data['status'] = $file['text_status'];
				$data['status_text'] = $this->_statuses[$file['text_status']];
				
				$this->_files[] = $data;
				$this->_forCommit[] = $file['path'];
			}
		}
	
	}

	/**
	 * Add all unversioned and unignored files
	 */
	public function addUnversioned ()
	{
		// refresh status;
		$this->updateStatus();
		
		foreach ($this->_files as $file)
		{
			if ($file['status_text'] == 'UNVERSIONED')
			{
				svn_add($file['path']);
			}
		}
	
	}

	/**
	 * Commit all changes to the Subversion repository
	 *
	 * @param $message string        	
	 * @return array
	 */
	public function commit ($message)
	{

		$result = svn_commit($message, $this->_forCommit);
		
		return array('revision' => $result[0], 'name' => $result[2]);
	
	}

	/**
	 * Return all versioned files
	 *
	 * @return array
	 */
	public function getFiles ()
	{

		return $this->_files;
	
	}

	/**
	 * Retrieve Subversion repository logs
	 *
	 * @return array
	 */
	public function getLogs ()
	{

		$logs = svn_log($this->_workingCopy);
		
		foreach ($logs as $key => $value)
		{
			$logs[$key]['date'] = date('Y-m-d H:i:s', strtotime($value['date']));
		}
		return $logs;
	
	}

}
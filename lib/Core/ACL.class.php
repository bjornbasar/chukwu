<?

/**
 * Simple ACL class with predefined actions and heirarchy
 *
 * @author Sheldon Senseng
 * @copyright Sheldon Senseng <sheldonsenseng@gmail.com>
 * @version 0.1
 *         
 */
class Core_ACL
{

	protected $_permissions;

	protected $_user;

	protected $_db;

	/**
	 * Predefined constants for Action Types and Heirarchy
	 */
	const ACL_ADMIN = 4;

	const ACL_CREATE = 3;

	const ACL_EDIT = 2;

	const ACL_VIEW = 1;

	/**
	 * Class Constructor
	 */
	public function __construct ()
	{

		$this->_db = new Core_DB();
	
	}

	/**
	 * Updates the Users table
	 *
	 * @param $data array        	
	 */
	public function updateUser ($data)
	{

		if (isset($data['username']))
		{
			$this->_db->autoexecute('auth_users', $data, array('username' => $data['username']));
		}
		else
		{
			$this->_db->autoexecute('auth_users', $data);
		}
	
	}

	/**
	 * Updates the Modules table
	 *
	 * @param $data array        	
	 */
	public function updateModule ($data)
	{

		if (isset($data['auth_moduleid']))
		{
			$this->_db->autoexecute('auth_modules', $data, array('auth_moduleid' => $data['auth_moduleid']));
		}
		else
		{
			$this->_db->autoexecute('auth_modules', $data);
		}
	
	}

	/**
	 * Updates the Roles table
	 *
	 * @param $data array        	
	 */
	public function updateRole ($data)
	{

		if (isset($data['auth_roleid']))
		{
			$this->_db->autoexecute('auth_roles', $data, array('auth_roleid' => $data['auth_roleid']));
		}
		else
		{
			$this->_db->autoexecute('auth_roles', $data);
		}
	
	}

	/**
	 * Updates the Permissions table
	 *
	 * @param $data array        	
	 */
	public function updatePermission ($data)
	{

		if (isset($data['auth_permissionid']))
		{
			$this->_db->autoexecute('auth_permissions', $data, array('auth_permissionid' => $data['auth_permissionid']));
		}
		else
		{
			$this->_db->autoexecute('auth_permissions', $data);
		}
	
	}

	/**
	 * Authenticates using the given username and password and processes the
	 * user's permissions
	 *
	 * @param $username string        	
	 * @param $password string        	
	 * @return boolean
	 */
	public function authenticate ($username, $password)
	{

		$query = "select * from `auth_users` where `username` = ? and `password` = ? and `status` = ?";
		$result = $this->_db->getRow($query, array($username, $password, 1));
		
		if (count($result) < 1)
		{
			$this->signout();
			return false;
		}
		
		// add user info to session
		unset($result['password']);
		unset($result['status']);
		
		Core_Helper::updateSession('user', $result);
		$this->_user = $result;
		
		// get permissions
		$this->getPermissions($result['auth_roleid']);
		return true;
	
	}

	/**
	 * Clears the User Session Data
	 */
	public function signout ()
	{

		Core_Helper::clearSession('user');
		Core_Helper::clearSession('permissions');
	
	}

	/**
	 * Retrieves the permissions allowed for the given Role
	 *
	 * @param $roleid integer        	
	 */
	public function getPermissions ($roleid)
	{

		$query = "select * from `auth_permissions` where `auth_roleid` = ? and `status` = ?";
		$result = $this->_db->getArray($query, array($roleid, 1));
		
		$query = "select * from `auth_modules` where `status` = 1";
		$modules = $this->_db->getArray($query);
		
		$permissions = array();
		
		foreach ($modules as $module)
		{
			$permissions[$module['auth_moduleid']] = 0;
		}
		
		foreach ($result as $key => $value)
		{
			$moduleid = $value['auth_moduleid'];
			$actionid = $value['auth_actionid'];
			
			$permissions[$moduleid] = $actionid;
		}
		
		$this->_permissions = $permissions;
		Core_Helper::updateSession('permissions', $this->_permissions);
	
	}

	/**
	 * Checks if the signed in user is allowed to access the given module and
	 * access level
	 *
	 * @param $moduleid integer        	
	 * @param $accessLevel integer        	
	 * @return boolean
	 */
	public function isAllowed ($moduleid, $accessLevel = 1)
	{

		$permissions = Core_Helper::getSession('permissions');
		if (isset($permissions[$moduleid]) && $permissions[$moduleid] >= $accessLevel)
		{
			return true;
		}
		return false;
	
	}

	/**
	 * Retrieves the Access Level for a module
	 *
	 * @param $moduleid integer        	
	 * @return mixed
	 */
	public function getPermission ($moduleid)
	{

		$permissions = Core_Helper::getSession('permissions');
		
		if (isset($permissions[$moduleid]))
		{
			return $permissions[$moduleid];
		}
		return false;
	
	}

	/**
	 * Checks if the user is signed in
	 *
	 * @return boolean
	 */
	public function isSignedIn ()
	{

		if (! is_null(Core_Helper::getSession('user')))
		{
			return true;
		}
		return false;
	
	}

	/**
	 * Logs messages for accounting purposes
	 *
	 * @param $moduleid integer        	
	 * @param $details string        	
	 */
	public function log ($moduleid, $details)
	{

		if (! isset($this->_user['username']))
		{
			throw new Exception('No user logged in.');
		}
		else
		{
			$this->db->autoexecute('auth_logs', array('username' => $this->_user['username'], 'auth_moduleid' => $moduleid, 'details' => Core_Helper::sanitize($details), 'datetime' => date('Y-m-d H:i:s')));
		}
	
	}

	/**
	 * Class Destructor
	 */
	public function __destruct ()
	{

		unset($this->_db);
	
	}

}
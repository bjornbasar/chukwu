<?

/**
 * Class for Multi-tiered Access Levels and Authentication
 *
 * @author Sheldon Senseng
 * @copyright Sheldon Senseng <sheldonsenseng@gmail.com>
 * @version 0.1
 *         
 * @uses data/sql/framework_bootstrap.sql
 *      
 */
class Core_Authentication
{

	private $db;

	public $role;

	public $roleid;

	public $email;

	private $acl;

	public $username;

	public $isloggedin = false;

	/**
	 * Retrieve authentication stored data and initialize information
	 */
	public function __construct ()
	{

		$this->db = new Core_DB();
		
		if (! file_exists(APP_DATA_AUTH . 'ACL'))
		{
			$this->dataRefresh();
		}
		else
		{
			$this->dataGet();
		}
		
		if (isset($_SESSION[APP_NAME]['auth_user']))
		{
			$this->username = $_SESSION[APP_NAME]['auth_user']['username'];
			$this->role = $_SESSION[APP_NAME]['auth_user']['role'];
			$this->isloggedin = true;
		}
		elseif (isset($_COOKIE[APP_NAME . '_auth_cookie']))
		{
			$_SESSION[APP_NAME]['auth_user'] = unserialize($_COOKIE[APP_NAME . '_auth_cookie']);
			
			$this->username = $_SESSION[APP_NAME]['auth_user']['username'];
			$this->role = $_SESSION[APP_NAME]['auth_user']['role'];
			$this->isloggedin = true;
		}
	
	}

	/**
	 * Authenticate user
	 *
	 * @param $username string        	
	 * @param $password string        	
	 * @param $remember boolean        	
	 */
	public function process ($username, $password, $remember = false)
	{
		// retrieve user data
		$query = 'select u.*, a.auth_roleid, a.name as `role` from auth_users as u join `auth_roles` as a on u.auth_roleid = a.auth_roleid where u.username = ? and u.password = ? and u.status = 1';
		$result = $this->db->getRow($query, array($username, $password));
		
		if (isset($result['role']))
		{
			// authentication success
			$this->role = $result['role'];
			$this->username = $result['username'];
			$this->roleid = $result['auth_roleid'];
			$this->email = $result['email'];
			
			$_SESSION[APP_NAME]['auth_user']['username'] = $this->username;
			$_SESSION[APP_NAME]['auth_user']['role'] = $this->role;
			$_SESSION[APP_NAME]['auth_user']['roleid'] = $this->roleid;
			$_SESSION[APP_NAME]['auth_user']['email'] = $this->email;
			$this->isloggedin = true;
			
			return true;
		}
		
		// authentication failed
		return false;
	
	}

	/**
	 * Set authentication cookie
	 */
	private function remember ()
	{

		setcookie(APP_NAME . '_auth_cookie', serialize(array('username' => $this->username, 'role' => $this->role)), time() + 86400, null, APP_URI);
	
	}

	/**
	 * Prepare authentication stored data
	 */
	private function dataPrepare ()
	{

		$this->acl = new Zend_Acl();
		
		// get all roles
		$query = 'select t1.name, t2.name as `parent` from auth_roles as t1 left join auth_roles as t2 on t1.inherits = t2.auth_roleid where t1.status = 1 order by t1.auth_roleid';
		$roles = $this->db->getArray($query, array());
		
		foreach ($roles as $role)
		{
			if ($role['parent'])
			{
				$this->acl->addRole(new Zend_Acl_Role($role['name']), $role['parent']);
			}
			else
			{
				$this->acl->addRole(new Zend_Acl_Role($role['name']));
			}
		}
		
		// get all modules
		$query = 'select t1.name, t2.name as `parent` from auth_modules as t1 left join auth_modules as t2 on t1.inherits = t2.auth_moduleid where t1.status = 1 order by t1.auth_moduleid';
		$modules = $this->db->getArray($query, array());
		
		foreach ($modules as $module)
		{
			if ($module['parent'])
			{
				$this->acl->add(new Zend_Acl_Resource($module['name']), $module['parent']);
			}
			else
			{
				$this->acl->add(new Zend_Acl_Resource($module['name']));
			}
		}
		
		// get all actions
		$query = 'select t1.name, t2.name as `parent` from auth_actions as t1 left join auth_actions as t2 on t1.inherits = t2.auth_actionid where t1.status = 1 order by t1.auth_actionid';
		$actions = $this->db->getArray($query, array());
		
		foreach ($actions as $action)
		{
			if ($action['parent'])
			{
				$this->acl->add(new Zend_Acl_Resource($action['name']), $action['parent']);
			}
			else
			{
				$this->acl->add(new Zend_Acl_Resource($action['name']));
			}
		}
		
		/*
		 * // get all actions $query = 'select * from auth_actions where status
		 * = 1 order by auth_actionid'; $actions = $this->db->getArray($query,
		 * array()); // deny everything to everyone foreach ($roles as $role) {
		 * foreach ($modules as $module) { foreach ($actions as $action) {
		 * $this->acl->deny($role['name'], $module['name'], $action['name']); }
		 * } }
		 */
		
		// get all permissions
		$query = 'select r.name as `role`, m.name as `module`, a.name as `action`, p.status from auth_permissions as p join auth_roles as r on p.auth_roleid = r.auth_roleid join auth_modules as m on p.auth_moduleid = m.auth_moduleid join auth_actions as a on p.auth_actionid = a.auth_actionid where p.status = 1';
		$permissions = $this->db->getArray($query, array());
		
		// allow specific role->module->action permissions
		foreach ($permissions as $permission)
		{
			$this->acl->allow($permission['role'], $permission['module'], $permission['action']);
		}
	
	}

	/**
	 * Check if user is allowed to access module
	 *
	 * @param $module string        	
	 * @param $action string        	
	 */
	public function isAllowed ($module, $action)
	{

		return $this->acl->isAllowed($this->role, $module, $action);
	
	}

	/**
	 * Replace stored data with current authentication information
	 */
	public function dataRefresh ()
	{
		// prepare acl and write to data file
		$this->dataPrepare();
		
		$fh = fopen(APP_DATA_AUTH . 'ACL', 'w+');
		
		fwrite($fh, serialize($this->acl));
		
		fclose($fh);
	
	}

	/**
	 * Retrieve the authentication stored data
	 */
	private function dataGet ()
	{
		// get acl from data file
		$fh = fopen(APP_DATA_AUTH . 'ACL', 'r');
		$this->acl = unserialize(fread($fh, filesize(APP_DATA_AUTH . 'ACL')));
		fclose($fh);
	
	}

	/**
	 * Class destructor
	 */
	public function __destruct ()
	{

		unset($this->db);
		unset($this->roleid);
	
	}

}
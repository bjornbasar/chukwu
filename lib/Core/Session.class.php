<?

/**
 * Class for Database based Session Handling
 *
 * @author Sheldon Senseng
 * @copyright Sheldon Senseng <sheldonsenseng@gmail.com>
 * @version 0.1
 *         
 * @uses CREATE TABLE `sessions` (
 *       `session_id` varchar(255) collate utf8_bin NOT NULL,
 *       `session_data` text collate utf8_bin NOT NULL,
 *       `expires` int(11) NOT NULL,
 *       PRIMARY KEY (`session_id`),
 *       KEY `expires` (`expires`)
 *       ) ENGINE=MyISAM
 *      
 */
class Core_Session
{

	/**
	 * Session variable lifetime
	 *
	 * @var int
	 */
	private $life_time;

	/**
	 * Database Connection placeholder
	 *
	 * @var Core_DB
	 */
	private $db;

	/**
	 * Class Constructor that sets the session handlers and session timeout
	 */
	function __construct ()
	{
		// connect to db
		$this->db = new Core_DB(DB_SESSION);
		
		// Read the maxlifetime setting from PHP
		$this->life_time = get_cfg_var('session.gc_maxlifetime');
		
		// Register this object as the session handler
		session_set_save_handler(array(&$this, 'open'), array(&$this, 'close'), array(&$this, 'read'), array(&$this, 'write'), array(&$this, 'destroy'), array(&$this, 'gc'));
	
	}

	/**
	 * Retrieves the session path
	 *
	 * @param $save_path string        	
	 * @param $session_name string        	
	 * @return boolean
	 */
	function open ($save_path, $session_name)
	{

		global $sess_save_path;
		
		$sess_save_path = $save_path;
		
		// Don't need to do anything. Just return TRUE.
		
		return true;
	
	}

	/**
	 * Closes the session
	 *
	 * @return boolean
	 */
	function close ()
	{
		// Don't need to do anything. Just return TRUE.
		return true;
	
	}

	/**
	 * Retrieves the session variable based on the specified ID
	 *
	 * @param $id string        	
	 * @return mixed
	 */
	function read ($id)
	{
		// Set empty result
		$data = '';
		
		// Fetch session data from the selected database
		
		$time = time();
		
		$newid = mysql_real_escape_string($id);
		$sql = "SELECT `session_data` FROM `sessions` WHERE `session_id` = '$newid' AND `expires` > $time";
		
		return $this->db->getRow($sql);
	
	}

	/**
	 * Sets a session variable
	 *
	 * @param $id string        	
	 * @param $data mixed        	
	 * @return boolean
	 */
	function write ($id, $data)
	{
		// Build query
		$time = time() + $this->life_time;
		
		$newid = mysql_real_escape_string($id);
		$newdata = mysql_real_escape_string($data);
		
		$sql = "REPLACE `sessions`(`session_id`,`session_data`,`expires`) VALUES ('$newid', '$newdata', $time)";
		
		$this->db->execute($sql);
		
		return TRUE;
	
	}

	/**
	 * Removes a session variable
	 *
	 * @param $id string        	
	 * @return boolean
	 */
	function destroy ($id)
	{
		// Build query
		$newid = mysql_real_escape_string($id);
		$sql = "DELETE FROM `sessions` WHERE `session_id` = '$newid'";
		
		$this->db->execute($sql);
		
		return TRUE;
	
	}

	/**
	 * Garbage collection to remove expired sessions
	 *
	 * @return boolean
	 */
	function gc ()
	{
		// Garbage Collection
		
		// Build DELETE query. Delete all records who have passed the expiration
		// time
		$sql = 'DELETE FROM `sessions` WHERE `expires` < UNIX_TIMESTAMP();';
		
		$this->db->execute($sql);
		
		// Always return TRUE
		return true;
	
	}

}
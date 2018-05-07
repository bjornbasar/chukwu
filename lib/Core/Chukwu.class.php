<?
require_once APP_ROOT . "lib/Smarty/libs/Smarty.class.php";

/**
 * Main Framework class for module handling
 *
 * @author Sheldon Senseng
 * @copyright Sheldon Senseng <sheldonsenseng@gmail.com>
 * @version 0.1
 *         
 */
class Core_Chukwu
{

	private $_parameters;

	private $_template;

	private $_displayType = 'html';

	private $_jsonData = '';

	private $_xmlData = '';

	private $_restData = '';

	public $hasbeendisplayed = false;

	/**
	 * Initialize the module handlers for display and processing
	 *
	 * @param $parameters array        	
	 */
	public function __construct ($parameters)
	{

		$this->_parameters = $parameters;
		if ($this->_parameters['allowdisplay'])
		{
			$this->_template = new Smarty();
			Smarty::muteExpectedErrors();
			$this->_template->caching = APP_CACHING; // set to true to enable
			                                         // caching
			$this->_template->debugging = APP_DEBUG;
			$this->_template->template_dir = APP_TEMPLATES;
			$this->_template->compile_dir = APP_TEMPLATES_COMPILED;
			$this->_template->cache_dir = APP_TEMPLATES_CACHE;
			
			if (isset($this->_parameters['title']))
			{
				$this->assign('TEMPLATE_TITLE', APP_NAME . ' - ' . $this->_parameters['title']);
			}
			else
			{
				$this->assign('TEMPLATE_TITLE', APP_NAME);
			}
			
			$this->_setDisplayType($parameters['displaytype']);
		}
	
	}

	/**
	 * Set the module display type
	 *
	 * @param $displaytype string        	
	 */
	private function _setDisplayType ($displaytype)
	{

		$this->_displayType = $displaytype;
	
	}

	/**
	 * Renders the view of the module
	 *
	 * @throws Exception
	 */
	public function display ()
	{
		
		// preprocessing before display
		require_once APP_ROOT . 'preprocess.php';
		
		if ($this->_parameters['allowdisplay'])
		{
			switch ($this->_displayType)
			{
				case 'json':
					$json = Zend_Json::encode($this->_jsondata);
					if (isset($_GET['callback']))
					{
						echo $_GET['callback'] . "($json)";
					}
					else
					{
						echo $json;
					}
					exit();
					break;
				
				case 'xml':
					
					break;
				
				case 'rest':
					echo $this->_restData;
					exit();
					break;
				
				default:
					require APP_LIB . 'lessphp/lessc.inc.php';
					
					$this->compileLess();
					
					if (isset($this->_parameters['body']))
					{
						$this->_template->assign('body', APP_TEMPLATES . $this->_parameters['body']);
					}
					
					$this->_template->display($this->_parameters['template']);
					$this->hasbeendisplayed = true;
					break;
			}
		}
		else
		{
			// throw exception for "this is an action module"
			throw new Exception('Action modules cannot be displayed');
		}
	
	}

	/**
	 * Assigns data to the template
	 *
	 * @param $key string        	
	 * @param $value mixed        	
	 */
	public function assign ($key, $value)
	{

		$this->_template->assign($key, $value);
	
	}

	/**
	 * Load the module files
	 */
	public function load ()
	{

		global $PARAMS;
		require APP_MODULES . $this->_parameters['path'];
	
	}

	/**
	 * Fire module triggers
	 *
	 * @param $issuccess boolean        	
	 */
	public function trigger ($issuccess = null)
	{

		if ($issuccess || is_null($issuccess))
		{
			$this->forward($this->_parameters['success']);
		}
		else
		{
			$this->forward($this->_parameters['fail']);
		}
	
	}

	/**
	 * Change location
	 *
	 * @param $url string        	
	 */
	public function forward ($url)
	{

		header('Location: ' . $url);
	
	}

	/**
	 * Set system message
	 *
	 * @param $message string        	
	 */
	public function setmessage ($message)
	{

		$_SESSION[APP_NAME]['message'] = $message;
	
	}

	/**
	 * Check if there is a system message
	 */
	public function hasmessage ()
	{

		if (isset($_SESSION[APP_NAME]['message']))
		{
			return true;
		}
		return false;
	
	}

	/**
	 * Retrieve system message
	 */
	public function getmessage ()
	{

		if (isset($_SESSION[APP_NAME]['message']))
		{
			$temp = $_SESSION[APP_NAME]['message'];
			unset($_SESSION[APP_NAME]['message']);
			return $temp;
		}
		return false;
	
	}

	public function compileLess ()
	{

		$files = Core_Helper::listFiles(APP_CSS, false);
		
		foreach ($files as $file)
		{
			if (Core_Helper::getFileExtension($file) == 'less')
			{
				lessc::ccompile($file, str_ireplace('.less', '.css', $file), true);
			}
		}
	
	}

	/**
	 * Class destructor
	 */
	public function __destruct ()
	{

		unset($this->_parameters);
	
	}

}
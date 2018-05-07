<?
define('APP_ROUTER', true);
require 'verify.php';
require 'settings.php';

// set error and exception handlers
set_error_handler('Core_Handler::errorHandler');
set_exception_handler('Core_Handler::exceptionHandler');

// Get the module name and parameters - start
$PARAMS = explode('/', trim($_SERVER['SCRIPT_NAME'], '/'));

define('MODULE_FULLNAME', $PARAMS[0]);

$MODULE = array_shift($PARAMS);
// Get the module name and parameters - end

// parse the modules.ini file
$MODULES = parse_ini_file(APP_CONF . 'modules.ini', true);

// check if the selected module is defined
if (isset($MODULES[$MODULE]))
{
	$currentModuleConfig = $MODULES[$MODULE];
	$isDefault = false;
}
else
{
	if (stripos($MODULE, '.do') !== false)
	{
		// check default paths for module based on module name
		$moduleBasePath = 'actions/' . str_replace('_', '/', str_replace('.do', '', $MODULE));

		// create default parameters
		$currentModuleConfig = array();
		$currentModuleConfig['path'] = $moduleBasePath . '.action.php';
	}
	elseif (stripos($MODULE, '.json') !== false)
	{
		// check default paths for module based on module name
		$moduleBasePath = 'json/' . str_replace('_', '/', $MODULE);

		// create default parameters
		$currentModuleConfig = array();
		$currentModuleConfig['path'] = $moduleBasePath . '.php';
	}
	elseif (stripos($MODULE, '.xml') !== false)
	{
		// check default paths for module based on module name
		$moduleBasePath = 'xml/' . str_replace('_', '/', $MODULE);

		// create default parameters
		$currentModuleConfig = array();
		$currentModuleConfig['path'] = $moduleBasePath . '.php';
	}
	elseif (stripos($MODULE, '.rest') !== false)
	{
		// check default paths for module based on module name
		$moduleBasePath = 'rest/' . str_replace('_', '/', $MODULE);

		// create default parameters
		$currentModuleConfig = array();
		$currentModuleConfig['path'] = $moduleBasePath . '.php';
	}
	elseif (stripos($MODULE, '.pkg') !== false)
	{
		// check default paths for module based on module name
		$moduleBasePath = 'packages/' . str_replace('_', '/', str_replace('.pkg', '', $MODULE));

		// create default parameters
		$currentModuleConfig = array();
		$currentModuleConfig['path'] = $moduleBasePath . '.php';
	}
	else
	{
		// check default paths for module based on module name
		$moduleBasePath = 'main/' . str_replace('_', '/', $MODULE);

		// create default parameters
		$currentModuleConfig = array();
		$currentModuleConfig['path'] = $moduleBasePath . '.php';
		if (stripos($MODULE, 'partial') !== false)
		{
			$moduleBasePath = '' . str_replace('_', '/', $MODULE);
			$currentModuleConfig['path'] = $moduleBasePath . '.php';
			$currentModuleConfig['template'] = $moduleBasePath . '.tpl';
		}
		else
		{
			$currentModuleConfig['body'] = $moduleBasePath . '.tpl';
			$currentModuleConfig['template'] = APP_TPL_DEFAULT;
		}
	}

	$isDefault = true;
}

// start session
if (APP_SESSION)
{
	$SESSION = new Core_Session();
}
session_start();

// check if the current module is an action or a display page
if (stripos($MODULE, '.do') !== false)
{
	$currentModuleConfig['allowdisplay'] = false;
}
elseif (stripos($MODULE, '.json') !== false)
{
	$currentModuleConfig['allowdisplay'] = true;
	$currentModuleConfig['displaytype'] = 'json';
}
elseif (stripos($MODULE, '.xml') !== false)
{
	$currentModuleConfig['allowdisplay'] = true;
	$currentModuleConfig['displaytype'] = 'xml';
}
elseif (stripos($MODULE, '.rest') !== false)
{
	$currentModuleConfig['allowdisplay'] = true;
	$currentModuleConfig['displaytype'] = 'rest';
}
elseif (stripos($MODULE, '.pkg') !== false)
{
	$currentModuleConfig['allowdisplay'] = false;
	$currentModuleConfig['displaytype'] = 'pkg';
}
else
{
	$currentModuleConfig['allowdisplay'] = true;
	$currentModuleConfig['displaytype'] = 'html';
}

if (file_exists(APP_MODULES . $currentModuleConfig['path']) && (! isset($currentModuleConfig['template']) || file_exists(APP_TEMPLATES . $currentModuleConfig['template'])) && (! isset($currentModuleConfig['body']) || file_exists(APP_TEMPLATES . @$currentModuleConfig['body'])))
{
	if ($currentModuleConfig['displaytype'] == 'pkg')
	{
		$package = explode('_', $MODULE);
		$packages_id = $package[0];
		$type = str_replace('.pkg', '', $package[1]);

		if ($type == 'process')
		{
			die("Module not found");
		}
		else
		{
			$PACKAGES = new Packages($packages_id, $type, $currentModuleConfig['path']);
		}
	}
	else
	{
		// create instance of module viewer
		$CHUKWU = new Core_Chukwu($currentModuleConfig);
		$CHUKWU->load();

		if ($currentModuleConfig['allowdisplay'] && ! $CHUKWU->hasbeendisplayed)
		{
			$CHUKWU->display();
		}
	}
}
else
{
	// check if module is a unit test
	if (stripos($MODULE, '.test') !== false)
	{
		// check default paths for module based on module name
		$unitTestPath = APP_TESTS . str_replace('_', '/', str_replace('.test', '', $MODULE)) . '.test.php';
		if (file_exists($unitTestPath))
		{
			// load unit test
			define('TESTNAME', 'UNIT TEST: ' . strtoupper(str_replace('.test', '', $MODULE)));
			require APP_LIB . 'simpletest/autorun.php';

			$unitTestClass = APP_TESTS_PREFIX . str_replace('.test', '', $MODULE);
			require $unitTestPath;
			$unitTest = new $unitTestClass();
		}
		else
		{
			throw new Exception('Unit Test not found');
		}
	}
	elseif ($isDefault)
	{
		// display the 'module not found' page
		// throw new Exception("Module not found: $currentModuleConfig[path] and
		// $currentModuleConfig[template]");
		die("Module not found");
	}
	else
	{
		// throw exception for 'file not found'
		throw new Exception('Defined files in modules.ini is not found');
	}
}

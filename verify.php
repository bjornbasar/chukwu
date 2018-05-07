<?
/**
 * Verifies that all files and folders needed for the framework to run are present
 *
 * @author Sheldon Senseng
 * @copyright Sheldon Senseng <sheldonsenseng@gmail.com>
 *
 */

$currentPath = dirname(__FILE__) . '/';

function filePermissions ($file)
{

	return substr(decoct(fileperms($file)), - 3) . '';

}

// config check
$errors = '';

if (! file_exists($currentPath . 'settings.php'))
{
	$errors .= "Configuration file settings.php cannot be found in the install directory: <strong>" . $currentPath . "settings.php</strong><br/><br/>\n";
}

// directories check
if (! file_exists($currentPath . 'application/views/.smarty/.compiled'))
{
	$errors .= "Smarty compiled directory does not exist: <strong>" . $currentPath . "application/views/.smarty/.compiled/</strong><br/><br/>\n";
}
elseif (filePermissions($currentPath . 'application/views/.smarty/.compiled') != '777')
{
	$errors .= "Smarty compiled directory is not writeable: <strong>" . $currentPath . "application/views/.smarty/.compiled/</strong><br/><br/>\n";
}

if (! file_exists($currentPath . 'application/views/.smarty/.cache'))
{
	$errors .= "Smarty cache directory does not exist: <strong>" . $currentPath . "application/views/.smarty/.cache/</strong><br/><br/>\n";
}
elseif (filePermissions($currentPath . 'application/views/.smarty/.cache') != '777')
{
	$errors .= "Smarty cache directory is not writeable: <strong>" . $currentPath . "application/views/.smarty/.cache/</strong><br/><br/>\n";
}

if (! file_exists($currentPath . 'log'))
{
	$errors .= "Log directory does not exist (777): <strong>" . $currentPath . "log/</strong><br/><br/>\n";
}
elseif (filePermissions($currentPath . 'log') != '777')
{
	$errors .= "Log directory is not writeable (777): <strong>" . $currentPath . "log/</strong><br/><br/>\n";
}

if (! APP_ROUTER)
{

	// Apache checks
	if (! in_array('mod_rewrite', apache_get_modules()))
	{
		$errors .= "Please install/activate <strong>mod_rewrite</strong> in your Apache installation.<br/><br/>\n";
	}

}

if ($errors != '')
{
	echo $errors;

	echo "<h3>Please run <i>install.sh</i> from your install folder.</h3>";
	exit();
}
// cleanup
unset($errors);
unset($currentPath);

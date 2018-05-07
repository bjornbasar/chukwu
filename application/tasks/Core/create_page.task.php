<?
extract($PARAMS, EXTR_REFS);

if (isset($name) && $name)
{
	// parse name to convert into path conventions
	$path = str_replace('_', '/', $name);
	
	$files = array();
	// controller
	$this->message("Creating controller for: $name ...\n");
	$files['controller'] = APP_APPLICATION . 'controllers/main/' . $path . '.php';
	
	// view
	$this->message("Creating view for: $name ...\n");
	$files['view'] = APP_APPLICATION . 'views/main/' . $path . '.tpl';
	
	// action
	$this->message("Creating action for: $name ...\n");
	$files['action'] = APP_APPLICATION . 'controllers/actions/' . $path . '.action.php';
	
	foreach ($files as $type => $file)
	{
		exec("mkdir -p " . dirname($file));
		exec("touch $file");
	}

}

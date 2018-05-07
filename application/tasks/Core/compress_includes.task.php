<?
define('APP_CSS_COMPRESSED', APP_ROOT . 'includes/css_compressed/');
define('APP_JS_COMPRESSED', APP_ROOT . 'includes/js_compressed/');

// get list of all files in CSS and JS Folders

$cssFiles = Core_Helper::listFiles(APP_CSS);
$jsFiles = Core_Helper::listFiles(APP_JS);

// make sure the directories exist

mkdir(APP_CSS_COMPRESSED);
mkdir(APP_JS_COMPRESSED);

$this->message("Compressing CSS files...\n");
foreach ($cssFiles as $file)
{
	$filename = basename($file);
	
	$newFile = APP_CSS_COMPRESSED . $filename;
	
	$this->message("$filename ...\n");
	
	exec("java -jar " . APP_ROOT . "yuicompressor.jar -o $newFile $file");
}

$this->message("\nCompressing CSS files...\n");
foreach ($jsFiles as $file)
{
	$filename = basename($file);
	
	$newFile = APP_JS_COMPRESSED . $filename;
	
	$this->message("$filename ...\n");
	
	exec("java -jar " . APP_ROOT . "yuicompressor.jar -o $newFile $file");
}

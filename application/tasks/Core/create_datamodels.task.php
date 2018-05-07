<?
define('APP_DATAMODELS', APP_CLASSES . 'DB/');

$db = new Core_DB();

$db->dataPrepare(DB_MAIN);

$tables = array();
foreach ($db->dataset as $table => $data)
{
	$tables[] = array('table' => $table, 'primary' => $data['primary']);
}

// Passing configuration to the constructor:
if (! file_exists(APP_DATAMODELS))
{
	$this->message("Creating folder to contain datamodels...\n\n");
	mkdir(APP_DATAMODELS);
}

foreach ($tables as $data)
{
	$this->message("Generating Datamodel for $data[table]\n");
	$class = new Zend_CodeGenerator_Php_Class(array('name' => 'DB_' . ucwords($data['table']), 'methods' => array(new Zend_CodeGenerator_Php_Method(array('name' => '__construct', 'body' => '$this->table = "' . $data['table'] . '";' . "\n" . '$this->primary = "' . $data['primary'] . '";' . "\n" . 'parent::__construct();')))));
	
	$class->setExtendedClass('Base');
	
	$file = new Zend_CodeGenerator_Php_File(array('classes' => array($class)));
	
	file_put_contents(APP_DATAMODELS . ucwords($data['table']) . '.class.php', $file->generate());
}
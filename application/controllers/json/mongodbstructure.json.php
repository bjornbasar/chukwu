<?
$pretty = false;
if (isset($_GET['pretty']))
{
	$pretty = true;
}

$collectionToView = @$PARAMS[0];

$database = 'CloudyThing';

$data = array();

// role
$collection = array();
$collection['collection'] = 'role';
$collection['properties'] = array();

$name = '_id';
$collection['properties'][$name] = array();
$collection['properties'][$name]['type'] = array('Required', 'Validator:MongoId');

$name = 'name';
$collection['properties'][$name] = array();
$collection['properties'][$name]['type'] = array('Required', 'Validator:StringLength' => array('max' => 32));

$name = 'display_name';
$collection['properties'][$name] = array();
$collection['properties'][$name]['type'] = array('Required', 'Validator:StringLength' => array('max' => 32));

$name = 'create';
$collection['properties'][$name] = array();
$collection['properties'][$name]['type'] = array('Document');

$name = 'create.module';
$collection['properties'][$name] = array();
$collection['properties'][$name]['type'] = array('Validator:Array');

$name = 'read';
$collection['properties'][$name] = array();
$collection['properties'][$name]['type'] = array('Document');

$name = 'read.module';
$collection['properties'][$name] = array();
$collection['properties'][$name]['type'] = array('Validator:Array');

$name = 'update';
$collection['properties'][$name] = array();
$collection['properties'][$name]['type'] = array('Document');

$name = 'update.module';
$collection['properties'][$name] = array();
$collection['properties'][$name]['type'] = array('Validator:Array');

$name = 'delete';
$collection['properties'][$name] = array();
$collection['properties'][$name]['type'] = array('Document');

$name = 'delete.module';
$collection['properties'][$name] = array();
$collection['properties'][$name]['type'] = array('Validator:Array');

$data[] = $collection;

// module
$collection = array();
$collection['collection'] = 'module';
$collection['properties'] = array();

$name = '_id';
$collection['properties'][$name] = array();
$collection['properties'][$name]['type'] = array('Required', 'Validator:MongoId');

$name = 'name';
$collection['properties'][$name] = array();
$collection['properties'][$name]['type'] = array('Required', 'Validator:StringLength' => array('max' => 32));

$name = 'display_name';
$collection['properties'][$name] = array();
$collection['properties'][$name]['type'] = array('Required', 'Validator:StringLength' => array('max' => 32));

$name = 'objects';
$collection['properties'][$name] = array();
$collection['properties'][$name]['type'] = array('Validator:Array');

$data[] = $collection;

// extra_fields
$collection = array();
$collection['collection'] = 'extra_fields';
$collection['properties'] = array();

$name = '_id';
$collection['properties'][$name] = array();
$collection['properties'][$name]['type'] = array('Required', 'Validator:MongoId');

$name = 'name';
$collection['properties'][$name] = array();
$collection['properties'][$name]['type'] = array('Required', 'Validator:StringLength' => array('max' => 64));

$name = 'display_name';
$collection['properties'][$name] = array();
$collection['properties'][$name]['type'] = array('Required', 'Validator:StringLength' => array('max' => 64));

$name = 'field';
$collection['properties'][$name] = array();
$collection['properties'][$name]['type'] = array('Required', 'Validator:StringLength' => array('max' => 64));

$name = 'display_field';
$collection['properties'][$name] = array();
$collection['properties'][$name]['type'] = array('Required', 'Validator:StringLength' => array('max' => 64));

$name = 'field_type';
$collection['properties'][$name] = array();
$collection['properties'][$name]['type'] = array('Required', 'Validator:StringLength' => array('max' => 64));

$data[] = $collection;

// user
$collection = array();
$collection['collection'] = 'user';
$collection['properties'] = array();

$name = '_id';
$collection['properties'][$name] = array();
$collection['properties'][$name]['type'] = array('Required', 'Validator:MongoId');

$name = 'username';
$collection['properties'][$name] = array();
$collection['properties'][$name]['type'] = array('Required', 'Validator:StringLength' => array('max' => 64));

$name = 'password';
$collection['properties'][$name] = array();
$collection['properties'][$name]['type'] = array('Required', 'Validator:StringLength' => array('max' => 64));

$name = 'role';
$collection['properties'][$name] = array();
$collection['properties'][$name]['type'] = array('Required', 'Validator:MongoId');

$name = 'created';
$collection['properties'][$name] = array();
$collection['properties'][$name]['type'] = array('Required', 'Validator:Date' => array('format' => 'yyyy-mm-dd hh:ii:ss'));

$name = 'updated';
$collection['properties'][$name] = array();
$collection['properties'][$name]['type'] = array('Required', 'Validator:Date' => array('format' => 'yyyy-mm-dd hh:ii:ss'));

$name = 'basicinfo';
$collection['properties'][$name] = array();
$collection['properties'][$name]['type'] = array('Required', 'Document');

$name = 'basicinfo.firstname';
$collection['properties'][$name] = array();
$collection['properties'][$name]['type'] = array('Required', 'Validator:StringLength' => array('max' => 128));

$name = 'basicinfo.lastname';
$collection['properties'][$name] = array();
$collection['properties'][$name]['type'] = array('Required', 'Validator:StringLength' => array('max' => 128));

$name = 'basicinfo.middlename';
$collection['properties'][$name] = array();
$collection['properties'][$name]['type'] = array('Required', 'Validator:StringLength' => array('max' => 128));

$name = 'basicinfo.prefix';
$collection['properties'][$name] = array();
$collection['properties'][$name]['type'] = array('Validator:StringLength' => array('max' => 16));

$name = 'basicinfo.suffix';
$collection['properties'][$name] = array();
$collection['properties'][$name]['type'] = array('Validator:StringLength' => array('max' => 16));

$name = 'basicinfo.birthdate';
$collection['properties'][$name] = array();
$collection['properties'][$name]['type'] = array('Validator:Date' => array('format' => 'yyyy-mm-dd'));

$name = 'basicinfo.avatar';
$collection['properties'][$name] = array();
$collection['properties'][$name]['type'] = array();

$name = 'contactinfo';
$collection['properties'][$name] = array();
$collection['properties'][$name]['type'] = array('Document');

$name = 'contactinfo.phone_mobile';
$collection['properties'][$name] = array();
$collection['properties'][$name]['type'] = array('Validator:StringLength' => array('max' => 32));

$name = 'contactinfo.phone_office';
$collection['properties'][$name] = array();
$collection['properties'][$name]['type'] = array('Validator:StringLength' => array('max' => 32));

$name = 'contactinfo.email';
$collection['properties'][$name] = array();
$collection['properties'][$name]['type'] = array('Validator:EmailAddress');

$name = 'contactinfo.alternate_email';
$collection['properties'][$name] = array();
$collection['properties'][$name]['type'] = array('Validator:EmailAddress');

$name = 'extra_info';
$collection['properties'][$name] = array();
$collection['properties'][$name]['type'] = array('Validator:Array');

$data[] = $collection;

$this->_jsondata = $data;

$output = array();
if ($collectionToView)
{
	foreach ($data as $collection)
	{
		if ($collection['collection'] == $collectionToView)
		{
			$output = $collection;
		}
	}
}
else
{
	$output = $data;
}

if ($pretty)
{
	printr(Zend_Json::prettyPrint(Zend_Json::encode($output)));
	exit();
}
else
{
	$this->_jsondata = $output;
}

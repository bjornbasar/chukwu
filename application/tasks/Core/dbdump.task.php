<?
require APP_CONF . 'DB.conf.php';

$processed = array();
foreach ($DATABASES as $database)
{
	$hostdb = serialize(array('host' => $database['host'], 'db' => $database['db']));
	
	// try to connect to the database first
	$exists = mysqli_connect($database['host'], $database['username'], $database['password'], $database['db']);
	
	if (! in_array($hostdb, $processed) && $exists)
	{
		mysqli_close($exists);
		$sqlfile = APP_DATA . 'sql/' . $database['host'] . '_' . $database['db'] . '.sql';
		$command = "mysqldump --opt -h $database[host] -u$database[username] -p$database[password] $database[db] > $sqlfile";
		
		$this->message($command);
		
		exec($command);
		$processed[] = $hostdb;
	}
}

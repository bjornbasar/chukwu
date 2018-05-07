<?
/**
 * Database Access Configuration
 *
 * @author Sheldon Senseng
 * @copyright Sheldon Senseng <sheldonsenseng@gmail.com>
 * @version 0.1
 *         
 */

$i = 0;

++ $i;
if (! defined('DB_MAIN'))
{
	define('DB_MAIN', $i);
}
$DATABASES[$i]['host'] = 'localhost';
$DATABASES[$i]['username'] = 'inter_admin';
$DATABASES[$i]['password'] = 'NeM2qAUD';
$DATABASES[$i]['db'] = 'db';
$DATABASES[$i]['persistent'] = true;

// add additional databases here
// ++$i;
// if (!defined('DB_TEMP'))
// {
// define('DB_TEMP', $i);
// }
// $DATABASES[$i]['host'] = 'localhost';
// $DATABASES[$i]['username'] = 'username';
// $DATABASES[$i]['password'] = 'password';
// $DATABASES[$i]['db'] = 'test';
// $DATABASES[$i]['persistent'] = true;

// for test data in Unit Tests
++ $i;
if (! defined('DB_TEST'))
{
	define('DB_TEST', $i);
}
$DATABASES[$i]['host'] = $DATABASES[DB_MAIN]['host'];
$DATABASES[$i]['username'] = $DATABASES[DB_MAIN]['username'];
$DATABASES[$i]['password'] = $DATABASES[DB_MAIN]['password'];
$DATABASES[$i]['db'] = 'test_' . $DATABASES[DB_MAIN]['db'];
$DATABASES[$i]['persistent'] = true;

if (! defined('DEFAULT_DB'))
{
	define('DEFAULT_DB', DB_MAIN);
}

// for database based session handling
$i = 0;
if (! defined('DB_SESSION'))
{
	define('DB_SESSION', $i);
}
$DATABASES[$i]['host'] = $DATABASES[DEFAULT_DB]['host'];
$DATABASES[$i]['username'] = $DATABASES[DEFAULT_DB]['username'];
$DATABASES[$i]['password'] = $DATABASES[DEFAULT_DB]['password'];
$DATABASES[$i]['db'] = $DATABASES[DEFAULT_DB]['db'];
$DATABASES[$i]['persistent'] = $DATABASES[DEFAULT_DB]['persistent'];


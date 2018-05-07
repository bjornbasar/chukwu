#!/usr/bin/php
<?
/**
 * Main file for running tasks
 *
 * @author Sheldon Senseng
 * @copyright Sheldon Senseng <sheldonsenseng@gmail.com>
 *           
 */

require 'settings.php';

// set error and exception handlers
set_error_handler ( 'Core_Handler::errorHandlerTask' );
set_exception_handler ( 'Core_Handler::exceptionHandlerTask' );

if ($argc == 1)
{
	exit ( "Please specify a task to run\n" );
}

define ( 'MODULE_FULLNAME', $argv [1] );

$TASK = array_shift ( $argv );
$TASK = array_shift ( $argv );
// Get the task name and parameters - end

// convert parameters to named array
$PARAMS = array ();
foreach ( $argv as $data )
{
	list ( $key, $value ) = explode ( '=', $data );
	$PARAMS [$key] = $value;
}
unset ( $key );
unset ( $value );
unset ( $data );

// parse the tasks.ini file
$TASKS = parse_ini_file ( APP_CONF . 'tasks.ini', true );

// check if the selected module is defined
foreach ( $TASKS as $currenttask => $currenttaskconfig )
{
	if ($currenttask == $TASK)
	{
		// include and display the current module
		unset ( $currenttask );
		
		if (file_exists ( APP_TASKS . $currenttaskconfig ['path'] ))
		{
			$CHUKWU = new Core_Task ( $currenttaskconfig );
			
			$CHUKWU->load ();
		}
		else
		{
			// throw exception for 'file not found'
			throw new Exception ( 'Defined files in tasks.ini is not found' );
		}
		exit ();
	}
}

switch (strtolower ( $TASK ))
{
	case 'list' :
		echo "List of defined Tasks:\n\n";
		foreach ( $TASKS as $currenttask => $currenttaskconfig )
		{
			echo "Name : $currenttask\n";
			echo "Description : $currenttaskconfig[description]\n\n";
		}
		exit ();
		break;
	case 'help' :
		$TASK = array_shift ( $argv );
		if (! $TASK)
		{
			echo "Please specify a task to get help from.\nFollow the following syntax: ./task.php help taskname\nTo get a list of all available tasks, run: ./task.php list\n\n";
			exit ();
		}
		foreach ( $TASKS as $currenttask => $currenttaskconfig )
		{
			if ($currenttask == $TASK)
			{
				echo "Name : $currenttask\n";
				echo "Description : $currenttaskconfig[description]\n";
				echo "Usage : $currenttaskconfig[usage]\n\n";
				exit ();
			}
		}
		echo "$TASK is not a defined task.\nTo get a list of all available tasks, run: ./task.php list\n\n";
		exit ();
		break;
}
// display the 'module not found' page
throw new Exception ( 'Task not found' );
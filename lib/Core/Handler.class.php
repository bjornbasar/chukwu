<?

/**
 * Class for error and exception handling
 *
 * @author Sheldon Senseng
 * @copyright Sheldon Senseng <sheldonsenseng@gmail.com>
 * @version 0.1
 *         
 */
class Core_Handler
{

	/**
	 * The Framework exception handler
	 *
	 * @param $e Exception        	
	 */
	public static function exceptionHandler ($exception)
	{

		$errorMessage = $exception->getMessage() . "<br/>\nfile: " . $exception->getFile() . "<br/>\nline: " . $exception->getLine();
		error_log(strip_tags(date('[Y-m-d H:i:s]') . "\n" . $errorMessage) . "\n\n", 3, APP_LOG . 'exceptions-' . date('Ymd'));
		exit($errorMessage);
	
	}

	/**
	 * The Framework Error Handler
	 *
	 * @param $errorNo mixed        	
	 * @param $errorStr string        	
	 * @param $errorFile string        	
	 * @param $errorLine integer        	
	 * @return boolean
	 */
	public static function errorHandler ($errorNo, $errorStr, $errorFile, $errorLine)
	{

		switch ($errorNo)
		{
			case E_USER_WARNING:
			case E_WARNING:
				$errorMessage = "<b>WARNING</b> [$errorNo]<br/>\n<i>$errorStr</i><br />\n";
				$errorMessage .= "Code is on line $errorLine in file $errorFile";
				$die = false;
				error_log(strip_tags(date('[Y-m-d H:i:s]') . "\n" . $errorMessage) . "\n\n", 3, APP_LOG . 'errors-' . date('Ymd'));
				break;
			
			case E_USER_NOTICE:
			case E_NOTICE:
				$errorMessage = "<b>NOTICE</b> [$errorNo]<br/>\n<i>$errorStr</i><br />\n";
				$errorMessage .= "Code is on line $errorLine in file $errorFile";
				$die = false;
				break;
			
			default:
				$errorMessage = "<b>ERROR $errorNo</b><br/>\n<i>$errorStr</i><br/>\n";
				$errorMessage .= "Code is on line $errorLine in file $errorFile";
				$die = true;
				error_log(strip_tags(date('[Y-m-d H:i:s]') . "\n" . $errorMessage) . "\n\n", 3, APP_LOG . 'errors-' . date('Ymd'));
				break;
		}
		
		if ($die)
		{
			exit($errorMessage);
		}
		
		// Don't execute PHP internal error handler
		return true;
	
	}

	/**
	 * The Framework exception handler for tasks
	 *
	 * @param $e Exception        	
	 */
	public static function exceptionHandlerTask ($exception)
	{

		$errorMessage = $exception->getMessage() . "\nfile: " . $exception->getFile() . "\nline: " . $exception->getLine();
		error_log(strip_tags(date('[Y-m-d H:i:s]') . "\n" . $errorMessage) . "\n\n", 3, APP_LOG . 'exceptions-' . date('Ymd'));
		exit($errorMessage . "\n");
	
	}

	/**
	 * The Framework Error Handler for tasks
	 *
	 * @param $errorNo integer        	
	 * @param $errorStr string        	
	 * @param $errorFile string        	
	 * @param $errorLine integer        	
	 * @return boolean
	 */
	public static function errorHandlerTask ($errorNo, $errorStr, $errorFile, $errorLine)
	{

		switch ($errorNo)
		{
			case E_USER_WARNING:
			case E_WARNING:
				$errorMessage = "WARNING [$errorNo]\n$errorStr\n";
				$errorMessage .= "Code is on line $errorLine in file $errorFile";
				$die = false;
				break;
			
			case E_USER_NOTICE:
			case E_NOTICE:
				$errorMessage = "NOTICE [$errorNo]\n$errorStr\n";
				$errorMessage .= "Code is on line $errorLine in file $errorFile";
				$die = false;
				break;
			
			default:
				$errorMessage = "ERROR [$errorNo]\n$errorStr\n";
				$errorMessage .= "Code is on line $errorLine in file $errorFile";
				$die = true;
				break;
		}
		
		error_log(strip_tags(date('[Y-m-d H:i:s]') . "\n" . $errorMessage) . "\n\n", 3, APP_LOG . 'errors-' . date('Ymd'));
		
		if ($die)
		{
			exit($errorMessage . "\n");
		}
		
		// Don't execute PHP internal error handler
		return true;
	
	}

}
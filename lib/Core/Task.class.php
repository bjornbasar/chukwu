<?

/**
 * Main Framework class for task handling
 *
 * @author Sheldon Senseng
 * @copyright Sheldon Senseng <sheldonsenseng@gmail.com>
 * @version 0.1
 *         
 */
class Core_Task
{

	/**
	 * Contains the starting timestamp for the task
	 *
	 * @var float
	 */
	private $start;

	/**
	 * Contains the ending timestamp for the task
	 *
	 * @var float
	 */
	private $end;

	/**
	 * Task parameters as defined in the task configuration file
	 *
	 * @var array
	 */
	private $parameters;

	/**
	 * The task name
	 *
	 * @var string
	 */
	private $task;

	/**
	 * Class Constructor that sets the task parameters, name and starting
	 * timestamp
	 *
	 * @param $parameters array        	
	 */
	public function __construct ($parameters)
	{

		$this->parameters = $parameters;
		$this->task = $parameters['name'];
		$this->start = microtime(true);
	
	}

	/**
	 * Loads the task file
	 */
	public function load ()
	{

		$parameters = $this->parameters;
		
		echo "Running $parameters[name]...\n\n";
		$this->message(date('Y-m-d H:i:s') . ' Starting Task', false);
		
		global $PARAMS;
		
		require APP_TASKS . $parameters['path'];
	
	}

	/**
	 * Logs a message into the task log file and optionally outputs the message
	 * to the command-line
	 *
	 * @param $message string        	
	 * @param $display boolean        	
	 */
	public function message ($message, $display = true)
	{

		if ($display)
		{
			echo $message . "\n";
		}
		error_log($message . "\n", 3, APP_LOG . 'task.' . $this->task . '.log');
	
	}

	/**
	 * Class destructor that computes the total runtime for the script and logs
	 * the task execution into the task log file
	 */
	public function __destruct ()
	{

		$this->end = microtime(true);
		
		$this->message("\n\nTask Done\nTask runtime: " . ($this->end - $this->start) . " seconds\n\n");
		$this->message(date('Y-m-d H:i:s') . ' Ending Task', false);
	
	}

}
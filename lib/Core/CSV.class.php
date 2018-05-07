<?

/**
 * Class to generate a CSV file from given data
 *
 * @author Sheldon Senseng
 * @copyright Sheldon Senseng <sheldonsenseng@gmail.com>
 * @version 0.1
 *         
 */
class Core_CSV
{

	private $_file;

	private $_filename;

	private $_writemode;

	/**
	 * Class Constructor
	 */
	public function __construct ()
	{

	}

	/**
	 * Creates the CSV file
	 *
	 * @param $filename string        	
	 * @param $willOverwrite boolean        	
	 * @throws Exception
	 */
	public function createCSV ($filename, $willOverwrite = false)
	{

		if (! $filename)
		{
			throw new Exception('Filename not specified for CSV output file.');
		}
		
		$this->_filename = $filename;
		
		if ($willOverwrite)
		{
			$this->_writeMode = 'w+';
		}
		else
		{
			$this->_writeMode = 'a+';
		}
		
		$this->_file = fopen($this->_filename, $this->_writeMode);
		
		if ($this->_file == false)
		{
			throw new Exception('Error opening filename provided for CSV output. ' . $this->_filename);
		}
	
	}

	/**
	 * Add a row of data to the CSV file
	 *
	 * @param $row array        	
	 * @throws Exception
	 */
	public function writeRow ($row)
	{

		if ($this->_file)
		{
			fputcsv($this->_file, $row, ',', '"');
		}
		else
		{
			throw new Exception('CSV file for output not initialized');
		}
	
	}

	/**
	 * Add an entire dataset to the CSV file
	 *
	 * @param $data array        	
	 * @throws Exception
	 */
	public function writeData ($data)
	{

		if ($this->_file)
		{
			foreach ($data as $row)
			{
				fputcsv($this->_file, $row, ',', '"');
			}
		}
		else
		{
			throw new Exception('CSV file for output not initialized');
		}
	
	}

	/**
	 * Retrieve and parse a CSV file
	 *
	 * @param $file string        	
	 * @param $hasHeaders boolean        	
	 * @return array
	 */
	public function readCSV ($file, $hasHeaders = false)
	{

		$fh = fopen($file, 'r');
		
		$returnvalue = array();
		
		if ($hasHeaders)
		{
			// get first line to be used as headers
			$headers = fgetcsv($fh);
			printr($headers);
		}
		
		$data = fgetcsv($fh);
		while ($data)
		{
			if ($hasHeaders)
			{
				$returnvalue[] = array_fill_keys($headers, $data);
			}
			else
			{
				$returnvalue[] = $data;
			}
			$data = fgetcsv($fh);
		}
		
		return $returnvalue;
	
	}

	/**
	 * Class Destructor
	 */
	public function __destruct ()
	{

		if ($this->_file)
		{
			fclose($this->_file);
		}
	
	}

}
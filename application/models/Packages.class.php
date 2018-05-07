<?

class Packages
{

	private $packages_id;

	private $type;

	private $path;

	public $result;

	private $earnings = 0;

	private $deductions = 0;

	private $employerearnings = 0;

	private $employerdeductions = 0;

	public function __construct ($packages_id, $type, $path, $parameters = null)
	{

		if (is_null($parameters))
		{
			global $PARAMS;
		}
		else
		{
			$PARAMS = $parameters;
		}
		
		$this->packages_id = $packages_id;
		$this->type = $type;
		
		switch ($type)
		{
			case 'process':
				$this->payrolls_id = array_shift($PARAMS);
				$this->users_id = array_shift($PARAMS);
				
				if (! $this->payrolls_id)
				{
					throw new Exception('No Payroll specified to process');
				}
				
				if (! $this->users_id)
				{
					throw new Exception('No User specified to process');
				}
				
				$this->parameters = $PARAMS;
				break;
			
			case 'admin':
				$this->parameters = $PARAMS;
				break;
		}
		
		$this->path = dirname(APP_MODULES . $path) . '/';
		
		require APP_MODULES . $path;
	
	}

	public function getResult ()
	{

		return array('payrolls_id' => $this->payrolls_id, 'users_id' => $this->users_id, 'earnings' => $this->earnings, 'deductions' => $this->deductions, 'employerearnings' => $this->employerearnings, 'employerdeductions' => $this->employerdeductions);
	
	}

	public function getData ($key, $users_id = null)
	{

		if (is_null($users_id) && (! isset($this->users_id) || ! $this->users_id))
		{
			return false;
		}
		else
		{
			// this is where they should be getting all the data, packages will not be given access to the database
		}
	
	}

	public function getUserData ()
	{

		$db = new Core_DB();
		
		$query = "select * from users where users_id = ?";
		return $db->getRow($query, array($this->users_id));
	
	}

	public function getPayrollData ()
	{

		$db = new Core_DB();
		
		$query = "select * from payrolls where payrolls_id = ?";
		return $db->getRow($query, array($this->payrolls_id));
	
	}

	public function getBasePay ()
	{

		$db = new Core_DB();
		
		$query = "select * from computations where users_id = ? and payrolls_id = ? and packages_id = ?";
		$result = $db->getRow($query, array($this->users_id, $this->payrolls_id, 1));
		
		return $result['earnings'] - $result['deductions'];
	
	}

	public function getCurrentEarnings ()
	{

		$db = new Core_DB();
		
		$query = "select sum(earnings) as `earnings` from computations where users_id = ? and payrolls_id = ?";
		$result = $db->getRow($query, array($this->users_id, $this->payrolls_id));
		
		return $result['earnings'];
	
	}

	public function getCurrentDeductions ()
	{

		$db = new Core_DB();
		
		$query = "select sum(deductions) as `deductions` from computations where users_id = ? and payrolls_id = ?";
		$result = $db->getRow($query, array($this->users_id, $this->payrolls_id));
		
		return $result['deductions'];
	
	}

	public function getCurrentNet ()
	{

		$net = $this->getCurrentEarnings() - $this->getCurrentDeductions();
		
		return $net;
	
	}

	public function getPackageTotals ($from, $to)
	{

		$db = new Core_DB();
		
		$query = "select sum(earnings) as earnings, sum(deductions) as deductions, sum(employerearnings) as employerearnings, sum(employerdeductions) as employerdeductions from computations where users_id = ? and packages_id = ? and paymentdate between ? and ?";
		$result = $db->getRow($query, array($this->users_id, $this->packages_id, $from, $to));
		
		return $result;
	
	}

	public function useDB ()
	{

		$this->db = new Core_Sqlite('package.sqlite', $this->path);
		
		return $this->db;
	
	}

	public function setStatusCode ($code)
	{

		header("HTTP/1.1 $code", true, $code);
	
	}

}
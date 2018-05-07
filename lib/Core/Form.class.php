<?

class Core_Form
{

	private $db;

	/**
	 * Instantiate DB object
	 */
	public function __construct ()
	{

		$this->db = new Core_DB();
	
	}

	/**
	 * Generate the form based on the database structure
	 *
	 * @param $table string        	
	 * @param $addLabel boolean        	
	 * @return string
	 */
	public function createForm ($table, $addLabel = false)
	{

		$form = array();
		if ($this->_tableExists($table))
		{
			$query = "desc `$table`";
			$result = $this->db->getArray($query);
			
			// if pri -> hidden
			// text -> textarea
			// foreign key -> select
			// all others -> input
			
			foreach ($result as $row)
			{
				$data = array();
				$data['name'] = $row['Field'];
				if ($row['Key'] == 'PRI')
				{
					$data['type'] = 'hidden';
				}
				elseif ($row['Field'] == 'password')
				{
					$data['type'] = 'password';
					$data['label'] = $row['Field'];
				}
				elseif (substr($row['Field'], - 3) == '_id')
				{
					$foreignTable = substr($row['Field'], 0, strlen($row['Field']) - 3);
					
					if ($this->_tableExists($foreignTable))
					{
						$data['type'] = 'select';
						// generate data for list
						$query = "select * from `$foreignTable`";
						$data['data'] = $this->db->getArray($query);
						$data['label'] = $foreignTable;
					}
					else
					{
						$data['type'] = 'text';
						$data['label'] = $row['Field'];
					}
				}
				else
				{
					$data['type'] = 'text';
					$data['label'] = $row['Field'];
				}
				
				if ($data['type'] != 'hidden')
				{
					$label = "<label for='$data[name]'>" . ucfirst($data['label']) . "</label>\n";
				}
				else
				{
					$label = '';
				}
				
				$element = '';
				
				switch ($data['type'])
				{
					case 'select':
						$element .= "<select name='$data[name]' id='$data[name]'>\n";
						
						foreach ($data['data'] as $options)
						{
							$element .= "<option value='" . $options[$data['name']] . "'>" . isset($options['name']) ? $options['name'] : isset($options['description']) ? $options['description'] : '' . "</option>\n";
						}
						
						$element .= "</select>\n";
						break;
					
					case 'hidden':
					case 'text':
					case 'password':
						$element .= "<input type='$data[type]' name='$data[name]' id='$data[name]'>";
						break;
				}
				
				$form[] = array('label' => $label, 'element' => $element);
			}
		}
		
		return $form;
	
	}

	/**
	 * Verify if the table exists
	 *
	 * @param $table string        	
	 * @return boolean
	 */
	private function _tableExists ($table)
	{

		$query = "show tables like '$table'";
		$result = $this->db->getArray($query);
		
		if (count($result) > 0)
		{
			return true;
		}
		return false;
	
	}

}
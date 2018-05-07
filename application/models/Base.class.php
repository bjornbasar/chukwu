<?

/**
 * Base Class for Data Models
 *
 * @author Sheldon Senseng
 * @copyright Sheldon Senseng <sheldonsenseng@gmail.com>
 * @version 0.1
 *         
 */

abstract class Base
{

	protected $db;

	protected $data;

	protected $table = null;

	protected $primary = null;

	public function __construct ()
	{

		$this->db = new Core_DB();
		
		if (is_null($this->table))
		{
			$this->table = strtolower(get_class($this));
		}
		
		if (is_null($this->primary))
		{
			$this->primary = $this->table . '_id';
		}
	
	}

	public final function __set ($var, $value)
	{

		$this->data[$var] = $value;
	
	}

	public final function __get ($var)
	{

		if (isset($this->data[$var]))
		{
			return $this->data[$var];
		}
		throw new Exception('Object property was not found');
	
	}

	public final function getDbData ($columns = '*', $where = null, $order = null, $onlyone = false)
	{

		$table = $this->table;
		
		$query = "select $columns from `$table`";
		
		if (! is_null($where))
		{
			$query .= " where $where ";
		}
		
		if (! is_null($order))
		{
			$query .= " order by $order";
		}
		
		if ($onlyone)
		{
			return $this->db->getRow($query, array());
		}
		else
		{
			return $this->db->getArray($query, array());
		}
	
	}

	public final function getAll ()
	{

		$query = "select * from `" . $this->table . "`";
		return $this->db->getArray($query, array());
	
	}

	public final function get ($id)
	{

		$query = "select * from `" . $this->table . "` where `" . $this->primary . "` = ?";
		return $this->db->getRow($query, array($id));
	
	}

	public final function getBy ($data = array())
	{

		$where = ' 1 ';
		foreach ($data as $key => $value)
		{
			$where .= " and `$key` = '$value' ";
		}
		$query = "select * from `" . $this->table . "` where $where";
		return $this->db->getArray($query, array());
	
	}

	public final function update ($data)
	{

		if (isset($data[$this->primary]) && $data[$this->primary])
		{
			$primary = $data[$this->primary];
			unset($data[$this->primary]);
			$this->db->autoexecute($this->table, $data, array($this->primary => $primary));
		}
		else
		{
			$this->db->autoexecute($this->table, $data);
		}
	
	}

}

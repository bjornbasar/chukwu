<?
/**
 * Search class based on Zend_Search_Lucene
 *
 * @author Sheldon Senseng
 * @copyright Sheldon Senseng <sheldonsenseng@gmail.com>
 * @version 0.1
 *         
 */

/**
 * Path for the Search Indices
 */
define('APP_DATA_SEARCH', APP_DATA . 'search/');

class Core_Search
{

	/**
	 * Zend_Search_Lucene index
	 *
	 * @var Zend_Search_Lucene
	 */
	private $index;

	/**
	 * Clas Constructor that sets the search index to be used
	 *
	 * @param $indexname string        	
	 */
	public function __construct ($indexname = 'core')
	{

		try
		{
			$this->index = Zend_Search_Lucene::open(APP_DATA_SEARCH . $indexname);
		}
		catch (Exception $e)
		{
			$this->index = Zend_Search_Lucene::create(APP_DATA_SEARCH . $indexname);
		}
	
	}

	/**
	 * Adds a single document to the search index
	 *
	 * @param $uniqueId string        	
	 * @param $source string        	
	 * @param $uniqueIdField string        	
	 * @param $data array        	
	 */
	public function addSingle ($uniqueId, $source, $uniqueIdField, $data = array())
	{

		$document = new Zend_Search_Lucene_Document();
		
		$document->addField(Zend_Search_Lucene_Field::unIndexed('uniqueid_value', $uniqueId));
		$document->addField(Zend_Search_Lucene_Field::unIndexed('uniqueid_source', $source));
		$document->addField(Zend_Search_Lucene_Field::unIndexed('uniqueid_field', $uniqueIdField));
		foreach ($data as $key => $value)
		{
			$document->addField(Zend_Search_Lucene_Field::unStored($key, $value));
		}
		
		$this->index->addDocument($document);
		
		$this->index->commit();
	
	}

	/**
	 * Adds multiple documents to the search index
	 *
	 * @param $uniqueId string        	
	 * @param $source string        	
	 * @param $uniqueIdField string        	
	 * @param $data array        	
	 */
	public function addMultiple ($uniqueId, $source, $uniqueIdField, $data = array())
	{

		foreach ($data as $doc)
		{
			$this->addSingle($uniqueId, $source, $uniqueIdField, $doc);
		}
	
	}

	/**
	 * Adds a single weighted document to the search index
	 *
	 * @param $uniqueId string        	
	 * @param $source string        	
	 * @param $uniqueIdField string        	
	 * @param $data array        	
	 */
	public function addSingleWeighted ($uniqueId, $source, $uniqueIdField, $data = array())
	{

		$document = new Zend_Search_Lucene_Document();
		
		$document->addField(Zend_Search_Lucene_Field::unIndexed('uniqueid_value', $uniqueId));
		$document->addField(Zend_Search_Lucene_Field::unIndexed('uniqueid_source', $source));
		$document->addField(Zend_Search_Lucene_Field::unIndexed('uniqueid_field', $uniqueIdField));
		foreach ($data as $value)
		{
			$field = Zend_Search_Lucene_Field::unStored($value['name'], $value['value']);
			if (isset($value['weight']))
			{
				$field->boost = $value['weight'];
			}
			$document->addField($field);
		}
		
		$this->index->addDocument($document);
		
		$this->index->commit();
	
	}

	/**
	 * Adds multiple weighted documents to the search index
	 *
	 * @param $uniqueId string        	
	 * @param $source string        	
	 * @param $uniqueIdField string        	
	 * @param $data array        	
	 */
	public function addMultipleWeighted ($uniqueId, $source, $uniqueIdField, $data = array())
	{

		foreach ($data as $doc)
		{
			$this->addSingleWeighted($uniqueId, $source, $uniqueIdField, $doc);
		}
	
	}

	/**
	 * Runs an optimization task on the search index
	 */
	public function optimize ()
	{

		$this->index->optimize();
	
	}

	/**
	 * Performs a search on the index
	 *
	 * @param $query string        	
	 * @return array
	 */
	public function search ($query)
	{

		return $this->index->find($query);
	
	}

	/**
	 * Class Destructor and optimize the index
	 */
	public function __destruct ()
	{

		$this->optimize();
	
	}

}
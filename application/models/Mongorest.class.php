<?php

/**
 * Used by the default REST Server Implementation to extend the Core REST Server using MongoDB
 *
 * @author Sheldon Senseng
 * @copyright Sheldon Senseng <sheldonsenseng@gmail.com>
 * @version 0.1
 *         
 */
class MongoRest extends Core_RestServer
{

	public function __construct ()
	{

		$this->_type = 'mongodb';
		parent::__construct();
	
	}

}
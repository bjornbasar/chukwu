<?php

/**
 * Used by the default REST Server Implementation to extend the Core REST Server
 *
 * @author Sheldon Senseng
 * @copyright Sheldon Senseng <sheldonsenseng@gmail.com>
 * @version 0.1
 *         
 */
class OauthRest extends Core_RestServer
{

	public function __construct ()
	{

		$this->_type = 'oauth';
		parent::__construct();
	
	}

}
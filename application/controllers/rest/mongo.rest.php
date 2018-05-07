<?php
/**
 * This default controller automatically creates a REST Server for your MongoDB Database
 */

$defaultRestObj = new MongoRest();

$this->_restData = $defaultRestObj;


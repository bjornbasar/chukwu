<?php
/**
 * This default controller automatically creates a REST Server for your Database
 */

$defaultRestObj = new MysqlRest();

$this->_restData = $defaultRestObj;
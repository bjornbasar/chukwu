<?
$this->message("Initiating Authentication Object...");
$auth = new Core_Authentication();

$this->message("Refreshing Authentication Data File...");
$auth->dataRefresh();

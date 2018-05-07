<?
$this->message("Initiating DB Object...");
$db = new Core_DB();

$this->message("Refreshing DB Data File...");
$db->dataRefresh();

<?
extract($PARAMS, EXTR_REFS);

if (! isset($message))
{
	$this->message("Please enter a message to be used for the commit.\nexample: ./task.php svn_ci message=\"some useful comment here\"");
}
else
{
	$svn = new Core_SVN();
	$svn->updateStatus();
	
	$this->message("Adding Unversioned and Unignored Files...\n");
	$svn->addUnversioned();
	
	$this->message("Updating Status...\n");
	$svn->updateStatus();
	
	$files = $svn->getFiles();
	
	$this->message("Affected files in the Working Copy:");
	foreach ($files as $file)
	{
		$this->message(str_pad("$file[status_text]", 15, ' ', STR_PAD_RIGHT) . $file['path']);
	}
	
	if (count($files) > 0)
	{
		$this->message(count($files) . ' file(s) to Commit...');
		
		$result = $svn->commit($message);
		
		$this->message("\nRevision: $result[revision]\nBy: $result[name]\n\nCommit Finished!");
	}
}


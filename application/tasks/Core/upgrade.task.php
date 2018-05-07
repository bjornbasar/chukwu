<?
$listFound = false;
$performUpgrade = true;

// get revision from HEAD of SVN
exec("svn info " . APP_SVN_REPOSITORY, $output);

foreach ($output as $line)
{
	if (strpos($line, 'Revision:') === 0)
	{
		$revision = (int) trim(str_replace('Revision:', '', $line));
	}
}

// check if framework is up to date
if (file_exists(APP_UPGRADE_REVISION))
{
	$fh = fopen(APP_UPGRADE_REVISION, 'r');
	$localRevision = (int) trim(fread($fh, filesize(APP_UPGRADE_REVISION)));
	
	if ($localRevision >= $revision)
	{
		$performUpgrade = false;
	}
}

if ($performUpgrade)
{
	$this->message("Retrieving remote copy of upgrade file list...\n");
	exec('svn export ' . APP_SVN_REPOSITORY . str_replace(APP_ROOT, '', APP_UPGRADE_FILELIST) . ' ' . APP_UPGRADE_FILELIST);
	
	if (file_exists(APP_UPGRADE_FILELIST))
	{
		$listFound = true;
	}
	
	if ($listFound)
	{
		$this->message("Retrieving List of files to upgrade...\n");
		// read list file
		$fh = fopen(APP_UPGRADE_FILELIST, 'r');
		$list = fread($fh, filesize(APP_UPGRADE_FILELIST));
		
		$fileList = unserialize($list);
		fclose($fh);
		
		$this->message("Retrieving latest copy of framework...\n");
		exec('svn export ' . APP_SVN_REPOSITORY . ' ' . APP_UPGRADE);
		
		$this->message("Upgrading local copy...\n");
		
		$progress = new Core_Progress(0, count($fileList));
		foreach ($fileList as $file)
		{
			copy(APP_UPGRADE . $file, APP_ROOT . $file);
			$progress->next();
		}
		
		$fh = fopen(APP_UPGRADE_REVISION, 'w+');
		fwrite($fh, $revision);
		fclose($fh);
		
		$this->message("Cleaning up...\n");
		exec('rm -fr ' . APP_UPGRADE);
		
		$this->message("Upgrade complete!");
	}
	else
	{
		$this->message("Cannot perform upgrade.\nList file missing.");
	}
}
else
{
	$this->message("Your framework is up-to-date.");
}
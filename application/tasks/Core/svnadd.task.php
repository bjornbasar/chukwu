<?
extract($PARAMS, EXTR_REFS);

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

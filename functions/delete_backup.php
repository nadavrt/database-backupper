<?php 

	/**
	*	Search for a file in the sql or zip directories.
	*	@param String $fileType The file type to search for. Should be either sql or zip.
	*	@return Boolean TRUE if the file was found or FALSE if it wasn't.
	**/
	function find_file($fileName = FALSE, $fileType = FALSE)
	{
		if ( !$fileName || !$fileType ) return FALSE;

		$files = scandir('../backups/' . $fileType . '/');
		$fileFound = FALSE;
		foreach ($files as $file)
		{
			if ( $file == $fileName . '.' . $fileType) 
			{
				$fileFound = TRUE;
				break;
			}
		}

		return $fileFound;

	}


	if ( !isset($_POST['fileName']) )
	{
		echo FALSE;
		die;
	}
	
	$sqlFile = find_file($_POST['fileName'], 'sql');
	$zipFile = find_file($_POST['fileName'], 'zip');

	if (!$sqlFile && !$zipFile)
	{
		echo FALSE;
		die;
	}
	
	if ($sqlFile) unlink('../backups/sql/' . $_POST['fileName'] . '.sql');
	if ($zipFile) unlink('../backups/zip/' . $_POST['fileName'] . '.zip');
	echo TRUE;
?>
<?php

	if ( (!isset($_FILES['import'])) || ($_FILES['import']['error'] == 4) )
	{
		echo json_encode( array('error' => 'No file was detected. Please make sure your file is within the upload size limit and try again.') );
		die();
	}
	$extension = explode('.', $_FILES['import']['name']);
	$extension = array_pop($extension);
	if ( ($extension != 'sql') && ($extension != 'zip') )
	{
		echo json_encode( array('error' => 'Invalid file type. Make sure your file is a valid SQL file or a zipped SQL file.') );
		die();
	}

	require '../credentials.php';
	import_backup(DB_NAME,DB_USER,DB_HOST,DB_PASSWORD,DB_CHARSET,$extension);


/**
*	Delete all the files in the tmp (temporary) folder.
**/
function delete_tmp_folder_content()
{
	$baseDir = '../backups/tmp/';
	$files = scandir($baseDir);
	foreach ($files as $file) 
	{
		if ( ($file != '.') && ($file != '..') && ($file != '.htaccess') ) 
		{
			if ( is_dir($baseDir . $file) ) recursive_deletion($baseDir.$file.'/');
			else unlink('../backups/tmp/' . $file);
		}
	}
}

/**
* 	Recursively delete all files and folders in the base directory provided and its sub-directories.
*	@param String $baseDir The base directory to scan and delete files and directories in. Must be relative to the functions folder.
*	@return NULL
**/
function recursive_deletion($baseDir)
{
	$files = scandir($baseDir);
	foreach ($files as $file) 
	{
		if ( ($file != '.') && ($file != '..') ) 
		{
			if ( is_dir($baseDir . $file) ) recursive_deletion($baseDir.$file.'/');
			else unlink($baseDir . $file);
		}
	}
	rmdir($baseDir);
}


/** 
*	Export a database to SQL form in gzip format.
*	@param  String $dbName The database name
*	@param  String $dbUser The database username
*	@param  String $dbHost The database host
*	@param  String $dbPass The database password
*	@param  String $dbCharset The database charset. This is only used to read the existing tables' data. This will NOT affect the database charset.
*	@param  String $extension The extension of the file to be imported.
*	@return Array A success or error message depending on the result of the import process.
**/
function import_backup($dbName,$dbUser,$dbHost,$dbPass,$dbCharset,$extension)
{
	$db = @mysqli_connect($dbHost,$dbUser,$dbPass,$dbName);
	if ( !$db )
  	{
  		echo json_encode( array('error' => 'Failed to connect to database: ' . mysqli_connect_error() ) );
  		die;
  	}

	$db->set_charset("$dbCharset");

	//Find and drop all the tables within the database.
	$query = '';
	$tables= array();
	$result = mysqli_query($db, 'SHOW TABLES');
	while($row = mysqli_fetch_row($result))
	{
		$tables[] = $row[0];
	}

	if ( (is_array($tables)) && !empty($tables) )
	{
		$tables = implode(',', $tables);
		$query = "DROP TABLE IF EXISTS $tables; \n";	
		mysqli_query($db, $query);
	}

	//If the file is zipped, unzip it
	if ( $extension == 'zip')
	{
		if ( class_exists( 'ZipArchive' ) )
		{
			$zip = new ZipArchive;
			$result = $zip->open($_FILES['import']['tmp_name']);
			if ( $result === FALSE )
			{
				echo json_encode( array('error' => 'Failed to open zipped file.' ) );
  				die;
			}
			else
			{
				//Extract the zip file into the temporary directory.
				$zip->extractTo('../backups/tmp/');
				$zip->close();
				$files = scandir('../backups/tmp/');
				$fileFound = FALSE;
				foreach ($files as $file) {
					if ( (strpos($file, '.sql')) !== FALSE )
					{
						$_FILES['import']['tmp_name'] = '../backups/tmp/' . $file;
						$fileFound = TRUE;
						break;
					}
				}

				if ( !$fileFound )
				{
					delete_tmp_folder_content();
					echo json_encode( array('error' => 'No SQL file was found. Please make sure your zipped file contains an SQL file.' ) );
  					die;		
				}
			}
		}
		else 
		{
			echo json_encode( array('error' => 'Class ZipArchive is not present. Please make sure your PHP version is 5.2.0 or above, or use an unzipped SQL file instead.' ) );
  			die;
		}	
	}
	

	//Import the new database tables
	$query = '';
	$lines = file($_FILES['import']['tmp_name']);
	foreach ($lines as $line)
	{
		// Skip it if it's a comment
		if (substr($line, 0, 2) == '--' || $line == '') continue;

		// Add this line to the current segment
		$query .= $line;
		// If it has a semicolon at the end, it's the end of the query
		if (substr(trim($line), -1, 1) == ';')
		{
		    // Perform the query
		    $result = mysqli_query($db, $query);

	    	if ($result == FALSE) 
			{
				// $erroneousLine = $line;
				break;
			}

		    // Clear the query and move to the next one.
		    $query = '';
		}
	}

	if ( $extension == 'zip' ) delete_tmp_folder_content();

	if ($result) echo json_encode( array('success' => 'Import completed successfully.') );
	else echo json_encode( array('error' => 'Database error. Please make sure the import file contains a valid SQL script.') );
}
?>
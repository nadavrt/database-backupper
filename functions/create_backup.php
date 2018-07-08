<?php
require '../credentials.php';
$createFile = ( isset($_POST['sqlFile']) )? TRUE:FALSE;
$createZip = ( isset($_POST['zipFile']) )? TRUE:FALSE;
create_backup(DB_NAME, DB_USER, DB_HOST, DB_PASSWORD, DB_CHARSET, '*', $createFile,$createZip);

/** 
*	Export a database to SQL form in gzip format.
*	@param  String $dbName The database name
*	@param  String $dbUser The database username
*	@param  String $dbHost The database host
*	@param  String $dbPass The database password
*	@param  String $dbCharset The database charset. This is only used to read the existing tables' data. This will NOT affect the database charset.
*	@param  Mixed $tables An array of all the tables to be backuped, or a string of all tables separate by commas. The Default is to backup all the tables.
*	@param  Boolean $createFile Whether or not to create an SQL file. Defaults to TRUE.
*	@param  Boolean $createZip Whether or not to create a zipped SQL file. Defaults to FALSE.
*	@return Array The relative links for the SQL and or ZIP files that were created.
**/
function create_backup($dbName, $dbUser, $dbHost, $dbPass, $dbCharset, $tables = '*', $createFile = TRUE, $createZip = FALSE)
{
	if ( !$createFile && !$createZip ) return;

	$db = @mysqli_connect($dbHost,$dbUser,$dbPass,$dbName);
	if ( !$db )
  	{
  		echo json_encode( array('error' => 'Failed to connect to database: ' . mysqli_connect_error() ) );
  		die;
  	}

	$db->set_charset("$dbCharset");
	
	//Get all of the tables to backup
	if($tables == '*')
	{
		$tables = array();
		$result = mysqli_query($db, 'SHOW TABLES');
		while($row = mysqli_fetch_row($result))
		{
			$tables[] = $row[0];
		}
	}
	else
	{
		$tables = is_array($tables) ? $tables : explode(',',$tables);
	}

	$return = '';
	
	//Cycle through and create the backup
	foreach($tables as $table)
	{
		$result = mysqli_query($db, 'SELECT * FROM '.$table);
		$numFields = mysqli_num_fields($result);
		//$return.= 'DROP TABLE '.$table.';';
		$createTable = mysqli_fetch_row(mysqli_query($db, 'SHOW CREATE TABLE '.$table));
		$return.= "\n".$createTable[1].";\n\n"; //$createTable[0] contains the label name. $createTable[1] is the SQL to create that table structure.
		

			while($row = mysqli_fetch_row($result))
			{
				$return.= 'INSERT INTO '.$table.' VALUES(';
				for($j=0; $j<$numFields; $j++) 
				{
					$row[$j] = addslashes($row[$j]); //Prepare string for the future SQL query.
					$row[$j] = str_replace("\n", '\n', $row[$j]);
					if (isset($row[$j])) { $return.= '"'.$row[$j].'"' ; } else { $return.= '""'; }
					if ($j<($numFields-1)) { $return.= ','; }
				}
				$return.= ");\n";
			}
		
		$return.="\n";
	}

	$fileName = DB_NAME .'-' . time();
	$filesUrl = array( 'sql' => 'backups/sql/' . $fileName .'.sql' );

	//save the SQL file to the server uploads directory
	if ( !file_exists('../backups') ) { mkdir('../backups', 0777, TRUE); }
	if ( !file_exists('../backups/sql') ) { mkdir('../backups/sql', 0777, TRUE); }
	$handle = fopen( '../backups/sql/' . $fileName .'.sql','w+');
	fwrite($handle,$return);
	fclose($handle);

	if ( $createZip )
	{
		if ( class_exists( 'ZipArchive' ) )
		{
			if ( !file_exists('../backups/zip') ) { mkdir('../backups/zip', 0777, TRUE); }
			$zip = new ZipArchive;
			$zip->open('../backups/zip/' . $fileName . ".zip", ZipArchive::CREATE);
	      	    $zip->addFile('../backups/sql/' . $fileName . '.sql', $fileName . '.sql');
			$zip->close();
			$filesUrl['zip'] = 'backups/zip/' . $fileName .'.zip';
		}
		else 
		{
			echo json_encode( array('error' => 'Class ZipArchive is not present. Please make sure your PHP version is 5.2.0 or above.') );
			die;
		}

		if ( !$createFile )
		{
			unlink('../backups/sql/' . $fileName . '.sql');
			unset($filesUrl['sql']);	
		} 

		echo json_encode($filesUrl);

	}
	else echo json_encode($filesUrl);
	
}

?>
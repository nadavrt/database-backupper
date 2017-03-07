<?php
/**
*	Scan and retuns a list of all the content in the sql and zip folders.
*	@param NULL
*	@return Array An array containing all the sql and zip file names and creation date.
**/
function get_backups()
{
	//Check if this file was loaded by the index or by an ajax call
	$sqlDir = (isset($_POST['ajax']))? '../backups/sql/':'backups/sql/';
	$zipDir = (isset($_POST['ajax']))? '../backups/zip/':'backups/zip/';

	$sql = scandir($sqlDir);
	$dot = array_search('.', $sql);
	$doubleDot = array_search('..', $sql);
	if ( $dot !== FALSE ) unset($sql[$dot]); 
	if ( $doubleDot !== FALSE ) unset($sql[$doubleDot]); 

	$zip = scandir($zipDir);
	$dot = array_search('.', $zip);
	$doubleDot = array_search('..', $zip);
	if ( $dot !== FALSE ) unset($zip[$dot]); 
	if ( $doubleDot !== FALSE ) unset($zip[$doubleDot]);
	
	$backups = array();
	$sqlCount = count($sql);
	$zipCount = count($zip);
	
	if ( ($sqlCount+$zipCount) != 0 )
	{
		if ( $sqlCount >= $zipCount )
		{
			foreach ($sql as $file) 
			{
				$fileName = explode('.sql', $file);
				$fileName = $fileName[0];
				$created = explode('-', $fileName);
				$created =  $created[count($created)-1];
				
				$zipFile = array_search($fileName . '.zip', $zip);
				if ( $zipFile !== FALSE )
				{
					unset($zip[$zipFile]);
					$backups[$created] = array(
						'name' => $fileName,
						'created' => $created,
						'sql' => $fileName . '.sql',
						'zip' => $fileName . '.zip'
					);

				}
				else $backups[$created] = array(
						'name' => $fileName,
						'created' => $created,
						'sql' => $fileName . '.sql',
						'zip' => FALSE
				);
			}

			foreach ($zip as $file) 
			{
				$fileName = explode('.zip', $file);
				$fileName = $fileName[0];
				$created = explode('-', $fileName);
				$created =  $created[count($created)-1];

				//If there is already an array key with this name search for an open array key position.
				while ( isset($backups[$created]) )
				{
					$created = (int)$created + 1;
				}
				$backups[$created] = array(
						'name' => $fileName,
						'created' => $created,
						'sql' => FALSE,
						'zip' => $fileName . '.zip'
				);
			}
		}
		else
		{
			foreach ($zip as $file) 
			{
				$fileName = explode('.zip', $file);
				$fileName = $fileName[0];
				$created = explode('-', $fileName);
				$created =  $created[count($created)-1];
				
				$sqlFile = array_search($fileName . '.sql', $sql);
				if ( $sqlFile !== FALSE )
				{
					unset($sql[$sqlFile]);
					$sqlFile = $fileName . '.sql';
				}
				
				$backups[$created] = array(
					'name' => $fileName,
					'created' => $created,
					'sql' => $sqlFile,
					'zip' => $fileName . '.zip'
				);

			}

			foreach ($sql as $file)
			{
				//If there is already an array key with this name search for an open array key position.
				while ( isset($backups[$created]) )
				{
					$created = (int)$created + 1;
				}

				$fileName = explode('.sql', $file);
				$fileName = $fileName[0];
				$created = explode('-', $fileName);
				$created =  $created[count($created)-1];
				$backups[$created] = array(
					'name' => $fileName,
					'created' => $created,
					'sql' => $fileName . '.sql',
					'zip' => FALSE
				);
			}
		}		
	}
	
	krsort( $backups ); //Asort by DSC order.
	return $backups;
}


/**
*	Returnss the content of the browse container (container title and a table of all the known sql and zip backup files).
*	@param NULL
*	@return String The created table HTML.
**/
function show_backups()
{
	$backups = get_backups();
	ob_start();
?>
	<h2>Browse &amp; Export</h2>
	<table>
		<tr>
			<th>Name</th>
			<th>Created</th>
			<th>SQL</th>
			<th>Zip</th>
			<th>Delete</th>
		</tr>

<?php 
	if (!empty($backups)):
		foreach ($backups as $backup):
			$name = $backup['name'];
			$created = gmdate("Y-m-d H:i:s", $backup['created']);
			$delete = '<a class="fa fa-trash-o fa-2 delete" data-name="' . $name . '" href=""></a>';
			$sql = $backup['sql'];
			$zip = $backup['zip'];
			$fileTypes = '';

			if ($sql) $sql = '<a class="fa fa-file-code-o fa-2" href="backups/sql/' . $sql . '"></a>';
			if ($zip) $zip = '<a class="fa fa-file-archive-o fa-2" href="backups/zip/' . $zip . '"></a>';

			if ( $sql && !$zip ) $fileTypes = 'class="only-sql"';
			else if ( !$sql && $zip ) $fileTypes = 'class="only-zip"';
	?>
			<tr <?php echo $fileTypes; ?>>
				<td><?php echo $name; ?></td>
				<td><?php echo $created; ?></td>
				<td><?php echo $sql; ?></td>
				<td><?php echo $zip; ?></td>
				<td><?php echo $delete; ?></td>
			</tr>
<?php   endforeach; ?>
<?php endif; ?>
	</table>
<?php 
	echo ob_get_clean();
}
	
if ( isset($_POST['ajax']) ) show_backups();
?>
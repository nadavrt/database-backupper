<?php 
/*
  Software Name: Database Backupper
  Plugin URI: https://github.com/nadavrt/database-backupper
  Description: A database backup application for easy singular database maintanance, complete with a sleek GUI and security options.
  Version: 1
  Author: Nadav Rotchild
  Author URI: http://www.nadavr.com
  License: MIT license
  PHP 5 >= 5.2.0, PECL zip >= 1.1.0
*/

include 'header.php';
?>

<!DOCTYPE html>
<html>
<head>
	<title>Doctor</title>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
	<link href='http://fonts.googleapis.com/css?family=Ubuntu:400,700' rel='stylesheet' type='text/css'>
	<link rel="stylesheet" type="text/css" href="css/style.css">
	<?php add_theme_css($settings); ?>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js"></script>
	<script src="main.js" type="text/javascript"></script>
</head>

<body>
	<div class="wrapper">
		<section class="sidebar">
			<div class="box menu-box">
		        <h2 class="title">Database Backup</h2>
		        <ul>
		          <li>
		            <a class="menu-box-tab" data-tab="create"><span class="icon fa fa-plus-circle fa-2"></span>Create</a>
		          </li>
		          <li>
		            <a class="menu-box-tab" data-tab="browse"><span class="icon fa fa-database fa-2"></span>Browse &amp; Export</a>
		          </li>
		          <li>
		            <a class="menu-box-tab" data-tab="import"><span class="icon fa fa-arrow-circle-o-up"></span>Import</a>
		          </li>
		          <li>
		            <a class="menu-box-tab" data-tab="settings"><span class="icon fa fa-cog fa-2"></span>Settings</a>
		          </li>
		          <li>
		            <a class="menu-box-tab" data-tab="security"><sapn class="icon fa fa-exclamation-triangle fa-2"></sapn>Security</a>
		          </li>
		        </ul>
		    </div>

		    <div class="box sidebar-box" data-parent="browse">
		        <h2 class="title">Filter By</h2>
				<div id="filters" data-order="desc">
					<div class="checked" data-filter="desc">
						<span class="radio-button"></span>
						<label for="desc">Date DESC</label>
						<span class="selected-button"></span>
		      	 	</div>
					
					<div data-filter="asc">
						<span class="radio-button"></span>
						<label for="asc">Date ASC</label>
						<span class="selected-button"></span>
		      	 	</div>

		      	 	<div data-filter="sql">
						<span class="radio-button"></span>
						<label for="sql">Only SQL</label>
						<span class="selected-button"></span>
		      	 	</div>

		      	 	<div data-filter="zip">
						<span class="radio-button"></span>
						<label for="zip">Only ZIP</label>
						<span class="selected-button"></span>
		      	 	</div>
		        </div>
		    </div>
		</section>

		<!-- View Box (main content) -->
		<section class="tabs">
			<div class="box view-box" data-tab="welcome">
				
				<div id="welcome-message">
					<h2>Welcome</h2>
					<p>What would you like to accomplish today?</p>
				</div>

				<div id="create">
				  <h2>Create Backup</h2>
				  <div class="form-wrapper">
					  <p>Select backup format:</p>
					  <form id="create-backup-form">
				      	<div class="backup-type">
			      	 		<input id="sqlFile" name="sqlFile" type='checkbox' />
			      	 		<label for="sqlFile"></label>
			      	 		<span class="form_label">SQL</span>
			      	 	</div>
			 
			  		  	<div class="backup-type">
			  		   		<input id="zipFile" name="zipFile" type='checkbox' />
			  		   		<label for="zipFile"></label>
			        		<span class="form_label">Zip</span>
				      	</div>

						<input type="submit" class="btn <?php echo $settings['theme']; ?> backup-button" value="Go" disabled="disabled">
				      	<div class="clear"></div>
				      </form>
				   </div>

				   <div id="download-files" class="form-wrapper">
				   		<ul class="loader hidden">
					   		<li class="loader-part"></li>
					   		<li class="loader-part"></li>
					   		<li class="loader-part"></li>
					    </ul>
					   	<div class="loader-message">Creating Backup</div>
						<p class="hidden"></p>
				   		<a href="" class="sql hidden"><button class="btn <?php echo $settings['theme']; ?> backup-button">SQL<span class="fa fa-file-code-o fa-2"></span></button></a>
				   		<a href="" class="zip hidden"><button class="btn <?php echo $settings['theme']; ?> backup-button">Zip<span class="fa fa-file-archive-o fa-2"></span></button></a>
				   </div>
				</div><!-- End of Create -->

				<div id="browse">
					<?php show_backups(); ?>
				</div>

				<div id="import">
				  <h2>Import Backup</h2>
				  <div class="form-wrapper">
					  <p>You can import your database as an SQL or a zipped SQL file. Do note that all the existing database tables will be dropped before the new database data is imported.</p>
					  <p>The maximum upload size is: <?php echo (ini_get('upload_max_filesize') ); ?></p>
					  <form id="import-backup-form" action="" method="POST" enctype="multipart/form-data">
					    <div id="upload-file-button" class="fa fa-file fa-3"><span>Choose File<span></div>
					    <div id="file-name"></div>
					    <div class="clear"></div>
						<input type="file" name="import">
						<input type="submit" class="btn <?php echo $settings['theme']; ?> backup-button" disabled="disabled" value="Go">
				      </form> 
				   </div>

				   <div id="import-status" class="form-wrapper hidden">
				   		<ul class="loader">
					   		<li class="loader-part"></li>
					   		<li class="loader-part"></li>
					   		<li class="loader-part"></li>
					   		<div class="clear"></div>
					    </ul>
					    <div class="loader-message"><p>Importing data. This might take a while.</p></div>
						<p class="hidden"></p>
						<input type="submit" class="btn <?php echo $settings['theme']; ?> refresh-button hidden" value="Import Again">
				   </div>
				</div><!-- End of Import -->

				<div id="settings" class="<?php echo $settings['theme']; ?>">
					<h2>Settings</h2>
					<div class="column" style="width:30%">
						<p>Theme colors set</p>
						<div class="theme-block" data-theme="default">
							<div class="color-layer turquoise"></div>
							<div class="color-layer white"></div>
							<div class="color-layer navy-blue"></div>
						</div>

						<div class="theme-block" data-theme="light">
							<div class="color-layer brown"></div>
							<div class="color-layer black"></div>
							<div class="color-layer white"></div>
						</div>

						<div class="theme-block" data-theme="dark">
							<div class="color-layer dark-grey"></div>
							<div class="color-layer grey"></div>
							<div class="color-layer white"></div>
						</div>
					</div>

					<div class="column" style="width:70%;">
						<p>Quick DB credentials change <button id="locker" class="btn <?php echo $settings['theme']; ?> icon fa fa-lock fa-2"></button></p>
						<form id="settings-form" action="functions/change_settings.php" method="POST">
							<p>
								<label for="dbName">DB Name</label>
								<input type="text" id="dbName" name="credentials[DB_NAME]">
							</p>
							<p>
								<label for="dbUser">DB Username</label>
								<input type="text" id="dbUser" name="credentials[DB_USER]">
							</p>
							<p>
								<label for="dbPassword">DB Password</label>
								<input type="password" id="dbPassword" name="credentials[DB_PASSWORD]">
							</p>
						</form>
					</div>

					<div class="clear"></div>
					<button id="save-settings" class="btn <?php echo $settings['theme']; ?>">Save</button>
				</div><!-- End of Settings -->

				<div id="security" class="<?php echo $settings['theme']; ?>">
					<h2>Security</h2>
					<div id="htaccessCheck" class="<?php echo ($settings['htpasswd'])? 'checked':''; ?>">
						<span class="radio-button"></span>
						<label for="enableHtaccess">Enable .htacess password protection</label>
						<span class="selected-button"></span>
					</div>

					<form id="security-form" action="functions/change_settings.php" method="POST" autocomplete="off">
						<input type="hidden" id="enableHtaccess" name="enableHtaccess" value="<?php echo ($settings['htpasswd'])? 'true':'false'; ?>">
						<p class="credential-block" <?php echo ($settings['htpasswd'])?'':'style="display:none;"'; ?>>
							<label for="dbName">Username</label>
							<input type="text" name="htaccess[username]">
						</p>
						<p class="credential-block" <?php echo ($settings['htpasswd'])?'':'style="display:none;"'; ?>>
							<label for="dbUser">Password</label>
							<input type="password" name="htaccess[password]">
						</p>
						<input type="submit" class="btn <?php echo $settings['theme']; ?>" value="Save">
					</form>
				</div><!-- End of Security -->

			</div><!-- End of View Box -->

		</section>

		<!-- Info Tabs -->
		<section class="info">
			<div class="box info-box" data-parent="create">
				<h2 class="title"><div class="icon fa fa-bullhorn fa-2"></div>Backup file names</h2>
				<div class="notice">
					<p>Did you know? The names given to the SQL and zip files created by this program are comprised of the database name followed by a Unix time stamp.</p>
				</div>
					
			</div>

			<div class="box info-box" data-parent="import">
				<h2 class="title"><div class="icon fa fa-bullhorn fa-2"></div>Maximum upload size too small?</h2>
				<div class="notice">
					<p>If your SQL file size is above the maximum upload limit imposed by your server the upload process might fail. If you run into this issue consider using a zipped file instead, or ask your system admin to increase the maximum file upload size.</p>
				</div>
			</div>

			<div class="box info-box" data-parent="settings">
				<h2 class="title"><div class="icon fa fa-bullhorn fa-2"></div>Don't lose your credentials</h2>
				<div class="notice">
					<p>Changing the database credentials on the fly is a neet feature. However, take note that it is an irreversible step. Once new credentials are provided the old ones are discarded and cannot be restored.</p>
				</div>
			</div>

			<div class="box info-box" data-parent="security">
					<?php if ($settings['htpasswd']): ?>
						<h2 class="title"><div class="icon fa fa-bullhorn fa-2"></div>You are protected</h2>
						<div class="notice">
						<p>Password protection is currently enabled. Input new data to reset the credentials. Untick the protection box and save to remove the protection.</p>
					<?php else: ?>
						<h2 class="title"><div class="icon fa fa-bullhorn fa-2"></div>Beware of Lockdowns!</h2>
						<div class="notice">
						<p>If you decide to use the password protection feature double check your username and password entries and write them down. Failing to do so might lock you out of this page for good, so proceed with caution.</p>
					<?php endif; ?>
				</div>
			</div>
		</section>

	</div><!-- End of .wrapper -->
</body>

</html>
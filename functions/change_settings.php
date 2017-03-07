<?php
	if ( !isset($_POST) ) header('Location: '. dir(_FILE_) . '../index.php' ); 


	/**
	*	Create a cookie that will contain the theme colors the user chose in the settings tab.
	*	@param String $_POST['theme'] The name of the selected theme. Defaults to the 'default' theme.
	*	@return NULL
	**/
	function define_settings_cookie()
	{
		
		$settings = json_decode($_COOKIE['doctor'], TRUE);
		
		//Did the user change the theme?
		if ( isset($_POST['theme']) && ($settings['theme'] != $_POST['theme']) ) $settings = define_theme_colors($_POST['theme'], $settings);

		//Were the .htpasswd credentials changed?
		if (isset($_POST['enableHtaccess']))
		{
			if ( update_htacess_protection($_POST['htaccess']) ) 
			{
				if ($_POST['enableHtaccess'] == 'true') $settings['htpasswd'] = TRUE;
				else $settings['htpasswd'] = FALSE;
			}
		}
		
		$settings = json_encode($settings);
		setcookie( 'doctor', $settings, time()+(3600*24)*30, '/' );
	}


	/**
	*	Update the color settings based on the user-selected theme.
	*	@param String $theme The theme to be used.
	*	@param Array $settings The existing settings.
	*	@return Array $settings The updated settings.
	**/
	function define_theme_colors($theme, $settings)
	{
		switch ($theme) 
		{
			case 'light':
				$themeDefaults = array(
					'theme' => 'light',
					'body' => '#C5AC91',
					'text' => '#000',
					'menuHover' => 'rgba(252, 180, 103, 0.65)',
					'titles' => '#FCB467',
					'boxes' => '#fff',
					'shadows' => '#565659',
					'alertBoxes' => '#8A3324',
					'alertCircle' => '#4E231C',
				);
				break;

			case 'dark':
				$themeDefaults = array(
					'theme' => 'dark',
					'body' => '#fff',
					'text' => '#000',
					'menuHover' => 'rgba(176, 180, 195, 0.68)',
					'titles' => '#AFAFAF',
					'boxes' => '#F1F1F1',
					'shadows' => '#AFAFAF',
					'alertBoxes' => '#565659',
					'alertCircle' => '#252527',					
				);
				break;				
			
			default:
				$themeDefaults = array(
					'theme' => 'default',
					'body' => '#1F253D',
					'text' => '#fff',
					'menuHover' => '#50597b',
					'titles' => '#11a8ab',
					'boxes' => '#394264',
					'shadows' => '#074142',
					'alertBoxes' => '#1A4E95',
					'alertCircle' => '#0A3269',
				);
		}

		foreach ($themeDefaults as $key => $val){
			$settings[$key] = $val;
		}

		return $settings;
	}

	/** 
	*	APR1-MD5 encryption method (windows compatible). A helper function for update_htacess_protection.
	*	@param String $plainpasswd The password to encrypt.
	*	@return String The MD5 encrypted password.
	***/
	function crypt_apr1_md5($plainpasswd)
	{
	    $salt = substr(str_shuffle("abcdefghijklmnopqrstuvwxyz0123456789"), 0, 8);
	    $len = strlen($plainpasswd);
	    $text = $plainpasswd.'$apr1$'.$salt;
	    $bin = pack("H32", md5($plainpasswd.$salt.$plainpasswd));
	    for($i = $len; $i > 0; $i -= 16) { $text .= substr($bin, 0, min(16, $i)); }
	    for($i = $len; $i > 0; $i >>= 1) { $text .= ($i & 1) ? chr(0) : $plainpasswd{0}; }
	    $bin = pack("H32", md5($text));
	    for($i = 0; $i < 1000; $i++)
	    {
	        $new = ($i & 1) ? $plainpasswd : $bin;
	        if ($i % 3) $new .= $salt;
	        if ($i % 7) $new .= $plainpasswd;
	        $new .= ($i & 1) ? $bin : $plainpasswd;
	        $bin = pack("H32", md5($new));
	    }
	    $tmp = '';
	    for ($i = 0; $i < 5; $i++)
	    {
	        $k = $i + 6;
	        $j = $i + 12;
	        if ($j == 16) $j = 5;
	        $tmp = $bin[$i].$bin[$k].$bin[$j].$tmp;
	    }
	    $tmp = chr(0).chr(0).$bin[11].$tmp;
	    $tmp = strtr(strrev(substr(base64_encode($tmp), 2)),
	    "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/",
	    "./0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz");
	 
	    return "$"."apr1"."$".$salt."$".$tmp;
	}
 
	/**
	*	Create the .htaccess and .htpasswd files according to the credentials provided by the user.
	*	@param Array $creds The credentials provided by the user.
	*	@return Boolean TRUE if the function succeeded in creating the files. Otherwise FALSE.
	*
	**/
	function update_htacess_protection($credentials)
	{
		if ( $_POST['enableHtaccess'] == 'true' ) //Add/Update the .htaccess and .htpasswd files
		{
			if ( !isset($credentials['username']) || !isset($credentials['password']) ) return FALSE;

			$username = $credentials['username'];
			$password = crypt_apr1_md5($credentials['password']);

			$fileLocation = str_replace('\\', '/', dirname(__FILE__));
			$fileLocation = explode('functions', $fileLocation);
			$fileLocation = $fileLocation[0];
			
			//Create the .htpasswd file
			$content = $username . ':' . $password;
			$file = fopen('../.htpasswd', "w");
			if ($file === FALSE) return FALSE;
			fwrite($file, $content);
			fclose($file);

			// Create the .htaccess file
			$content = 'AuthName "Please login to proceed"
			AuthType Basic
			AuthUserFile ' . $fileLocation . '.htpasswd
			Require valid-user';
			$file = fopen('../.htaccess', "w");
			if ($file === FALSE) return FALSE;
			fwrite($file, $content);
			fclose($file);
		}
		else
		{
			//delete the .htaccess and .htpasswd files
			if ( file_exists('../.htpasswd') ) unlink('../.htpasswd');
			if ( file_exists('../.htaccess') ) unlink('../.htaccess');
		}

		return TRUE;
	}


	/**
	*	Change the DB credentials found in credentials.php
	*	@param Array $_POST['credentials'] The new database name, username and password.
	*	@return NULL
	**/
	function change_db_credentials()
	{
		if ( !isset($_POST['credentials']) ) return;
		$credentials = file_get_contents('../credentials.php');
		$permittedVariables = array('DB_NAME','DB_USER','DB_PASSWORD');
		foreach ($_POST['credentials'] as $credName => $credVal)
		{
			if ( in_array($credName, $permittedVariables) )
			{
				//Sanitize the value before adding it.
				$credVal = str_replace("'", '', $credVal);
				$credVal = str_replace("\"", '', $credVal);
				$credentials = replace_credential($credentials, $credName, $credVal);
			}
		}
		file_put_contents('../credentials.php', $credentials);
	}

	/**
	*	Replace the value of a variable that was defined using the "define" function. A helper function used by change_db_credentials.
	*	@param String $credentials A string containing both the credential name followed by it's value.
	*	@param String $credName The name of the credential to search for.
	*	@return String $newValue The new value for said credential.
	**/
	function replace_credential($credentials, $credName, $newValue)
	{
		$newCredentials = '';
		$tempCredString = explode("'$credName'", $credentials);
		$newCredentials = $tempCredString[0] . "'$credName', '$newValue');";
		$tempCredString = explode(";", $tempCredString[1]);
		unset($tempCredString[0]);
		$tempCredString = implode(';', $tempCredString);
		$newCredentials = $newCredentials . $tempCredString;
		return $newCredentials;
	}

	/**
	*	Replace the value of a variable that was defined using the "define" function. A helper function used by change_db_credentials.
	*	@param String $credentials A string containing both the credential name followed by it's value.
	*	@param String $credName The name of the credential to search for.
	*	@return String $newValue The new value for said credential.
	**/
	function old_replace_credential($credentials, $credName, $newValue)
	{
		$newCredentials = '';
		$tempCredString = explode("'$credName', '", $credentials);
		$newCredentials = $tempCredString[0] . "'$credName', '";
		$tempCredString = explode("'", $tempCredString[1]);
		$tempCredString[0] = $newValue;
		$tempCredString = implode("'", $tempCredString);
		$newCredentials = $newCredentials . $tempCredString;
		return $newCredentials;
	}
	
	if ( isset($_POST['theme']) || isset($_POST['enableHtaccess']) ) define_settings_cookie();
	if ( isset($_POST['changeCredentials']) ) change_db_credentials();
	
	header('Location: '. dir(_FILE_) . '../index.php' );
?>
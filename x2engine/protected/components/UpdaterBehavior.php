<?php

/**
 * Behavior class with application updater utilities.
 */
class UpdaterBehavior extends CBehavior {

	/**
	 * Rebuilds the configuration file, i.e. during an update.
	 * 
	 * @param type $newversion If set, change the version to this value in the resulting config file
	 * @param type $newupdaterVersion If set, change the updater version to this value in the resulting config file
	 * @param type $newbuildDate If set, change the build date to this value in the resulting config file
	 */
	public function regenerateConfig($newversion=Null,$newupdaterVersion=Null,$newbuildDate=Null) {
		if(!file_exists('protected/config/X2Config.php')) {
			// App is using old config file. New one will be generated.
			include('protected/config/emailConfig.php');
			include('protected/config/dbConfig.php');
		} else {
			include('protected/config/X2Config.php');
		}
		
		if (!isset($appName)) {
			if(!empty(Yii::app()->name))
				$appName = Yii::app()->name;
			else
				$appName = "X2EngineCRM";
		}
		if (!isset($email)) {
			if(!empty(Yii::app()->params->admin->emailFromAddr))
				$email = Yii::app()->params->admin->emailFromAddr;
			else
				$email = 'contact@'.$_SERVER['SERVER_NAME'];
		}
		if (!isset($language)) {
			if(!empty(Yii::app()->language))
				$language = Yii::app()->language;
			else
				$language = 'en';
		}
		
		$config = "<?php\n";
		if (!isset($buildDate))
			$buildDate = time();
		if (!isset($updaterVersion))
			$updaterVersion = '';

		foreach(array('version','updaterVersion','buildDate') as $var)
			if(!empty(${'new'.$var}))
				${$var} = ${'new'.$var};
		
		foreach (array('appName', 'email', 'language', 'host', 'user', 'pass', 'dbname', 'version', 'updaterVersion') as $var)
			$config .= "\$$var='" . ${$var} . "';\n";
		$config .= "\$buildDate = $buildDate;\n";
		file_put_contents('protected/config/X2Config.php', $config);
	}
	
	public function respond($message,$error=false,$console=false,$fatal=false) {
		if(!$console)
			header("Content-type: application/json");
		$response = array();
		$response['message'] = $message;
		$response['error'] = $error;
		if($console) {
			echo $message;
		} else {
			echo CJSON::encode($response);
		}
		if($error && $fatal)
			Yii::app()->end();

	}
	
    /**
     * Wrapper for {@link FileUtil::ccopy}
     * 
     * Recursively copyies a directory if the specified.
     * @param string $file The starting point, whether file or directory.
     */
    protected function copyFile($file) {
        if (file_exists($file)) {
            if (is_dir($file)) {
                $objects = scandir($file);
                foreach ($objects as $object) {
                    if ($object != "." && $object != "..") {
                        $this->copyFile($file . "/" . $object);
                    }
                }
            } else {
                FileUtil::ccopy("$file", substr($file, 5));
            }
        }
    }
}

?>

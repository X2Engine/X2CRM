<?php

/*****************************************************************************************
 * X2CRM Open Source Edition is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2013 X2Engine Inc.
 * 
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License version 3 as published by the
 * Free Software Foundation with the addition of the following permission added
 * to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED WORK
 * IN WHICH THE COPYRIGHT IS OWNED BY X2ENGINE, X2ENGINE DISCLAIMS THE WARRANTY
 * OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
 * details.
 * 
 * You should have received a copy of the GNU Affero General Public License along with
 * this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 * 
 * You can contact X2Engine, Inc. P.O. Box 66752, Scotts Valley,
 * California 95067, USA. or at email address contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2Engine".
 *****************************************************************************************/

/**
 * Behavior class with application updater utilities.
 */
class UpdaterBehavior extends CBehavior {

	/**
	 * Rebuilds the configuration file.
	 * 
	 * @param type $newversion If set, change the version to this value in the resulting config file
	 * @param type $newupdaterVersion If set, change the updater version to this value in the resulting config file
	 * @param type $newbuildDate If set, change the build date to this value in the resulting config file
	 */
	public function regenerateConfig($newversion=Null,$newupdaterVersion=Null,$newbuildDate=null) {
		$newbuildDate = $newbuildDate==null?time():$newbuildDate;
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
			$buildDate = $newbuildDate;
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
     * Copies a file out of the temporary folder and into the live installation.
	 * 
	 * Wrapper for {@link FileUtil::ccopy} for updates.
	 * 
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
                FileUtil::ccopy($file, substr($file, 5));
            }
        }
    }
}

?>

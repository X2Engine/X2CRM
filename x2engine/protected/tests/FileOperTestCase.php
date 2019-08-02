<?php

/***********************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2 Engine, Inc. Copyright (C) 2011-2019 X2 Engine Inc.
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
 * You can contact X2Engine, Inc. P.O. Box 610121, Redwood City,
 * California 94061, USA. or at email address contact@x2engine.com.
 * 
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 * 
 * In accordance with Section 7(b) of the GNU Affero General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "Powered by
 * X2 Engine" logo. If the display of the logo is not reasonably feasible for
 * technical reasons, the Appropriate Legal Notices must display the words
 * "Powered by X2 Engine".
 **********************************************************************************/




Yii::import('application.components.util.*');

/**
 * Test case for tests that involve manipulating or getting info about file system objects.
 *
 * @property string $baseDir (read-only) path to the base of the testing files area
 * @property array $fileList (read-only) List of paths to test files, relative to webroot
 * @property string $relBaseDir (read-only) relative path to web root of the testing files area
 * @property array $relFileList (read-only) List of testing files relative to the base of the testing directory
 * @property string $testTime (read-only) Timestamp of the test currently being run (used to construct test paths)
 * @package application.tests
 * @author Demitri Morgan <demitri@x2engine.com>
 */
abstract class FileOperTestCase extends X2TestCase {

	/**
	 * Testing data output directory
	 * @var type 
	 */
	private $_baseDir;
	private $_fileList = array();
	private $_relBaseDir;
	private $_relFileList = array();
	private $_testTime;
	protected $subDirs = array('subdir1', 'subdir2');
	protected $exclSubDirs = array('exclDir1', 'exclDir2');
	protected $files = array('subdir1/testFile', 'subdir2/testFile');
	protected $exclFiles = array('exclDir1/testFile', 'exclDir2/exclFile');

	/**
	 * Magic getter for special properties
	 * @param type $name 
	 */
	public function __get($name) {
		$getter = 'get' . ucfirst($name);
		if (method_exists($this, $getter))
			return $this->$getter();
		else
			return $this->$name;
	}

	/**
	 * Magic setter for special properties
	 * @param string $name Property name
	 * @param mixed $value Property value 
	 */
	public function __set($name, $value) {
		$setter = 'set' . ucfirst($name);
		if (method_exists($this, $setter))
			return $this->$setter($value);
		else
			return $this->$name = $value;
	}

	public function getBaseDir() {
		if (!isset($this->_baseDir)) {
			$this->_baseDir = realpath(Yii::app()->basePath . '/../') . "/{$this->relBaseDir}";
		} else if (!is_int(strpos($this->_baseDir, $this->relBaseDir))) {
			$this->_baseDir = realpath(Yii::app()->basePath . '/../') . "/{$this->relBaseDir}";
		}
		return $this->_baseDir;
	}

	public function getRelFileList() {
		if (empty($this->_relFileList)) {
			$this->_relFileList = array();
			foreach (array_merge($this->files, $this->exclFiles) as $relPath)
				$this->_relFileList[] = "$relPath";
		}
		return $this->_relFileList;
	}
	
	public function getFileList() {
		if (empty($this->_fileList)) {
			$this->_fileList = array();
			foreach ($this->relFileList as $relPath)
				$this->_fileList[] = "{$this->relBaseDir}/$relPath";
		}
		return $this->_fileList;
	}

	public function getRelBaseDir() {
		if (empty($this->_relBaseDir)) {
			$this->_relBaseDir = "protected/tests/data/output/test-{$this->testTime}";
		} else if (!is_int(strpos($this->_relBaseDir, (string) $this->testTime))) {
			$this->_relBaseDir = "protected/tests/data/output/test-{$this->testTime}";
		}
		return $this->_relBaseDir;
	}

	public function getTestTime() {
		if (empty($this->_testTime))
			$this->_testTime = time();
		return $this->_testTime;
	}

	/**
	 * Construct test files and directories. 
	 */
	public function setupTestDirs() {
		if (!is_dir($this->baseDir))
			mkdir($this->baseDir);

		foreach (array_merge($this->subDirs, $this->exclSubDirs) as $dir)
			if (!is_dir($this->baseDir . DIRECTORY_SEPARATOR . $dir))
				mkdir($this->baseDir . DIRECTORY_SEPARATOR . $dir);
		foreach (array_merge($this->files, $this->exclFiles) as $file)
			if (!file_exists($this->baseDir . FileUtil::rpath("/$file")))
				file_put_contents($this->baseDir . FileUtil::rpath("/$file"), 'test file');
	}

	/**
	 * Remove all the test files and directories.
	 * @param bool $emptyFileList Whether to reset the properties that reference the file set
	 */
	public function removeTestDirs($emptyFileList = true) {
		foreach (array_merge($this->files, $this->exclFiles) as $file) {
			$path = $this->baseDir . FileUtil::rpath("/$file");
			if (file_exists($path))
				unlink($path);
		}
		foreach (array_merge($this->subDirs, $this->exclSubDirs) as $dir) {
			$path = $this->baseDir . FileUtil::rpath("/$dir");
			if (file_exists($path))
				rmdir($path);
		}
		if (is_dir($this->baseDir)) {
			rmdir($this->baseDir);
		}
		if ($emptyFileList) { // Reset everything
			$this->resetTestDirs();
		}
	}

	/**
	 * Empties the test fileset (but does nothing to the actual files)
	 */
	public function resetTestDirs() {
		$this->_baseDir = null;
		$this->_fileList = array();
		$this->_relBaseDir = null;
		$this->_relFileList = array();
		$this->_testTime = null;
	}

    public function tearDown() {
        parent::tearDown();
        $this->removeTestDirs();
    }

    /**
     * FTP connection wrapper for tests
     * @param String $test the test method to run
     */
    public function useFtp($test){
        if (X2_FTP_FILEOPER) {
            // Change to the tests directory so that relative paths work in testing.
            FileUtil::ftpInit(X2_FTP_HOST, X2_FTP_USER, X2_FTP_PASS, Yii::app()->basePath.DIRECTORY_SEPARATOR.'tests', X2_FTP_CHROOT_DIR);
            $this->$test();
            FileUtil::ftpClose();
        } else
            $this->markTestSkipped('Skipping: X2_FTP_FILEOPER is disabled.');
    }

    public function useSsh($test) {
        if (X2_SCP_FILEOPER) {
            // Change to the tests directory so that relative paths work in testing.
            FileUtil::sshInit(X2_SCP_HOST, X2_SCP_USER, X2_SCP_PASS);
            $this->$test();
            FileUtil::sshClose();
        } else
            $this->markTestSkipped('Skipping: X2_SCP_FILEOPER is disabled.');
    }
}

?>

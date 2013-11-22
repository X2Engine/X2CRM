<?php

/*********************************************************************************
 * Copyright (C) 2011-2013 X2Engine Inc. All Rights Reserved.
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95067 USA
 * 
 * Company website: http://www.x2engine.com 
 * Community and support website: http://www.x2community.com 
 * 
 * X2Engine Inc. grants you a perpetual, non-exclusive, non-transferable license 
 * to install and use this Software for your internal business purposes.  
 * You shall not modify, distribute, license or sublicense the Software.
 * Title, ownership, and all intellectual property rights in the Software belong 
 * exclusively to X2Engine.
 * 
 * THIS SOFTWARE IS PROVIDED "AS IS" AND WITHOUT WARRANTIES OF ANY KIND, EITHER 
 * EXPRESS OR IMPLIED, INCLUDING WITHOUT LIMITATION THE IMPLIED WARRANTIES OF 
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, TITLE, AND NON-INFRINGEMENT.
 ********************************************************************************/

Yii::import('application.components.util.*');

/**
 * Test case for tests that involve manipulating or getting info about file system objects.
 *
 * @property string $baseDir (read-only) path to the base of the testing files area
 * @property array $fileList (read-only) List of paths to test files, relative to webroot
 * @property string $relBaseDir (read-only) relative path to web root of the testing files area
 * @property array $relFileList (read-only) List of testing files relative to the base of the testing directory
 * @property string $testTime (read-only) Timestamp of the test currently being run (used to construct test paths)
 * @package X2CRM.tests
 * @author Demitri Morgan <demitri@x2engine.com>
 */
abstract class FileOperTestCase extends CTestCase {

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

    protected function tearDown() {
        parent::tearDown();
        $this->removeTestDirs();
    }

}

?>

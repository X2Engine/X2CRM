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

Yii::import('application.components.FileUtil');

/**
 * @package X2CRM.tests.unit.components 
 */
class FileUtilTest extends CTestCase {

	public $baseDir;
	public $subDirs = array('subdir1', 'subdir2');
	public $exclSubDirs = array('exclDir1', 'exclDir2');
	public $files = array('subdir1/testFile', 'subdir2/testFile');
	public $exclFiles = array('exclDir1/testFile', 'exclDir2/exclFile');

	public function setupTestDirs() {
		$time = time();
		$this->baseDir = FileUtil::rpath(Yii::app()->basePath . "/tests/data/output/test-$time");
		if(!is_dir($this->baseDir))
			mkdir($this->baseDir);

		foreach (array_merge($this->subDirs, $this->exclSubDirs) as $dir)
			if(!is_dir($this->baseDir . DIRECTORY_SEPARATOR . $dir))
				mkdir($this->baseDir . DIRECTORY_SEPARATOR . $dir);
		foreach (array_merge($this->files, $this->exclFiles) as $file)
			if(!file_exists($this->baseDir . FileUtil::rpath("/$file")))
				file_put_contents($this->baseDir . FileUtil::rpath("/$file"), 'test file');
	}
	
	public function removeTestDirs() {
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
		if (file_exists($this->baseDir)) {
			rmdir($this->baseDir);
		}
	}

	/**
	 * Expected behavior: if an exclude pattern is specified, and a subdirectory contains
	 * an excluded file, that subdirectory will be preserved. 
	 */
	public function testRrmDirWithPat() {
		$this->setupTestDirs();
		FileUtil::rrmdir($this->baseDir, '/(exclFile$|exclDir1$)/');
		foreach (array_merge($this->files, $this->subDirs) as $path)
			$this->assertFileNotExists($this->baseDir . FileUtil::rpath("/$path"));
		foreach (array_merge($this->exclFiles, $this->exclSubDirs) as $path)
			$this->assertFileExists($this->baseDir . FileUtil::rpath("/$path"));
		$this->removeTestDirs();
	}

	public function testRrmDirWithoutPat() {
		$this->setupTestDirs();
		FileUtil::rrmdir($this->baseDir);
		$this->assertFileNotExists($this->baseDir);
		$this->removeTestDirs();
	}
	
	/**
	 * Make sure it can properly create subdirectories
	 */
	public function testCcopy() {
		$this->setupTestDirs();
		$target = $this->baseDir.FileUtil::rpath("/subdir1/testFile");
		$destPath = 'sub/directory/structure';
		$dest = $this->baseDir.FileUtil::rpath("/subdir2/$destPath/testFile");
		FileUtil::ccopy($target,$dest);
		$this->assertFileExists($dest);
		unlink($dest);
		$destPath = explode('/',$destPath);
		while(!empty($destPath)) {
			rmdir($this->baseDir.FileUtil::rpath('/subdir2/'.implode('/',$destPath)));
			array_pop($destPath);
		}
		$this->removeTestDirs();
	}
	
	public function testFailPathRmDir() {
		$this->setupTestDirs();
		FileUtil::rrmdir(FileUtil::rpath($this->baseDir.'/subdir1/.'));
		$this->assertFileNotExists(FileUtil::rpath($this->baseDir.'/subdir1/testFile'));
		$this->removeTestDirs();
	}
	
	public function testFormatSize() {
		$this->assertEquals('10 KB',FileUtil::formatSize(1024*10));
	}
	
	public function testGetContents() {
		// With both methods:
		$this->assertTrue((bool) FileUtil::getContents('http://google.com'));
		FileUtil::$alwaysCurl = true;
		$this->assertTrue((bool) FileUtil::getContents('http://google.com'));
		FileUtil::$alwaysCurl = false;
	}
	
	/**
	 * Test copying the requirements checker script.
	 * 
	 * NOTE: the requirements checker (which gets deleted) will need to be copied back first.
	 */
	public function testRemoteCopy() {
		// 
		$outdir = Yii::app()->basePath . "/tests/data/output";
		$copy = "$outdir/requirements-copy.php";
		$curl = "$outdir/requirements-curl.php";
		$live = Yii::app()->basePath . "/../requirements.php";
		FileUtil::ccopy('http://x2planet.com/installs/requirements.php',$copy);
		FileUtil::$alwaysCurl = true;
		FileUtil::ccopy('http://x2planet.com/installs/requirements.php',$curl);		
		FileUtil::$alwaysCurl = false;
		// Test that the files are identical:
		$this->assertEquals(file_get_contents($copy),file_get_contents($curl));
		// Test that the first 4 bytes of the file are identical to the stored file:
		$afh = fopen($live,'rb');
		$cfh = fopen($copy,'rb');
		$ufh = fopen($curl,'rb');
		$aread = fread($afh,4);
		$this->assertEquals($aread,fread($cfh,4));
		$this->assertEquals($aread,fread($ufh,4));
		fclose($afh);
		fclose($cfh);
		fclose($ufh);
		unlink($copy);
		unlink($curl);
	}

}

?>

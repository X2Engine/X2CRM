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
 * Test case for {@link FileUtil}
 * 
 * @author Demitri Morgan <demitri@x2engine.com>
 * @package X2CRM.tests.unit.components 
 */
class FileUtilTest extends FileOperTestCase {

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
		$outdir = Yii::app()->basePath . "/tests/data/output";
		$copy = "$outdir/index-copy.php";
		$curl = "$outdir/index-curl.php";
		$file = 'index.php';
		$live = Yii::app()->basePath . "/../$file";
		FileUtil::ccopy("http://x2planet.com/updates/x2engine/$file",$copy);
		FileUtil::$alwaysCurl = true;
		FileUtil::ccopy("http://x2planet.com/updates/x2engine/$file",$curl);
		FileUtil::$alwaysCurl = false;
		// Test that the files are identical:
		$this->assertEquals(file_get_contents($copy),file_get_contents($curl));
		// Test that the first 4 bytes of the file are identical to the locally-stored file:
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
	
	public function testRelpath() {
		// Specifying both paths
		$startPoint = Yii::app()->basePath.'/config/main.php';
		$file = Yii::app()->basePath.'/../framework/YiiBase.php';
		$relpath = FileUtil::relpath($file,$startPoint);
		$this->assertEquals('../../framework/YiiBase.php',$relpath);
		// Specifying only one path. The return value should originate from 
		// index.php's directory!
		$relpath = FileUtil::relpath($file);
		$this->assertEquals('../../framework/YiiBase.php',$relpath);
		// Test on Windows!
		$startPoint = 'C:\\Program Files (x86)\\Something\\SomethingElse\\..\\something.exe';
		$endPoint = 'C:\\Windows\\Something\\..\\Something\\SomethingMore/library.dll';
		$relpath = FileUtil::relpath($endPoint,$startPoint);
		$this->assertEquals('../../Windows/Something/SomethingMore/library.dll',$relpath);
		// Two ordinary points that don't require upward traversal...
		$startPoint = '/home/joeschmoe/public_html/';
		$endPoint = '/home/joeschmoe/public_html/protected/controllers/FatController.php';
		$relpath = FileUtil::relpath($endPoint,$startPoint);
		$this->assertEquals('protected/controllers/FatController.php',$relpath);
		// Two points, one in a backup dir
		$startPoint = '/home/joeschmoe/public_html/protected/controllers/FatController.php';
		$endPoint   = '/home/joeschmoe/public_html/backup/protected/controllers/FatController.php';
		$relpath = FileUtil::relpath($endPoint,$startPoint);
		$this->assertEquals('../../backup/protected/controllers/FatController.php',$relpath);
		
	}
}

?>

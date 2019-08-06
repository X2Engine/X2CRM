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




Yii::import('application.components.util.CommandUtil');

/**
 * Command line utility tests. Currently only compatible with POSIX-compliant
 * operating systems.
 * 
 * @package application.tests.unit.components.util
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class CommandUtilTest extends FileOperTestCase {

	const OS = 'posix';

	const TESTCRON = 1;
	
	public function testRun() {
		$user = trim(exec('whoami'));
		$u = new CommandUtil();
		$this->assertEquals($user,trim($u->run('whoami')->output()));
	}

	public function testCmdExists() {
		$u = new CommandUtil();
		$iDontExist = 'supercalifragilisticexpialidocious';
		if(self::OS == 'posix')
			$this->assertTrue($u->cmdExists('ls'));
		else if(self::OS == 'dos')
			$this->assertTrue($u->cmdExists('dir'));
		$this->assertFalse($u->cmdExists($iDontExist));
	}

	/**
	 * Tests piping and filtered piping. Can only be run on Linux/Unix.
	 */
	public function testPipeFiltered() {
		$this->setupTestDirs();
		$u = new CommandUtil();
		$output = $u->run("find $this->baseDir -type f")->pipeFilteredTo('/excl/','echo')->output();
		$filesListed = array_filter(explode("\n",$output));
		$expectedFilesListed = array();
		foreach($this->exclFiles as $relPath){
			$expedtedFilesListed[] = $this->baseDir.'/'.$relPath;
		}
		$this->assertEquals($expectedFilesListed,$filesListed);
		$this->removeTestDirs();
	}

}

?>

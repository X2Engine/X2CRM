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

Yii::import('application.components.util.CommandUtil');

/**
 * Command line utility tests. Currently only compatible with POSIX-compliant
 * operating systems.
 * 
 * @package X2CRM.tests.unit.components.util
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

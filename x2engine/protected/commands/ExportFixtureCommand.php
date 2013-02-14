<?php

/* * *******************************************************************************
 * The X2CRM by X2Engine Inc. is free software. It is released under the terms of 
 * the following BSD License.
 * http://www.opensource.org/licenses/BSD-3-Clause
 * 
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95066 USA
 * 
 * Company website: http://www.x2engine.com 
 * Community and support website: http://www.x2community.com 
 * 
 * Copyright (C) 2011-2012 by X2Engine Inc. www.X2Engine.com
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without modification, 
 * are permitted provided that the following conditions are met:
 * 
 * - Redistributions of source code must retain the above copyright notice, this 
 *   list of conditions and the following disclaimer.
 * - Redistributions in binary form must reproduce the above copyright notice, this 
 *   list of conditions and the following disclaimer in the documentation and/or 
 *   other materials provided with the distribution.
 * - Neither the name of X2Engine or X2CRM nor the names of its contributors may be 
 *   used to endorse or promote products derived from this software without 
 *   specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND 
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED 
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. 
 * IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, 
 * INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, 
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, 
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF 
 * LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE 
 * OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED 
 * OF THE POSSIBILITY OF SUCH DAMAGE.
 * ****************************************************************************** */

/**
 * Exports a table in the live database (or a range of records in it) to a fixture/init script
 *
 * @author Demitri Morgan <demitri@x2engine.com>
 * @package X2CRM.commands
 */
class ExportFixtureCommand extends CConsoleCommand {

	/**
	 * @var array Specification for the command line arguments.
	 * 
	 * Each entry takes this form:
	 * array([local var name],[command line description],[default value],[validation expression],[validation error message])
	 */
	public $args = array(
		0 => array('tableName', 'table name', null, '$pass = array_key_exists($arg_in,Yii::app()->db->schema->tables);', "Table doesn't exist"),
		1 => array('type', 'fixture (f) or init script (i)', 'f', '$pass = in_array($arg_in,array("i","f"));', 'Must be "i" or "f"'),
		2 => array('range', '"WHERE" clause', '1', '$pass=($arg_in != null);', 'cannot be null'),
		3 => array('columns', 'table columns to include', '*', '$pass=($arg_in != null);', 'cannot be null'),
		4 => array('writeCond', 'overwrite (o), rename existing (r)', 'r', '$pass=in_array($arg_in,array("o","r"));', 'Must be "o" or "r"'),
	);
	public $fixtureDir;

	public function errorMessage($spec, $arg) {
		return "\nInvalid value for {$spec[0]}: \"" . $arg . "\" " . ($spec[4] != null ? "({$spec[4]})" : '') . "\n";
	}

	public function formatRecord($data, $alias = null) {
		return (!in_array($alias, array(null, ''), true) ? ("'" . addslashes($alias) . "' => ") : '') . var_export($data, true) . ",\n";
	}

	public function validInput($arg_in, $validator) {
		$pass = false;
		eval($validator);
		return $pass;
	}

	/**
	 * Export the contents of a table in the live database as a fixture or init script.
	 * 
	 * Usage:
	 * <tt>./yiic exportfixture [table name] [f|i] [range] [columns] [o|r]</tt>
	 * 
	 * @param array $args 
	 */
	public function run($args) {
		$this->fixtureDir = Yii::app()->basePath . '/tests/fixtures';
		foreach ($this->args as $pos => $spec) {
			$valid = false;
			while (!$valid) {
				if (array_key_exists($pos, $args)) {
					${$spec[0]} = $args[$pos];
					$valid = $this->validInput($args[$pos], $spec[3]);
					if (!$valid) {
						echo $this->errorMessage($spec, ${$spec[4]});
						echo $this->getHelp();
						Yii::app()->end();
					}
				} else {
					${$spec[0]} = $this->prompt("{$spec[0]} ({$spec[1]})", $spec[2]);
					$valid = $this->validInput(${$spec[0]}, $spec[3]);
					if (!$valid)
						echo $this->errorMessage($spec, ${$spec[0]});
				}
			}
		}
		if (!$valid) {
			echo $this->getHelp();
			Yii::app()->end();
		}
		$fileName = $tableName . ($type == 'i' ? '.init' : '') . '.php';
		$filePath = $this->fixtureDir . '/' . $tableName . ($type == 'i' ? '.init' : '') . '.php';

		if (file_exists(FileUtil::rpath($filePath))) {
			if ($writeCond == 'r') {
				$i = 0;
				$backup = $filePath;
				while (file_exists(FileUtil::rpath($backup))) {
					$backup = "$filePath.$i";
					$i++;
				}
				$this->copyFiles(array("backup of existing: $fileName" => array('source' => $filePath, 'target' => $backup)));
			} else {
				echo "\nOverwriting existing file $fileName\n";
			}
		}

		$aliasPrompt = false;
		if ($type == 'f') {
			$aliasPrompt = $this->confirm('Prompt for row aliases?');
		}

		$records = Yii::app()->db->createCommand()->select($columns)->from($tableName)->where($range)->queryAll();
		$fileCont = "<?php\nreturn array(\n";
		$aliases = array();
		foreach ($records as $index => $record) {
			$alias = null;
			if ($type == 'f') {
				$alias = $index;
				if ($aliasPrompt) {
					var_dump($record);
					$alias = $this->prompt("Alias for this record (enter for \"$index\"):");
					if (empty($alias)) {
						$alias = $index;
					}
					while (in_array($alias, $aliases)) {
						$alias = $this->prompt("Alias in use already. Enter another:");
						if (empty($alias)) {
							$alias = $index;
						}
					}
					$aliases[] = $alias;
				} else {
					
				}
			}
			$fileCont .= $this->formatRecord($record, $alias);
		}
		$fileCont .= ");\n?>";

		file_put_contents($filePath, $fileCont);
		echo "\nExport complete.\n";
	}

	public function getHelp() {
		return "\n***Usage:***\n\tyiic exportfixture [tableName] [type (f|i)] [range] [columns] [writeCond (o|r)]\n\n";
	}

}

?>

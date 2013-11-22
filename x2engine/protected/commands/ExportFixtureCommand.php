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
 * Exports a table in the live database (or a range of records in it) to a fixture/init script
 *
 * @package X2CRM.commands
 * @author Demitri Morgan <demitri@x2engine.com>
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

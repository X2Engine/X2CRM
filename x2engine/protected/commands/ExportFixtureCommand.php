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
 * Exports a table in the live database (or a range of records in it) to a fixture/init script
 *
 * @package application.commands
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class ExportFixtureCommand extends CConsoleCommand {

    private $_mode; 

	/**
	 * @var array Specification for the command line arguments.
	 * 
	 * Each entry takes this form:
	 * array(
     *  [local var name],
     *  [command line description],
     *  [default value],
     *  [validation expression],
     *  [validation error message]
     * )
	 */
	public $args = array(
		0 => array(
            'tableName',
            'table name',
            null,
            '$pass = array_key_exists($arg_in, Yii::app()->db->schema->tables);',
            "Table doesn't exist"
        ),
		1 => array(
            'type',
            'fixture (f) or init script (i)',
            'f',
            '$pass = in_array($arg_in, array("i", "f"));',
            'Must be "i" or "f"'
        ),
		2 => array(
            'range',
            '"WHERE" clause',
            '1',
            '$pass=($arg_in != null);',
            'cannot be null'
        ),
		3 => array(
            'columns',
            'table columns to include',
            '*',
            '$pass=($arg_in != null);',
            'cannot be null'
        ),
		4 => array(
            'writeCond', 
            'overwrite (o), rename existing (r), output to stdout (s), output to file ([filename])',
            's',
            '$pass=true;',
            '',
            //'$pass=in_array($arg_in, array("o","r","s"));',
            //'Must be "o", "r", or "s"'
        ),
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
	 * <tt>./yiic exportfixture interactive [table name] [f|i] [range] [columns] [o|r]</tt>
	 * 
	 * @param array $args 
	 */
	public function actionInteractive($args) {
        $this->_mode = 'interactive';
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
        $this->actionExport ($tableName, $type, $range, $columns, $writeCond);
    }

    /**
     * Non-interactive fixture export with option to specify aliases as command line args
     */
    public function actionExport (
        $tableName, $type='f', $range=1, $columns='*', $writeCond='s', array $aliases=array ()) {

		$fileName = $tableName . ($type == 'i' ? '.init' : '') . '.php';
		$filePath = $this->fixtureDir . '/' . $tableName . ($type == 'i' ? '.init' : '') . '.php';

		if (file_exists(FileUtil::rpath($filePath))) {
            switch ($writeCond) {
                case 'r': 
                    $i = 0;
                    $backup = $filePath;
                    while (file_exists(FileUtil::rpath($backup))) {
                        $backup = "$filePath.$i";
                        $i++;
                    }
                    $this->copyFiles(
                        array(
                            "backup of existing: $fileName" => array(
                                'source' => $filePath, 
                                'target' => $backup
                            )
                        ));
                    break;
                case 'o': 
				    echo "\nOverwriting existing file $fileName\n";
                    break;
                case 's': 
                    break;
                default: // filename
				    echo "\nWriting to file $writeCond\n";
            }
		}

		$aliasPrompt = false;
		if ($type == 'f' && $this->_mode==='interactive') {
			$aliasPrompt = $this->confirm('Prompt for row aliases?');
		}

		$records = Yii::app()->db->createCommand()
            ->select($columns)
            ->from($tableName)
            ->where($range)
            ->queryAll();
		$fileCont = "<?php\nreturn array(\n";
		foreach ($records as $index => $record) {
			$alias = null;
			if ($type == 'f') {
                if (!$aliasPrompt && isset ($aliases[$index])) {
				    $alias = $aliases[$index];
                } else {
				    $alias = $index;
                }
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

        if (!in_array ($writeCond, array ('s', 'r', 'o'))) {
		    file_put_contents($writeCond, $fileCont);
        } elseif ($writeCond !== 's')  {
		    file_put_contents($filePath, $fileCont);
        } else {
            /**/print ($fileCont);
        }
		echo "\nExport complete.\n";
	}

//	public function getHelp() {
//		return "\n***Usage:***\n\tyiic exportfixture [tableName] [type (f|i)] [range] [columns] [writeCond (o|r|s)]\n\n";
//	}

}

?>

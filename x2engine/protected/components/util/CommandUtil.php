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




/**
 * Stand-alone class for running more advanced command line programs.
 *
 * Provides a wrapper/shortcut object for running commands using proc_open.
 * 
 * This should ideally permit chaining of programs via pipes. An example, on
 * Unix-like systems:
 *
 * $cmd->run('ps aux')->pipeTo('awk \'/foo/ {print $2}\'')->pipeTo('kill')->complete();
 *
 * The above would send SIGTERM to all processes matching "foo". However, this
 * is not yet possible due to a "bug" in PHP:
 * http://stackoverflow.com/questions/6014761/proper-shell-execution-in-php
 *
 * @package application.components.util
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class CommandUtil {

	const DEBUG = 0;

	/**
	 * Subprocess commands
	 * @var array
	 */
	public $cmd = array();

	/**
	 * Saves input given to each program, for debugging/examination purposes
	 * @var type
	 */
	public $inputs = array();

	/**
	 * The status of the last process that was completed.
	 */
	public $lastReturnCode;

	/**
	 * @var string Operating system; a keyword "posix" for posix-compliant
	 * 	operating systems like Linux/Unix, or "dos" for Windows-based systems.
	 */
	public $os;

	/**
	 * The stack of subprocesses inputs/outputs currently executing.
	 *
	 * Each value corresponds to an index in {@link processes}.
	 * @var array
	 */
	public $pipeline = array();

	public $procArrays = array(
		'cmd',
		'inputs',
		'pipeline',
		'processes',
	);

	/**
	 * All subprocess handles of the current program, 
	 * @var array
	 */
	public $processes = array();

	/**
	 * Gathers some information about the operating environment.
	 */
	public function __construct(){
		if(!function_exists('proc_open'))
			throw new Exception('The function "proc_open" does not exist or is not available on this system, so running command line programs will not work.');
		$this->os = substr(strtoupper(PHP_OS), 0, 3) == 'WIN' ? 'dos' : 'posix';
	}

	/**
	 * Closes a process and throws an exception if it exited with error code.
	 * @param type $ind
	 * @throws Exception
	 */
	public function close($ind){
		if(gettype($this->processes[$ind]) != 'resource')
			return;
        $this->debug('Closing process at index '.$ind);
		$err = stream_get_contents($this->pipeline[$ind][2]);
		if($code = proc_close($this->processes[$ind]) == -1 && self::DEBUG)
			throw new Exception("Command {$this->cmd[$ind]} exited with error status.".(empty($err) ? '' : " Error output was as follows: \n $err"));
        $this->debug('Closed process at index '.$ind);
		return $code;
	}

	/**
	 * Returns true or false based on whether the named command exists on the system.
	 * @param string $cmd Name of the command
	 * @return bool
	 */
	public function cmdExists($cmd){
		if($this->os == 'posix'){
			return trim($this->run("which $cmd")->output()) != null;
		}
	}

	/**
	 * Closes all processes and returns the return value of proc_close from the last one.
	 */
	public function complete(){
		$n_proc = $this->nProc();
		$code = 0;
		if($n_proc > 0){ // Close processes
			foreach($this->processes as $ind => $process){
				$codeTmp = $this->close($ind);
				if($ind == $n_proc - 1)
					$code = $codeTmp;
			}
			// Empty arrays
			foreach($this->procArrays as $array)
				$this->$array = array();
		}
		return $this->lastReturnCode = $code;
	}

    public function debug($msg,$lvl=1) {
        if(self::DEBUG >= $lvl) {
            echo "[debug] $msg\n";
        }
    }

	/**
	 * Returns the current number of subprocesses.
	 * @return integer
	 */
	public function nProc(){
		return count($this->processes);
	}

	/**
	 * Returns the output of the last command and closes/clears all processes.
	 * @return string
	 */
	public function output(){
		$n_proc = $this->nProc();
		if($n_proc > 0){
			$output = stream_get_contents($this->pipeline[$n_proc - 1][1]);
            $this->complete();
			return $output;
		} else
			return null;
	}
	
	/**
	 * Wrapper for {@link pipeTo}
	 * @param type $filter PCRE regular expression
	 * @param type $cmd
	 * @param type $cwd
	 * @return CommandUtil
	 */
	public function pipeFilteredTo($filter, $cmd, $cwd = null){
		return $this->pipeTo($cmd, $cwd, $filter);
	}

	/**
	 * Takes the output of the last comand and pipes it to a new command
	 * @param string $cmd
	 * @param string $cwd
	 * @param string $filter Optional regular expressions filter to restrict the input to only certain lines.
	 * @return CommandUtil
	 */
	public function pipeTo($cmd, $cwd = null, $filter = null){
		$n_proc = $this->nProc();
        $this->debug('pipeTo('.$cmd.'): $n_proc = '.$n_proc);
		if($n_proc == 0)
			throw new Exception('Cannot pipe to subprocess; no prior processes from which to pipe have been opened.');
		return $this->run($cmd, $n_proc - 1, $cwd, $filter);
	}

	/**
	 * Runs a command on the command line.
	 * @param string $cmd
	 * @param resource|string $input The input for the program.
	 * @param string $cwd The directory to work in while executing.
	 * @return CommandUtil
	 */
	public function run($cmd, $input = null, $cwd = null, $filter = null){
		$cwd = $cwd === null ? __DIR__ : $cwd;
		$descriptor = array(
			1 => array('pipe', 'w'),
			2 => array('pipe', 'w'),
		);
		// Read input
		$inputType = gettype($input);
		if($input !== null){
			if($inputType == 'resource'){
				// Interpret as a file descriptor.
				$inputText = stream_get_contents($input);
                $this->debug("Interpreted input as a stream resource, and read input:\n$inputText",2);
			}else if($inputType == 'string'){
				// Interpret as literal input
				$inputText = $input;
                $this->debug("Interpreted input as a string, and it is:\n$inputText",2);
			}else if($inputType == 'integer'){
				// Interpret as an index of a process whose output will be used as input
				$inputText = stream_get_contents($this->pipeline[$input][1]);
				$this->close($input);
			}
			$descriptor[0] = array('pipe', 'r');
		}

		// Spawn new process
		$procIndex = $this->nProc();
        $this->debug("Spawning $cmd and storing process handle at index $procIndex");
		$this->cmd[$procIndex] = $cmd;
		$this->processes[$procIndex] = proc_open($cmd, $descriptor, $this->pipeline[$procIndex], $cwd);
        $filter = empty($filter) ? '/.*/' : $filter;
        
		// Write input to process
		if(!empty($inputText)){ // Send input to the program
            $this->debug("Writing to input of child process $procIndex...");
			$this->inputs[$procIndex] = $inputText;
			foreach(explode("\n", $inputText) as $inputLine)
				if(preg_match($filter, $inputLine))
					fwrite($this->pipeline[$procIndex][0], $inputLine);
            $this->debug("...done.");
		}
		return $this;
	}

	//////////////////////////
	// Cron-related methods //
	//////////////////////////
	//
	// The following methods are used for creating scheduled commands. Typically
	// these will only work in Linux/Unix environments.

	/**
	 * Loads and returns the cron table. Performs environment check first.
	 * @return string
	 */
	public function loadCrontab() {
		// Check to see if everything is as it should be
		if(!$this->cmdExists('crontab'))
			throw new Exception('The "crontab" command does not exist on this system, so there is no way to set up cron jobs.',1);
		if($this->run('crontab -l')->complete() == -1)
			throw new Exception('There is a cron service available on this system, but PHP is running as a system user that does not have permission to use it.',2);
		// Get the existing crontab
		return $this->run('crontab -l')->output();
	}

	/**
	 * Saves the cron table.
	 *
	 * Save the table to a temporary file, sends it to cron, and deletes the
	 * temporary file.
	 *
	 * @param string $crontab The new contents of the updated cron table
	 */
	public function saveCrontab($crontab) {
		$tempFile = __DIR__.DIRECTORY_SEPARATOR.'crontab-'.time();
		file_put_contents($tempFile, $crontab);
		$status = $this->run("crontab $tempFile")->complete();
		unlink($tempFile);
	}
}

?>

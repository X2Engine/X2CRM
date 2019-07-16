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




Yii::import('application.commands.X2ConsoleCommand');

/**
 * Update/migrate custom code from the "custom" folder using Git.
 *
 * Notes:
 * (1) This command requires a Unix-like shell environment with rsync and git
 *     installed in it in order to run properly.
 * (2) The Git repository must be up-to-date and have tags corresponding to
 *     the versions updating to and from.
 * (3) This script will not work properly if the git repository is a clone of
 *     the public repository found on Github, and if using Professional Edition.
 *     Otherwise, if using Open Source Edition, this script should work with a
 *     clone of that repository (assuming the clone has all version tags).
 * (4) Since controller classes only extend their base-code analogues, and do
 *     not fully copy/replace them, they are ignored by this whole process.
 *     Updating them should just be a matter of updating only the methods that
 *     were overridden, if any, instead of the entire file.
 *
 * @property string $branch The name of the temporary branch that will be used
 *  for merging and updating custom code.
 * @property array $fileList List of custom files to be copied.
 * @property string $gitdir The directory of the git repository. If unspecified,
 *  it is assumed to be one level above the web root.
 * @property string $rsync Default rsync command to use for synchronizing files.
 * @property string $source The path to the custom folder. If unspecified, it is
 *  assumed that it is the custom folder inside the current installation.
 * @package application.commands
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class MigrateCustomCommand extends X2ConsoleCommand {

    const DEBUG = 0;

    const PERSIST_FILE = '.x2_git_migrate.json';

    /**
     * The version from which the custom code is being updated.
     * @var string
     */
    public $origin;

    /**
     * The version to which the custom code should be updated.
     * @var string
     */
    public $target;

    private $_branch;
    private $_fileList;
    private $_gitdir;
    private $_source;
    /**
     * Stores parameter names.
     * @var array
     */
    private $params = array();

    /**
     * Updates the custom code.
     *
     * If it can automatically merge, and if there are no merge conflicts, it
     * will copy the files back into the source folder.
     *
     * @param string $source Path to the custom code folder.
     * @param string $origin The version of the X2Engine installation at which it
     *  was customized. In other words, the version from which X2Engine is being
     *  updated.
     * @param string $target The target version to which the X2Engine
     *  customizations will be updated.
     * @param string $gitdir The directory
     * @param string $branch Name of branch to use for merging upstream changes
     *  into the custom code
     */
    public function actionUpdate($origin,$target,$source=null,$gitdir=null,$branch=null,$nocopy=0) {
        // Initialize
        $this->initParams(get_defined_vars());

        // Prompt the user if there's a branch name collision
        $delBranch = 'y';
        if($this->branchExists()) {
            $msg = "A branch named {$this->branch} will be created, but such a "
                   ."branch already exists in the Git repository. It will be "
                   ."deleted. Continue?";
            $msg = $this->formatter($msg)->bold()->color('red')->format();
            $delBranch = $this->prompt($msg,'y');
        }
        if(strtolower($delBranch)=='n') {
            return;
        }
        
        // Assume we're starting a new update, so clean everything up:
        $this->cleanUp();
        
        // Create the new branch at the start tag:
        $this->headerMsg("-- Creating a Git branch for the update at {$this->origin} --");
        $mkBranch = $this->git("branch {$this->branch} {$this->origin}");
        if($mkBranch != 0) {
            $this->end();
        }

        // Checkout:
        $this->headerMsg('-- Switching to the new branch --');
        $checkout = $this->git("checkout {$this->branch}");
        if($checkout != 0) {
            $this->end();
        }

        // Copy files into the repo:
        $this->copyForth();
        
        // Commit:
        $this->headerMsg('-- Committing changes --');
        $this->git("add ./");
        $commit = $this->git("commit -a -m 'Local custom changes as of {$this->origin}'");
        if($commit != 0) {
            $this->end();
        }

        // Update:
        $this->headerMsg("-- Merging upstream changes to version {$this->target} --");
        $update = $this->git("merge {$this->target}",false);
        if($update != 0) {
            $this->headerMsg("Automatic merge failed. Resolve conflicts, commit changes, and run \"migratecustom copy --source={$this->source}\"",'red');
            $this->end();
        }

        // Copy the merged files:
        $this->copyBack();

        // Done.
        $this->end(true);
    }

    /**
     * Copies the current source files onto analogues found in the git
     * directory.
     * @param string $source Path to the source (custom folder)
     *
     */
    public function actionCopy($origin=null,$target=null,$source=null,$gitdir=null,$branch=null) {
        if(!isset($source) || !isset($origin,$target) || !isset($branch)) {
            // Use the persist file to restore data, in case of having to
            // manually fix conflicts and merge, so that the process can be
            // resumed
            $this->restoreParams(get_defined_vars());
        }
        $this->copyBack();

        // Done.
        $this->end(true);
    }

    public function branchExists() {
        return (int) $this->git("show-branch {$this->branch}",false) == 0;
    }

    /**
     * Removes the persist file and deletes the temporary branch.
     */
    public function cleanUp() {
        // Delete persist file
        if(file_exists($persistFile = $this->source.DIRECTORY_SEPARATOR.self::PERSIST_FILE)){
            $this->headerMsg("-- Deleting the persist file --");
            unlink($persistFile);
        }
        // Delete branch if it exists
        if($this->branchExists()) {
            $this->headerMsg("-- Deleting temporary branch {$this->branch} --");
            $this->git('reset --hard HEAD');
            $this->git('checkout -q master');
            $this->git("branch -D {$this->branch}");
        }
    }

    /**
     * Copies the custom code from the git repository back into the original
     * folder, overwriting originals.
     */
    public function copyBack() {
        $this->headerMsg('-- Copying merged files from the Git repository back into the source --');
        $this->sys("{$this->rsync} --existing {$this->gitdir}/x2engine/ {$this->source}/");
    }

    public function copyForth(){
        $this->headerMsg('-- Copying customized files into the Git repository --');
        $this->sys("{$this->rsync} {$this->source}/ {$this->gitdir}/x2engine/");
    }

    /**
     * Displays debugging messages
     * @param type $msg
     */
    public function debug($msg) {
        if(self::DEBUG) { 
            echo $this->formatter('[debug] ')->color('blue')->bold()->format()."$msg\n";
        }
    }

    /**
     * 
     */
    public function end($cleanUp = false) {
        if($cleanUp){
            $this->cleanUp();
        }else{
            $this->saveParams();
        }
        Yii::app()->end();
    }

    /**
     * Opens a git subprocess in the git directory.
     * 
     * @param string $command Git command to run
     * @param bool $echo Whether to echo (true) or suppress (false) any output
     *  from the command.
     * @param bool $embolden Whether to embolden error output and turn it red.
     */
    public function git($command,$echo=true,$embolden = true){ // &$pipes,$descriptorSpec=array()) {
        return $this->sys("git $command",$this->gitdir,$echo,$embolden);
    }

    /**
     * Run a system command, echo its output.
     *
     * @param type $command
     * @param bool $echo Whether to echo (true) or suppress (false) any standard
     *  output from the command.
     * @param bool $embolden Whether to embolden error output and turn it red.
     * @return type
     */
    public function sys($command,$cwd=null,$echo=true,$embolden=true) {
        if($cwd == null) {
            $cwd = __DIR__;
        }
        $descriptorSpec = array(
            0 => array('pipe', 'r'), // stdin
            1 => array('pipe', 'w'), // stdout
            2 => array('pipe', 'w'), // stderr
        );
        $this->debug("Running: $command");
        $cmd = proc_open("$command", $descriptorSpec, $pipes, $cwd);
        $stdOut = stream_get_contents($pipes[1]);
        $stdErr = stream_get_contents($pipes[2]);
        $code = proc_close($cmd);
        $this->debug("Exit code for $command: $code\n");
        if($code != 0 && $echo) {
            if($embolden) {
                $this->headerMsg($stdErr,'red',false);
            } else {
                echo $stdErr;
            }
        } elseif($echo) {
            echo $stdOut;
        }
        return $code;
    }

    /**
     * Gets the default source path, which is guaranteed to exist more or less
     * @return string
     */
    public function getDefaultSource() {
        return realpath(implode(DIRECTORY_SEPARATOR,array(Yii::app()->basePath,'..','custom')));
    }

    /**
     * Getter for {@branch}
     * @return type
     */
    public function getBranch() {
        if(empty($this->_branch)) {
            $this->_branch = "custom_code_update_{$this->origin}-{$this->target}";
        }
        return $this->_branch;
    }

    /**
     * Getter for {@link fileList}
     * @return array
     */
    public function getFileList() {
        if(!isset($this->_fileList)) {
            $cmd =  new CommandUtil();
            $findCmd = "find {$this->source}/ -type f";
            $this->debug("Running find command: $findCmd");
            $output = $cmd->run($findCmd)->output();
            $this->debug("Output: $output");
            $output = explode("\n",$output);
            $this->_fileList = array();
            foreach($output as $line) {
                if(preg_match(':(?<path>protected.+\.php)$:',$line,$match)) {
                    $this->_fileList[] = $match['path'];
                    $this->debug("file in file list: ".$match['path']);
                } else {
                    $this->debug("line not part of file list: $line");
                }
            }
        }
        return $this->_fileList;
    }

    /**
     * Getter for {@link gitdir}
     * @return string
     */
    public function getGitdir() {

        if(empty($this->_gitdir)) {
            $this->_gitdir = realpath(implode(DIRECTORY_SEPARATOR,array(Yii::app()->basePath,'..','..')));
        }
        return $this->_gitdir;
    }

    /**
     * Getter for {@link rsync}
     * @return string
     */
    public function getRsync() {
        return 'rsync -ac --exclude="*~"';
    }

    /**
     * Getter for {@link source}
     * @return string
     */
    public function getSource() {
        if(empty($this->_source)) {
            $this->_source = $this->getDefaultSource();
        }
        return $this->_source;
    }

    /**
     * Sets properties initially
     * @param array $params
     */
    public function initParams($params) {
        foreach($params as $name=>$value) {
            if($this->canSetProperty($name) || property_exists($this, $name)) {
                if($name=='gitdir' || $name == 'source')
                    $value = rtrim($value,DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
                $this->params[$name] = $name;
                $this->$name = $value;
            }
        }
    }

    /**
     * Uses parameters saved to the persistence file during the current operation
     *
     * @param type $params Optional parameters to override old saved parameters.
     */
    public function restoreParams($params=array()) {
        $persistFile = $this->source.DIRECTORY_SEPARATOR.self::PERSIST_FILE;
        $savedParams = file_exists($persistFile) ? json_decode(file_get_contents($persistFile),1) : array();
        $savedParams = empty($savedParams) ? array() : $savedParams;
        foreach(array_keys($params) as $name) {
            if(!empty($savedParams[$name]) && empty($params[$name])) {
                $params[$name] = $savedParams[$name];
            }
        }
        $this->initParams($params);
        $this->headerMsg("-- Continuing with previous parameters --");
        foreach($this->params as $property) {
            echo $this->formatter($property)->color('green')->format().": {$this->$property}\n";
        }
    }

    /**
     * Saves parameters to the persistence file.
     */
    public function saveParams() {
        foreach($this->params as $property) {
            $params[$property] = $this->$property;
        }
        file_put_contents($this->source.DIRECTORY_SEPARATOR.self::PERSIST_FILE,json_encode($params));
    }

    /**
     * Setter for {@link branch}
     * @param string $value
     */
    public function setBranch($value){
        $this->_branch = $value;
    }

    /**
     * Setter for {@link gitdir}
     * @param string $value
     */
    public function setGitdir($value){
        $this->_gitdir = $this->validPath($value,'gitdir');
    }

    /**
     * Setter for {@link source}
     * @param type $value
     */
    public function setSource($value){
        $this->_source = $this->validPath($value,'source');
    }

    public function validPath($value,$name) {
        $path = realpath(str_replace('~','/home/'.get_current_user(),$value));
        if(!$path) {
            $this->headerMsg("Invalid path specified for $name: $value",'red');
            Yii::app()->end();
        }
        return $path;
    }
}

?>

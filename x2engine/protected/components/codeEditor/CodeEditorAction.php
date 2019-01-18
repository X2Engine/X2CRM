<?php

/***********************************************************************************
 * X2Engine Open Source Edition is a customer relationship management program developed by
 * X2 Engine, Inc. Copyright (C) 2011-2017 X2 Engine Inc.
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
 * @edition:ent
 *
 *
 * TODO:
 *  * Update linting to save and lint temp file
 *  * Update create/save functionality to use customization framework
 *  * Add locking to prevent two users modifying the same file
 *  * Fix cookie persistence: In jquery.treeview.js, prepareBranches returns no elements
 *    to serialize for storage.
 */

class CodeEditorAction extends CAction {

    /**
     * List of files blacklisted from use in the Code Editor
     * These have been excluded for safety reasons to ensure the
     * continued operation of the system in event of operator error,
     * and to prevent the leaking of sensitive information.
     */
    private static $blacklist = null;
    public static $blacklistedDirs = array(
        'assets',
        'custom',
        'framework',
        'protected/runtime/cache',
        'protected/runtime/cache2',
    );
    public static $blacklistedFiles = array(
        'constants.php',
        'index.php',
        'index-test.php',
        'protected/commands/UpdateCommand.php',
        'protected/components/codeEditor/CodeBrowserAction.php',
        'protected/components/codeEditor/CodeEditorAction.php',
        'protected/components/codeEditor/views/codeEditor.php',
        'protected/components/ResponseBehavior.php',
        'protected/components/UpdaterBehavior.php',
        'protected/components/util/EncryptUtil.php',
        'protected/components/util/FileUtil.php',
        'protected/components/util/ResponseUtil.php',
        'protected/components/views/requirements.php',
        'protected/config/encryption.iv',
        'protected/config/encryption.key',
        'protected/config/X2Config.php',
        'protected/data/install_timestamp',
        'protected/data/modelReservedWords.php',
        'protected/data/mysqlReservedWords.php',
        'protected/data/update_backup.sql',
        'protected/runtime/state.bin',
        'protected/views/admin/updater.php',
        'protected/yiic',
        'protected/yiic.bat',
        'protected/yiic.php',
    );

    public function run() {
        if (Yii::app()->request->isPostRequest) {
            $operation = filter_input(INPUT_POST, 'operation', FILTER_DEFAULT);
            $filename = filter_input(INPUT_POST, 'filename', FILTER_DEFAULT);
            $contents = filter_input(INPUT_POST, 'contents', FILTER_DEFAULT);
            if ($operation === 'load' && !empty($filename)) {
                echo $this->loadFile($filename);
            } else if ($operation === 'create' && !empty($filename)) {
                if (!$this->createFile($filename))
                    throw new CHttpException(400, Yii::t('admin', 'Failed to create file'));
            } else if ($operation === 'lint' && !empty($filename)) {
                $this->lintFile($filename);
            } else if ($operation === 'save' && !empty($filename) && !empty($contents)) {
                if (!$this->saveFile($filename, $contents))
                    throw new CHttpException(400, Yii::t('admin', 'Failed to save file'));
            } else {
                throw new CHttpException(400, Yii::t('admin', 'Invalid request, please check your parameters'));
            }
        } else {
            $this->controller->render('application.components.codeEditor.views.codeEditor', array(
            ));
        }
    }

    /**
     * Create file operation
     * @param string $file File to create
     */
    protected function createFile($file) {
        $file = dirname(Yii::app()->basePath) . '/' . $file;
        if (AppFileUtil::isWithinApplicationDirectory($file) && !file_exists($file)) {
            return touch($file);
        }
    }

    /**
     * Load file operation
     * @param string $file File to load
     */
    protected function loadFile($file) {
        if (AppFileUtil::isWithinApplicationDirectory($file) && is_file($file)) {
            if (static::isBlacklisted($file))
                throw new CHttpException(403, Yii::t('admin', 'Forbidden: File is blacklisted'));
            return file_get_contents($file);
        }
    }

    /**
     * Lint file operation
     * @param string $file File to lint
     */
    protected function lintFile($file) {
        if (AppFileUtil::isWithinApplicationDirectory($file) && is_file($file)) {
            $descriptor = array(
                0 => array('pipe', 'r'),
                1 => array('pipe', 'w'),
                2 => array('pipe', 'w'),
            );
            $quotedFile = escapeshellarg($file);
            $testProc = proc_open('php -l '.$quotedFile, $descriptor, $pipes);
            $output = stream_get_contents($pipes[2]);
            $ret = proc_close($testProc);

            if($ret !== 0) {
                throw new CHttpException(400, $output);
            }
            unset($pipes);
        } else {
            throw new CHttpException(400, Yii::t('admin', 'File not found'));
        }
    }

    /**
     * Save file operation
     * @param string $file File to lint
     * @param string $contents New contents of file
     */
    protected function saveFile($file, $contents) {
        if (AppFileUtil::isWithinApplicationDirectory($file) && is_file($file)) {
            return file_put_contents($file, $contents);
        }
    }

    public static function getBlacklist() {
        if (is_null(static::$blacklist)) {
            $blacklist = array();
            $basePath = dirname(Yii::app()->basePath);
            foreach (array_merge(static::$blacklistedFiles, static::$blacklistedDirs) as $file) {
                $blacklist[] = $basePath .'/'. $file;
            }
            static::$blacklist = $blacklist;
        }
        return static::$blacklist;
    }

    public static function isBlacklisted($file) {
        $blacklistedFile = in_array($file, static::getBlacklist());
        $inBlacklistedDir = false;
        $appBase = dirname(Yii::app()->basePath);
        foreach (static::$blacklistedDirs as $dir) {
            // Ensure requested file is not within a blacklisted directory
            if (0 === strpos($file, $appBase.'/'.$dir)) {
                $inBlacklistedDir = true;
                break;
            }
        }
        return $blacklistedFile || $inBlacklistedDir;
    }
}

?>

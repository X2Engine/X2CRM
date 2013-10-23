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

/**
 * Standalone file manipulation class.
 *
 * Miscellaneous file system utilities. It is not a child class of CComponent or
 * the like in order to be portable/stand-alone (so it can be used outside the
 * app, i.e. by the installer).
 *
 * @package X2CRM.components.util
 * @author Demitri Morgan <demitri@x2engine.com>
 */
class FileUtil {

    public static $_finfo;

    public static $alwaysCurl = false;

    /**
     * Copies a file or directory recursively.
     *
     * If the local filesystem directory to where the file will be copied does
     * not exist yet, it will be created automatically. Furthermore, if a remote
     * URL is being accessed and allow_url_fopen isn't set, it will attempt to
     * use CURL instead.
     *
     * @param string $source The source file.
     * @param string $target The destination path.
     * @param boolean $relTarget Transform the target to a relative path. This
     *  option must be false unless the target is an absolute path.
     * @param boolean $contents If true, and the source is a directory, its
     *  contents will be copied to the destination; otherwise, the whole directory
     *  will be copied into the destination directory.
     * @return boolean
     */
    public static function ccopy($source, $target, $relTarget = false, $contents = true){
        $ds = DIRECTORY_SEPARATOR;
        // Normalize target to the form where if it's a directory it doesn't have
        // a trailing slash, and is platform-agnostic:
        $target = rtrim(self::rpath($target), $ds);
        // Make the target into a relative path:
        if($relTarget)
            $target = self::relpath($target);
        // Safeguard against overwriting files:
        if(is_dir($source) && !is_dir($target) && is_file($target))
            throw new Exception("Cannot copy a directory ($source) into a file ($target).");

        // Create parent directories if they don't exist already.
        // 
        // If a file is being copied: the path to examine for directory creation
        //  is one lower than the target (the bottom-level parent).
        // If a directory is being copied: the same is true (to not create the
        //  top level node) even though it's a directory, because the target
        //  directory will be created anyway if necessary
        // If a directory is being copied and $contents is false: it's assumed
        //  that the target is a destination directory and not part of the tree
        //  to be copied. Thus, 
        $pathNodes = explode($ds, $target);
        if($contents)
            array_pop($pathNodes);
        for($i = 0; $i <= count($pathNodes); $i++){
            $parent = implode($ds, array_slice($pathNodes, 0, $i));
            if(!is_dir($parent) && $parent != ''){
                if(!@mkdir($parent))
                    throw new Exception("Failed to create directory $parent");
            }
        }

        if(preg_match('%^https?://%', $source)){
            if(self::tryCurl($source)){
                // Fall back on the getContents method, which will try using CURL
                $ch = self::curlInit($source);
                $contents = curl_exec($ch);
                if((bool) $contents)
                    return @file_put_contents($target, $contents) !== false;
                else
                    return false;
            } else{
                $context = stream_context_create(array(
                    'http' => array(
                        'timeout' => 15  // Timeout in seconds
                        )));
                return @copy($source, $target, $context) !== false;
            }
        }else{
            // Recursively copy a whole folder
            $source = self::rpath($source);
            if(!is_dir($source) && !file_exists($source))
                throw new Exception("Source file/directory to be copied ($source) not found.");

            if(is_dir($source)){
                if(!$contents){
                    // Append the bottom level node in the source path to the
                    // target path.
                    //
                    // This ensures that we copy in the aptly-named target
                    // directory instead of dumping the contents of the source
                    // into the designated target.
                    $source = rtrim($source, $ds);
                    $sourceNodes = explode($ds, $source);
                    $target = $target.$ds.array_pop($sourceNodes);
                }
                if(!is_dir($target)){
                    mkdir($target);
                }
                $return = true;
                $files = scandir($source);
                foreach($files as $file){
                    if($file != '.' && $file != '..'){
                        // Must be recursively called with $relTarget = false
                        // because if ccopy is called with $relTarget = true,
                        // then at this stage "$target" is already relative,
                        // and the argument passed to relpath must be absolute.
                        // It also must be called with contents=true because
                        // that option, if enabled at lower levels, will create
                        // the parent directory twice.
                        $return = $return && FileUtil::ccopy($source.$ds.$file, $target.$ds.$file);
                    }
                }
                return $return;
            }else{
                return @copy($source, $target) !== false;
            }
        }
    }

    /**
     * Removes DOS-related junk from an absolute path.
     *
     * Returns the path as an array of nodes.
     */
    public static function cleanDosPath($path){
        $a_dirty = explode('\\', $path);
        $a = array();
        foreach($a_dirty as $node){
            $a[] = $node;
        }
        $lastNode = array_pop($a);
        if(preg_match('%/%', $lastNode)){
            // The final part of the path might contain a relative path put
            // together with forward slashes (for the lazy developer)
            foreach(explode('/', $lastNode) as $node)
                $a[] = $node;
        }else{
            $a[] = $lastNode;
        }
        return $a;
    }

    /**
     * Initializes and returns a CURL resource handle
     * @param string $url
     * @return resource
     */
    public static function curlInit($url){
        $ch = curl_init($url);
        $curlopt = array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_BINARYTRANSFER => 1,
            CURLOPT_POST => 0,
            CURLOPT_TIMEOUT => 15
        );
        curl_setopt_array($ch, $curlopt);
        return $ch;
    }

    /**
     * Wrapper for file_get_contents that attempts to use CURL if allow_url_fopen is disabled.
     *
     * @param type $source
     * @param type $url
     * @return type
     * @throws Exception
     */
    public static function getContents($source, $use_include_path = false, $context = null){
        if(self::tryCurl($source)){
            $ch = self::curlInit($source);
            return @curl_exec($ch);
        }else{
            // Use the usual copy method
            return @file_get_contents($source, $use_include_path, $context);
        }
    }

    /**
     * Removes redundant up-one-level directory traversal from a path.
     *
     * Returns an array corresponding to each node in the path, with redundant
     *  directory traversal removed. For example, "items/files/stuff/../things"
     *  will be returned as array("items","files","things"). Note, however, that
     *  "../../stuff/things" will be returned as array("stuff","things"), which
     *  does not accurately reflect the actual path from the original working
     *  directory. The intention of this function was to clean up absolute paths.
     * @param array $path Path to clean
     */
    public static function noTraversal($path){
        $p2 = array();
        foreach($path as $node){
            if($node == '..'){
                if(count($p2) > 0)
                    array_pop($p2);
            } else{
                $p2[] = $node;
            }
        }
        return $p2;
    }

    /**
     * Format a path so that it is platform-independent. Doesn't return false
     * if the path doesn't exist (so unlike realpath() it can be used to create
     * new files).
     *
     * @param string $path
     * @return string
     */
    public static function rpath($path){
        return implode(DIRECTORY_SEPARATOR, explode('/', $path));
    }

    /**
     * Calculates a relative path between two absolute paths.
     *
     * @param string $path The path to which the absolute path should be calculated.
     * @param string $start The starting path. Must be absolute, if specified, and
     *  must be specified if the path argument is not platform-agnostic.
     * @param string $ds Directory separator. If the two paths (path and start)
     *  use the (almost) ubiquitous convention of forward slashes, but the
     *  calculation is to take place on a Windows machine, this must be set to
     *  forward slash, so that
     */
    public static function relpath($path, $start = null, $ds = DIRECTORY_SEPARATOR){
        $thisPath = $start === null ? realpath('.').$ds : $start;
        // Get node arrays for each path:
        if(preg_match('/^([A-Z]):\\\\/', $thisPath, $match0)){ // Windows environment
            if(preg_match('/([A-Z]):\\\\/', $path, $match1)){ // Target path is absolute
                if($match0[1] != $match1[1]) // Source and destination are on different drives. Regurgitate the absolute path.
                    return $path;
                else{ // Source/destination on same drive.
                    $a1 = self::cleanDosPath($path);
                    array_shift($a1);
                    $a1 = self::noTraversal($a1);
                }
            }else{ // Target path is relative
                $a1 = self::noTraversal(explode($ds, $path));
            }
            $a0 = self::cleanDosPath($thisPath);
            array_shift($a0);
            $a0 = self::noTraversal($a0);
            array_pop($a0);
        }else{ // Unix environment. SO MUCH EASIER.
            $a0 = self::noTraversal(explode($ds, $thisPath));
            array_pop($a0);
            $a1 = self::noTraversal(explode($ds, $path));
        }
        // Find out what the paths have in common and calculate the number of levels to traverse up:
        $l = 0;
        while($l < count($a0) && $l < count($a1)){
            if($a0[$l] != $a1[$l])
                break;
            $l++;
        }
        $lUp = count($a0) - $l;
        return str_repeat('..'.$ds, $lUp).implode($ds, array_slice($a1, $l));
    }

    /**
     * Recursively remove a directory and all its subdirectories.
     *
     * Walks a directory structure, removing files recursively. An optional
     * exclusion pattern can be included. If a directory contains a file that
     * matches the exclusion pattern, the directory and its ancestors will not
     * be deleted.
     *
     * @param string $path
     * @param string $noDelPat PCRE pattern for excluding files in deletion.
     */
    public static function rrmdir($path, $noDelPat = null){
        $useExclude = $noDelPat != null;
        $special = '/.*\/?\.+\/?$/';
        $excluded = false;
        if(!realpath($path))
            return false;
        $path = realpath($path);
        if(filetype($path) == 'dir'){
            $objects = scandir($path);
            foreach($objects as $object){
                if(!preg_match($special, $object)){
                    if($useExclude){
                        if(!preg_match($noDelPat, $object)){
                            $excludeThis = self::rrmdir($path.DIRECTORY_SEPARATOR.$object, $noDelPat);
                            $excluded = $excluded || $excludeThis;
                        }else{
                            $excluded = true;
                        }
                    } else
                        self::rrmdir($path.DIRECTORY_SEPARATOR.$object, $noDelPat);
                }
            }
            reset($objects);
            if(!$excluded)
                if(!preg_match($special, $path))
                    rmdir($path);
        } else
            unlink($path);
        return $excluded;
    }

    /**
     * Create/return finfo resource handle
     *
     * @return resource
     */
    public static function finfo(){
        if(!isset(self::$_finfo))
            if(extension_loaded('fileinfo'))
                self::$_finfo = finfo_open();
            else
                self::$_finfo = false;
        return self::$_finfo;
    }

    /**
     * Create human-readable size string
     *
     * @param type $bytes
     * @return type
     */
    public static function formatSize($bytes, $places = 0){
        $sz = 'BKMGTP';
        $factor = floor((strlen($bytes) - 1) / 3);
        return sprintf("%.{$places}f ", $bytes / pow(1024, $factor)).@$sz[$factor]."B";
    }

    /**
     * The criteria for which CURL should be used.
     * @return type
     */
    public static function tryCurl($source){
        $try = preg_match('%^https?://%', $source) && (ini_get('allow_url_fopen') == 0 || self::$alwaysCurl);
        if($try)
            if(!extension_loaded('curl'))
                throw new Exception('No HTTP methods available. Tried accessing a remote object, but allow_url_fopen is not enabled and the CURL extension is missing.', 500);
        return $try;
    }

}

?>

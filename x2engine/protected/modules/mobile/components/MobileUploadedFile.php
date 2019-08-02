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
 * Enables trimming of GET parameters from file paths 
 * (allowing validation Android-cropped images which come with what appears to be a cache-busting
 * timestamp parameter).
 */

class MobileUploadedFile extends CUploadedFile {

	static private $_files;

    /**
     * Remove GET params 
     */
    public function getExtensionName()
    {
        return preg_replace ('/\?.*$/', '', parent::getExtensionName ());
    }

    /**
     * Overridden so that self refers to MobileUploadedFile
     * This method is Copyright (c) 2008-2014 by Yii Software LLC
     * http://www.yiiframework.com/license/ 
     */
    protected static function prefetchFiles()
    {
        self::$_files = array();
        if(!isset($_FILES) || !is_array($_FILES))
            return;

        foreach($_FILES as $class=>$info)
            self::collectFilesRecursive($class, $info['name'], $info['tmp_name'], $info['type'], $info['size'], $info['error']);
    }

    /**
     * Overridden so that self refers to MobileUploadedFile
     * This method is Copyright (c) 2008-2014 by Yii Software LLC
     * http://www.yiiframework.com/license/ 
     */
    public static function getInstanceByName($name)
    {
        if(null===self::$_files)
            self::prefetchFiles();

        return isset(self::$_files[$name]) && self::$_files[$name]->getError()!=UPLOAD_ERR_NO_FILE ? self::$_files[$name] : null;
    }

    /**
     * Overridden so that self refers to MobileUploadedFile
     * This method is Copyright (c) 2008-2014 by Yii Software LLC
     * http://www.yiiframework.com/license/ 
     */
    public static function getInstance($model, $attribute)
    {
        return self::getInstanceByName(CHtml::resolveName($model, $attribute));
    }

    /**
     * Overridden to change instantiated class
     * This method is Copyright (c) 2008-2014 by Yii Software LLC
     * http://www.yiiframework.com/license/ 
     */
    protected static function collectFilesRecursive($key, $names, $tmp_names, $types, $sizes, $errors)
    {
        if(is_array($names))
        {
            foreach($names as $item=>$name)
                self::collectFilesRecursive($key.'['.$item.']', $names[$item], $tmp_names[$item], $types[$item], $sizes[$item], $errors[$item]);
        }
        else
            self::$_files[$key] = new MobileUploadedFile($names, $tmp_names, $types, $sizes, $errors);
    }
}

?>

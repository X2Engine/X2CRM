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

Yii::import('application.components.codeEditor.CodeEditorAction');

/**
 * @edition:ent
 *
 * CTreeView ajax endpoint for code editor file browser
 */

class CodeBrowserAction extends CAction {
    public function run() {
        if (Yii::app()->request->isAjaxRequest) {
            if (isset($_GET['root']) && $_GET['root'] !== 'source') {
                if (AppFileUtil::isWithinApplicationDirectory($_GET['root']))
                    $cwd = $_GET['root'];
                else
                    $cwd = false;
            } else {
                $cwd = dirname(Yii::app()->basePath);
            }
            if (is_dir($cwd)) {
                $files = array();
                $contents = glob($cwd.'/*');
                foreach ($contents as $content) {
                    $basename = basename($content);
                    $blacklisted = CodeEditorAction::isBlacklisted($content);
                    if (is_file($content)) {
                        $htmlOptions = array(
                            'id' => $basename,
                            'class' => 'fileBrowserLink',
                        );

                        $ext = explode('.', $content);
                        $ext = end($ext);
                        $blacklistExts = array('png', 'jpg', 'jpeg', 'ico', 'bmp', 'gif', 'bin',
                            'mp3', 'wav', 'mp4', 'flac', 'pdf', 'doc', 'xls', 'odt', 'ods', 'odg');
                        if (in_array($ext, $blacklistExts)) {
                            $blacklisted = true;
                        } else {
                            switch ($ext) {
                                case 'scss':
                                    $mode = 'sass';
                                    break;
                                case 'css':
                                case 'sql':
                                    $mode = $ext;
                                    break;
                                case 'js':
                                case 'json':
                                    $mode = 'javascript';
                                    break;
                                case 'html':
                                    $mode = 'xml';
                                    break;
                                case 'php': // Use PHP mixed mode by default
                                default:
                                    $mode = 'application/x-httpd-php';
                            }
                        }

                        if ($blacklisted) {
                            $text = $basename;
                        } else {

                            $htmlOptions['onclick'] = '
                                var throbber = auxlib.pageLoading();
                                $.ajax({
                                    type: "POST",
                                    data: {
                                        operation: "load",
                                        filename: '.CJSON::encode($content).'
                                    },
                                    success: function(data) {
                                        window.codeEditor.setData(data);
                                        x2.codeEditor.currentFile = '.CJSON::encode($content).';
                                        x2.codeEditor.setMode('.CJSON::encode($mode).');
                                        $("html").scrollTop(0);
                                    },
                                    error: function(data) {
                                        alert(data.responseText);
                                    },
                                    complete: function() {
                                        throbber.remove();
                                    }
                                });
                            ';

                            $text = CHtml::link($basename, '', $htmlOptions);
                        }
                        $files[] = array(
                            'id' => $content,
                            'text' => X2Html::tag('span', array(
                                'class' => 'file'
                            ), $text),
                            'hasChildren' => false,
                        );
                    } else if (is_dir($content)) {
                        $files[] = array(
                            'id' => $content,
                            'text' => X2Html::tag('span', array(
                                'class' => 'folder'
                            ), $basename),
                            'hasChildren' => !$blacklisted,
                        );
                    }
                }
                echo CTreeView::saveDataAsJson($files);
            } else {
                Yii::app()->end();
            }
        } else {
            throw new CHttpException(400, Yii::t('admin', 'Invalid Request'));
        }
    }
}

?>

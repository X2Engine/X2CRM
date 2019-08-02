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
 * Creates combined sass file from mobile module assets list. Assuming compass is watching the sass
 * directory, a combined.css file should be generated in protected/modules/mobile/assets/css.
 * Only needs to be run if new css asset files are added.
 */

class CombineMobileCssCommand extends CConsoleCommand {
    
    public function generateCombinedSassFile () {
        Yii::import ('application.modules.mobile.MobileModule');
        $fileName = '../compass/protected/modules/mobile/assets/css/combined.scss';
        $cssDependencies = array ();
        MobileModule::$useMergedCss = false; 
        $packages = MobileModule::getPackages (null);
        $assetsBase = '../'; 
        $themeBase = '../../../../../'; 
        foreach ($packages as $package) {
            if (!isset ($package['css'])) continue;
            if (!$package['baseUrl']) {
                $basePath = $assetsBase;
            } else {
                continue;
                //$basePath = $themeBase;
            }
            foreach ($package['css'] as $path) {
                $scss = preg_replace ('/\.css$/', '.scss', $path);
                if ($basePath === $assetsBase && 
                    file_exists ('../compass/protected/modules/mobile/assets/'.$scss)/* ||
                    $basePath === $themeBase && 
                    file_exists ('../compass/'.$scss)*/) {

                    $cssDependencies[] = $basePath.$scss;
                } 
            }
        }
        $fp = fopen ($fileName, 'w');
        $contents = <<<EOT
/*!*********************************************************************************
 * X2CRM is a customer relationship management program developed by
 * X2Engine, Inc. Copyright (C) 2011-2016 X2Engine Inc.
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
 * California 95067, USA. on our website at www.x2crm.com, or at our
 * email address: contact@x2engine.com.
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
 **********************************************************************************/

EOT;
        foreach ($cssDependencies as $css) {
            $contents .= "@import '$css';\n";
        }
        fwrite ($fp, $contents);
        fclose ($fp);
    }

    public function actionCombine () {
        $this->generateCombinedSassFile ();
        //$this->compileCombinedSassFile ();
    }

}

?>

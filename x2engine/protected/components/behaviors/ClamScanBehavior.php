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
 * A behavior to enable ClamAV virus scanning of the file associted with the model
 *
 * @property string $pathAttribute Model attribute containing file path
 * @package application.components.behaviors
 */
class ClamScanBehavior extends CBehavior {

    public $pathAttribute;

    /**
     * Scan the model's associated file for viruses
     * @return bool Whether a virus was detected
     */
    public function clamScan() {
        $pathAttribute = $this->pathAttribute;
        $filePath = $this->owner->$pathAttribute;
        // Test for the availability of clamav:
        $descriptor = array(
            0 => array('pipe', 'r'),
            1 => array('pipe', 'w'),
            2 => array('pipe', 'w'),
        );
        $testProc = proc_open('clamscan --help', $descriptor, $pipes);
        $ret = proc_close($testProc);
        $prog = 'clamscan';
        unset($pipes);

        if($ret !== 0){
            $testProc = proc_open('clamscan.exe --help', $descriptor, $pipes);
            $ret = proc_close($testProc);
            if($ret !== 0)
                throw new CException(Yii::t('admin', 'Unable to perform anti virus scan; the "clamscan" utility is not available on this system.'));
            else
                $prog = 'clamscan.exe';
            unset($pipes);
        }
        $quotedFilePath = escapeshellarg($filePath);
        $scan = proc_open($prog . ' -i ' . $quotedFilePath, $descriptor, $pipes);
        $output = stream_get_contents($pipes[1]);
        $classification = explode(':', preg_replace('/\n.*/', '', $output));
        $ret = proc_close($scan);

        if ($ret == 1 && isset($classification[1])) {
            $classification = trim(rtrim(str_replace('FOUND', '', $classification[1])));
            $this->owner->addError($pathAttribute,Yii::t('app', 'Warning: Detected virus "'.$classification.'"'));
            return false;
        } else if ($ret == 2) {
            throw new CException(Yii::t('app', 'Encountered an error performing anti virus scan. Aborting...'));
        }

        return true;
    }
}

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




class MobileActivityFeed {

    public static function getText (Events $model) {
        $text = '';
        switch ($model->type) {
            case 'feed':
                $text = $model->text;
                break;
            case 'structured-feed':
                $text = $model->text;
                break;
            case 'comment':
                $text = $model->text;
                break;
            case 'media': // legacy media type has photos rendered by view file
                $text = $model->text;
                break;
            default:
                $text = $model->getText (array ('requireAbsoluteUrl' => true));
                break;
        }
        // This is needed to parse the activity text, location comment, and 
        // location info from one string and format the text on the post accordingly
        $eventTexts = explode('$|&|$', $text);
        if(count($eventTexts) == 3) { 
            // take out all white space, if the location comment part of the text
            // is completely empty, don't even include 'Location Comment:'
            $string = preg_replace('~\x{00a0}~','',$eventTexts[1]);
            $stringNoWhiteSpace = preg_replace('/\s/', '', $string);
            if (strcmp($eventTexts[2],"")) {
                $text = Yii::t('app', 'Checkin Post').": ". "<br><br>" .$eventTexts[2] . "<br><br>" .$eventTexts[0];
                if (strcmp($stringNoWhiteSpace,''))
                    $text.="<br><br>" .Yii::t('app', 'Location Comment').":<br>".$eventTexts[1];
            } 
        } 

        //takeout trailing $|&|$ that's used to format activity feed posts
        $text = str_replace("$|&|$", "", $text);
        return $text;
    }
}

?>

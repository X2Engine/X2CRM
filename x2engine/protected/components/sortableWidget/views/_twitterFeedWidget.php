<?php
/***********************************************************************************
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

Yii::app()->clientScript->registerCssFile(
    Yii::app()->theme->baseUrl.'/css/components/sortableWidget/views/twitterFeedWidget.css'); 

?>
<div class='twitter-timeline-container-outer'>
    <div class='twitter-timeline-container'>
        <div class='twitter-timeline-container-inner'>
        <?php
        $this->getTimeline ();
        ?>
        </div>
        <button class='load-more-tweets-button x2-button'><?php  
            echo CHtml::encode (Yii::t('app', 'Load more'));
        ?></button>
    </div>
    <div class='tweet-box'>
        <a class='new-tweet-button' 
         href='https://twitter.com/intent/tweet?screen_name=<?php echo $username; ?>'>
        <?php
        echo CHtml::encode (Yii::t('app', 'Tweet to {username}', array (
            '{username}' => '@'.$username,
        )));
        ?>
        </a> 
    </div>
</div>

<script type="text/javascript" async src="//platform.twitter.com/widgets.js"></script>

<!--<a class="twitter-timeline" 
 href="https://twitter.com/x2engine" 
 data-screen-name="x2engine"
 data-widget-id="530872797577220096">
Tweets by @cruzio</a>

<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+"://platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>-->
